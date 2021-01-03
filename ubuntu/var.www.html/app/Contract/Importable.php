<?php
namespace Htec\Contract;

interface Importable
{
    public function getImportItemStructure(): array;
    public function importData(string $data): void;
}
