<?php
namespace Htec\Mapper;

use Htec\Mapper;

class City extends Mapper
{
    protected const SELECT_PATTERN = 'SELECT %s FROM %s %s';

    protected string $table = 'cities';

    public function getColumnsDefinition()
    {
        return [
            ['column' => 'id', 'name' => 'id', 'type' => 'int'],
            ['column' => 'country_id', 'name' => 'countryId', 'type' => 'int', 'required' => true, 'unique' => true],
            ['column' => 'name', 'name' => 'name', 'type' => 'str', 'required' => true, 'unique' => true],
        ];
    }

    private function prepareSqlParams($where, $limit = 0): string
    {
        $where = $this->prepareParamsForQuery($where);
        $conditionPlaceHolders = [];
        foreach ($where as $whereItem) {
            $conditionPlaceHolders[] = "`{$this->getTable()}`.`{$whereItem['column']}` {$whereItem['operator']} {$this->quote($whereItem['value'])}";
        }

        $whereSql = '';

        if (!empty($conditionPlaceHolders)) {
            $whereSql = 'WHERE ' . implode(' AND ', $conditionPlaceHolders);
        }

        $selectColumns = implode(', ', $this->getColumns());

        $joins = [
            'JOIN countries ON countries.id = country_id',
            'LEFT JOIN comments ON comments.city_id = cities.id',
        ];

        $joins = implode(' ', $joins);

        $limitSql = $limit > 0 ? "LIMIt {$limit}" : '';

        return sprintf(
            static::SELECT_PATTERN,
            $selectColumns,
            $this->getTable(),
            "{$joins} {$whereSql} GROUP BY {$this->getTable()}.id ORDER BY country_name {$limitSql}",
        );
    }

    public function findRowByWhere($where, $joins = [], $order = [])
    {
        $sql = $this->prepareSqlParams($where, 1);

        return parent::fetchRow($sql);
    }

    public function findByWhere($where, $joins = [], $order = [], $limit = 0)
    {
        $sql = $this->prepareSqlParams($where, $limit);

        return parent::fetch($sql);
    }

    public function searchByName(string $name)
    {
        return $this->findByWhere(['name' => ['operator' => 'like', 'value' => "%{$name}%"]]);
    }

    protected function getColumns()
    {
        $selectSql = parent::getColumns();

        $selectSql[] = "countries.name as country_name";
        $selectSql[] = "CONCAT('[', GROUP_CONCAT(JSON_QUOTE(comments.text) SEPARATOR ','), ']') AS comments";

        return $selectSql;
    }


}
