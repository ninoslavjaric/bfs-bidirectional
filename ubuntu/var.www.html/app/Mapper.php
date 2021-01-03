<?php
namespace Htec;

use Htec\Core\DbAccessLayer;

abstract class Mapper extends DbAccessLayer
{
    const SORT_ASC = 'ASC';
    const SORT_DESC = 'DESC';

    protected string $table;

    public function getTable()
    {
        return $this->table;
    }

    protected function prepareParamsForQuery($where)
    {
        $colsDefinition = array_filter($this->getColumnsDefinition(), function($column) use ($where) {
            return  in_array($column['name'], array_keys($where));
        });

        return array_map(function($definition) use ($where) {
            $operator = '=';
            $value = $where[$definition['name']];
            if (is_array($value) && array_key_exists('operator', $value) && array_key_exists('value', $value)) {
                $operator = $value['operator'];
                $value = $value['value'];
            }

            return ['column' => $definition['column'], 'operator' => $operator, 'value' => $value];
        }, $colsDefinition);
    }

    public function findByWhere($where, $joins = [], $order = [], $limit = 0)
    {
        $where = $this->prepareParamsForQuery($where);
        $order = $this->prepareParamsForQuery($order);

        return parent::findByWhere($where, $joins, $order, $limit);
    }

    public function findRowBy($fieldName, $value)
    {
        return $this->findRowByWhere([$fieldName => $value]);
    }

    public function findRowByWhere($where, $joins = [], $order = [])
    {
        $where = $this->prepareParamsForQuery($where);
        $order = $this->prepareParamsForQuery($order);

        return parent::findRowByWhere($where, $joins, $order);
    }

    final public function insert(array $data): int
    {
        $record = $this->generateColumnValueMap($data);

        return parent::insert($record);
    }

    final public function update(array $where, array $data): bool
    {
        $record = $this->generateColumnValueMap($data);
        $where = $this->generateColumnValueMap($where);

        return parent::update($where, $record);
    }

    private function generateColumnValueMap(array $data): array
    {
        $record = [];

        foreach ($this->getColumnsDefinition() as $columnDefinition) {
            $dataKey = $columnDefinition['name'];
            $columnName = $columnDefinition['column'];

            if (array_key_exists($dataKey, $data)) {
                $record[$columnName] = $data[$dataKey];
            }
        }

        return $record;
    }
}
