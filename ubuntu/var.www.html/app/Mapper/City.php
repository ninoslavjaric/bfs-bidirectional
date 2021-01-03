<?php
namespace Htec\Mapper;

use Htec\Mapper;

final class City extends Mapper
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

    public function findRowByWhere($where, $joins = [], $order = [])
    {
        $joins[] = ['countries', 'country_id'];
        return parent::findRowByWhere($where, $joins, $order);
    }


    public function findByWhere($where, $joins = [], $order = [], $limit = 0)
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

        $sql = sprintf(
            static::SELECT_PATTERN,
            $selectColumns,
            $this->table,
            "JOIN countries ON countries.id = country_id {$whereSql} ORDER BY countryName",
        );

        return parent::fetch($sql);
    }

    public function searchByName(string $name)
    {
        return $this->findByWhere(['name' => ['operator' => 'like', 'value' => "%{$name}%"]]);
    }

    protected function getColumns()
    {
        $selectSql = parent::getColumns();

        $selectSql[] = "countries.name as countryName";

        return $selectSql;
    }


}
