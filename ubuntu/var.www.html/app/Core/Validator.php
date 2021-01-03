<?php
namespace Htec\Core;

use Htec\Service;
use Htec\Traits\SingletonTrait;

final class Validator
{
    use SingletonTrait;

    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_STR = 'str';
    const TYPE_DATETIME = 'datetime';
    const TYPE_ENUM = 'enum';

    // todo aggregate messages here
    private array $messages = [];

    public function validateInputs(array $data, array $columnsDefinition, Service $service, bool $isUpdate = false): array
    {
        $result = [];

        if ($isUpdate) {
            $message = $this->validateInitialUpdateCondition($data);
            if (!empty($message)) {
                return [$message];
            }
        }

        $definitionMap = [];

        foreach ($columnsDefinition as $columDefinition) {
            $definitionMap[$columDefinition['name']] = $columDefinition;

            if ($isUpdate) {
                continue;
            }

            $message = $this->validateRequired($columDefinition, $data);
            if (!empty($message)) {
                $result[] = $message;
            }
        }

        foreach ($data as $attribute => $value) {
            $message = $this->validateByDefinitionExistence($definitionMap, $attribute);
            if (!empty($message)) {
                $result[] = $message;
                continue;
            }

            $message = $this->validateByType($definitionMap[$attribute], $value);
            if (!empty($message)) {
                $result[] = $message;
            }
        }

        $uniqueColumnsDefined = array_filter($columnsDefinition, function ($definition) use ($data) {
            return ($definition['unique'] ?? false) && array_key_exists($definition['name'], $data);
        });

        if (!empty($uniqueColumnsDefined)) {
            $uniqueKeys = array_column($uniqueColumnsDefined, 'name');
            $uniqueData = [];
            foreach ($data as $key => $value) {
                if (in_array($key, $uniqueKeys)) {
                    $uniqueData[$key] = $value;
                }
            }

            $message = $this->validateByUnique($uniqueData, $service);

            if (!empty($message)) {
                $result[] = $message;
            }
        }

        return $result;
    }

    private function validateByDefinitionExistence(array $definitionMap, string $attribute): string
    {
        $message = '';

        if (!array_key_exists($attribute, $definitionMap)) {
            $message = "{$attribute} is not defined as a parameter";
        }

        return $message;
    }

    private function validateByType(array $columDefinition, $value): string
    {
        $message = '';

        switch ($columDefinition['type']) {
            case self::TYPE_INT:
                if (!$this->validateInt($value)) {
                    $message = "{$columDefinition['name']} must be integer";
                }
                break;

            case self::TYPE_FLOAT:
                if (!$this->validateFloat($value)) {
                    $message = "{$columDefinition['name']} must be floating point";
                }
                break;

            case self::TYPE_STR:
                if (!$this->validateString($value)) {
                    $message = "{$columDefinition['name']} must be string";
                }
                break;

            case self::TYPE_ENUM:
                $allowedValues = $columDefinition['values'];
                if (!$this->validateEnum($value, $allowedValues)) {
                    $message = "{$columDefinition['name']} must take values from " . json_encode($allowedValues);
                }
                break;

            case self::TYPE_DATETIME:
                break;
        }

        return $message;
    }

    private function validateInt($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateFloat($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    private function validateString($value): bool
    {
        return is_string($value);
    }

    private function validateEnum($value, array $allowedValues): bool
    {
        return in_array($value, $allowedValues);
    }

    private function validateRequired(array $columnDefinition, array $data): string
    {
        $message = '';

        $name = $columnDefinition['name'];

        if (($columnDefinition['required'] ?? false) && !array_key_exists($name, $data)) {
            $message = "{$name} is required.";
        }

        return $message;
    }

    private function validateByUnique(array $uniqueData, Service $service): string
    {
        $message = '';
        $data = $service->get($uniqueData);

        if (!empty($data)) {
            $keys = array_keys($uniqueData);
            $message = 'Attributes ' . implode(', ', $keys) . ' must be unique';
        }

        return $message;
    }

    private function validateInitialUpdateCondition(array $data): string
    {
        $message = '';

        if (!isset($data['id'])) {
            $message = 'Id required on update';
        }

        return $message;
    }
}
