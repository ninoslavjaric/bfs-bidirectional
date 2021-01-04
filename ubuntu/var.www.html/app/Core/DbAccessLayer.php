<?php
namespace Htec\Core;

abstract class DbAccessLayer
{
    protected const SELECT_PATTERN = 'SELECT %s FROM %s %s';
    protected string $table;

    private \PDO $connection;

    abstract public function getColumnsDefinition();

    /**
     * @return \PDO
     */
    private function getConnection(): \PDO
    {
        if (!isset($this->connection)) {
            $dbCfg = Configuration::getInstance()->getConfig('database');
            $this->connection = new \PDO(
                "mysql:dbname={$dbCfg['database']};host={$dbCfg['host']}", $dbCfg['username'], $dbCfg['password']
            );
        }

        return $this->connection;
    }

    private function prepareSelectCondidionParams(array $where)
    {
        $conditionPlaceHolders = [];
        foreach ($where as $whereItem) {
            $conditionPlaceHolders[] = "`{$this->getTable()}`.`{$whereItem['column']}` = ?";
        }

        return [implode(' AND ', $conditionPlaceHolders), array_column($where, 'value')];
    }

    private function getOrderSql(array $order): string
    {
        $orderSql = '';

        if (!empty($order)) {
            $orderSql = 'ORDER BY ';
            $order = array_map(function($orderItem) {
                return "`{$orderItem['column']}` {$orderItem['value']}";
            }, $order);

            $orderSql .= implode(', ', $order);
        }

        return $orderSql;
    }

    private function prepareStatement(array $where, array $joins = [], array $order = [], int $limit = 0): \PDOStatement
    {
        list($selectAddon, $values) = $this->getSelectAddon($where, $joins, $order, $limit);

        $selectColumns = implode(', ', $this->getColumns());

        $sql = sprintf(static::SELECT_PATTERN, $selectColumns, $this->table, $selectAddon);
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($values);

        list($sqlState, $errDriver, $errMessage) = $statement->errorInfo();

        if ($errMessage) {
            Logger::logError("[SQLSTATE {$sqlState}] #{$errDriver} : {$errMessage}");
        }

        return $statement;
    }

    public function findRowByWhere(array $where, array $joins = [], array $order = [])
    {
        $statement = $this->prepareStatement($where, $joins, $order, 1);

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByWhere(array $where, array $joins = [], array $order = [], int $limit = 0)
    {
        $statement = $this->prepareStatement($where, $joins, $order, $limit);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function getColumns()
    {
        $selectSql = [];
        foreach ($this->getColumnsDefinition() as $definition) {
            $selectSql[] = "{$this->table}.{$definition['column']}";
        }

        return $selectSql;
    }

    private function getSelectAddon(array $where, array $joins, array $order, int $limit)
    {
        list($conditionPlaceHolders, $values) = $this->prepareSelectCondidionParams($where);

        if (!empty($conditionPlaceHolders)) {
            $conditionPlaceHolders = "WHERE {$conditionPlaceHolders}";
        }

        $joinSql = '';
        foreach ($joins as &$join) {
            @list($table, $remoteColumn, $localColumn) = $join;
            $localColumn = $localColumn ?? 'id';

            $join = "INNER JOIN {$table} ON {$table}.{$localColumn} = {$remoteColumn}";
        }

        if (!empty($joins)) {
            $joinSql = implode(' ', $joins);
        }

        $orderSql = $this->getOrderSql($order);

        $limitSql = $limit ? "LIMIT {$limit}" : '';

        $selectAddonSql = "{$joinSql} {$conditionPlaceHolders} {$orderSql} {$limitSql}";

        return [$selectAddonSql, $values];
    }

    protected function insert(array $record): int
    {
        list($columns, $values, $valuePlaceHolders) = $this->getQueryBuildArrays($record);

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s);',
            $this->table,
            implode('`,`', $columns),
            $valuePlaceHolders
        );

        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($values);

        list($sqlState, $errDriver, $errMessage) = $statement->errorInfo();

        if ($errMessage) {
            Logger::logError("[SQLSTATE {$sqlState}] #{$errDriver} : {$errMessage}");
        }

        return $this->getConnection()->lastInsertId();
    }

    protected function update(array $where, array $record): bool
    {
        list($updateColumns, $updateValues, $valuePlaceHolders) = $this->getQueryBuildArrays($record);
        list($whereColumns, $whereValues) = $this->getQueryBuildArrays($where);

        array_walk($updateColumns, function(&$column) {
            $column = "`{$column}` = ?";
        });

        array_walk($whereColumns, function(&$column) {
            $column = "`{$column}` = ?";
        });

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s;',
            $this->table,
            implode(', ', $updateColumns),
            implode(' AND ', $whereColumns)
        );

        $statement = $this->getConnection()->prepare($sql);

        $values = array_merge($updateValues, $whereValues);
        $statement->execute($values);

        list($sqlState, $errDriver, $errMessage) = $statement->errorInfo();

        if ($errMessage) {
            Logger::logError("[SQLSTATE {$sqlState}] #{$errDriver} : {$errMessage}");
        }

        return true;
    }

    private function prepareStatementFromSql($sql)
    {
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute();

        list($sqlState, $errDriver, $errMessage) = $statement->errorInfo();

        if ($errMessage) {
            Logger::logError("[SQLSTATE {$sqlState}] #{$errDriver} : {$errMessage}");
        }

        return $statement;
    }

    protected function fetch($sql)
    {
        $sth = $this->prepareStatementFromSql($sql);
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function fetchRow($sql)
    {
        $sth = $this->prepareStatementFromSql($sql);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    protected function fetchScalar($sql)
    {
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute();

        list($sqlState, $errDriver, $errMessage) = $statement->errorInfo();

        if ($errMessage) {
            Logger::logError("[SQLSTATE {$sqlState}] #{$errDriver} : {$errMessage}");
        }

        return $statement->fetchColumn();
    }

    private function getQueryBuildArrays($record): array
    {
        $columns = array_keys($record);
        $values = array_values($record);

        $valuePlaceHolders = str_repeat(', ?', count($columns));

        return [$columns, $values, ltrim($valuePlaceHolders, ', ')];
    }

    protected function execute($query)
    {
        $this->getConnection()->query($query);
    }

    protected function quote($value)
    {
        return $this->getConnection()->quote($value);
    }
}
