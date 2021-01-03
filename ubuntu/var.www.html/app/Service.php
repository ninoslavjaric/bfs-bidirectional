<?php
namespace Htec;

use Htec\Core\Logger;
use Htec\Core\Validator;
use Htec\Exception\InvalidParamsException;
use Htec\Exception\NotFoundException;
use Htec\Traits\SingletonTrait;

abstract class Service
{
    use SingletonTrait;

    protected Mapper $mapper;

    private static array $requestScopeData = [];

    final protected function __construct()
    {
        $mapperClass = str_replace('Service', 'Mapper', static::class);
        $this->mapper = new $mapperClass();
    }

    final private function validateWhere(array $where): void
    {
        foreach (array_keys($where) as $field) {
            if (!in_array($field, $this->getFields())) {
                throw new \Exception("Table {$this->mapper->getTable()} has no field {$field}");
            }
        }
    }

    public function getAll(array $where = []): array
    {
        $this->validateWhere($where);

        $result = $this->mapper->findByWhere($where) ?: [];

        foreach ($result as &$item) {
            $item = $this->generateDataKeyValueMap($item);
        }

        return $result;
    }

    public function get(array $where): array
    {
        $this->validateWhere($where);

        $result = $this->mapper->findRowByWhere($where) ?: [];

        return $this->generateDataKeyValueMap($result);
    }

    public function getBy($field, $value): array
    {
        if (!in_array($field, $this->getFields())) {
            throw new \Exception("Table {$this->mapper->getTable()} has no field {$field}");
        }

        $result = $this->mapper->findRowBy($field, $value) ?: [];

        return $this->generateDataKeyValueMap($result);
    }

    final private function getFields()
    {
        return array_column($this->mapper->getColumnsDefinition(), 'name');
    }

    final public function create(array $data): array
    {
        $this->beforeCreate($data);

        $id = $this->mapper->insert($data);

        return $this->afterCreate($id, $data);
    }

    protected function beforeCreate(array &$data): void
    {
        $columnsDefinition = $this->mapper->getColumnsDefinition();
        $validationMessages = Validator::getInstance()->validateInputs($data, $columnsDefinition, $this);

        if (!empty($validationMessages)) {
            throw new InvalidParamsException(implode(PHP_EOL, $validationMessages));
        }
    }

    protected function afterCreate(int $id, array $data): array
    {
        Logger::logInfo(sprintf('Created %s | ID %d | DATA %s', get_class($this), $id, json_encode($data)));

        $data = $this->mapper->findRowBy('id', $id);

        if (empty($data)) {
            throw new NotFoundException(static::class . " record {$id} not found");
        }

        return $data ?: [];
    }

    public final function edit(array $data): array
    {
        $this->beforeEdit($data);

        $id = $data['id'];
        unset($data['id']);

        $this->mapper->update(['id' => $id], $data);

        return $this->afterEdit($id, $data);
    }

    protected function beforeEdit(array &$data)
    {
        $columnsDefinition = $this->mapper->getColumnsDefinition();
        $validationMessages = Validator::getInstance()->validateInputs($data, $columnsDefinition, $this, true);

        if (!empty($validationMessages)) {
            throw new InvalidParamsException(implode(PHP_EOL, $validationMessages));
        }
    }

    protected function afterEdit(int $id, array $data)
    {
        Logger::logInfo(sprintf('Updated %s | ID %d | DATA %s', get_class($this), $id, json_encode($data)));

        $data = $this->mapper->findRowBy('id', $id);

        if (empty($data)) {
            throw new NotFoundException(static::class . " record {$id} not found");
        }

        return $data ?: [];
    }


    public function delete(int $id): int
    {
        $this->beforeDelete($id);

        $this->mapper->delete($id);

        return $this->afterDelete($id);
    }

    protected function beforeDelete(int $id)
    {
        $data = $this->getBy('id', $id);

        if (empty($data)) {
            throw new InvalidParamsException(static::class . " record {$id} doesn't exist");
        }
    }

    protected function afterDelete(int $id)
    {
        Logger::logInfo(sprintf('Deleted %s | ID %d', get_class($this), $id));

        $data = $this->getBy('id', $id);

        if (!empty($data)) {
            throw new NotFoundException(static::class . " record {$id} not deleted");
        }

        return $id;
    }

    protected function getColumnDefinitionForKeyValueMap(): array
    {
        return $this->mapper->getColumnsDefinition();
    }

    protected function generateDataKeyValueMap(array $data): array
    {
        $record = [];

        foreach ($this->getColumnDefinitionForKeyValueMap() as $columnDefinition) {
            $dataKey = $columnDefinition['name'];
            $columnName = $columnDefinition['column'];

            if (array_key_exists($columnName, $data)) {
                $record[$dataKey] = $data[$columnName];
            }
        }

        return $record;
    }

    public static function getRequestScopeData(): array
    {
        return self::$requestScopeData;
    }

    public static function setRequestScopeData(string $key, $requestScopeData): void
    {
        self::$requestScopeData[$key] = $requestScopeData;
    }
}
