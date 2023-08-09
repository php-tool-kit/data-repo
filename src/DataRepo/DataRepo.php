<?php

namespace Ptk\DataRepo;

use PDO;

use Ptk\DataRepo\Table\Table;

class DataRepo extends PDO {
    
    public function __construct(string $local = ':memory:', ?string $userName = null, ?string $password = null, ?array $options = null) {
        $dsn = "sqlite:$local";
        parent::__construct($dsn, $userName, $password, $options);
        parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getTableNames(): array {

    }

    public function getTables(): array {

    }

    public function dropTable(string $tableName): DataRepo {

    }

    public function truncateTable(string $tableName): DataRepo {

    }

    public function addTable(Table $tableName): DataRepo {

    }

    public function fromArray(array $data): DataRepo {

    }

    public function fromSpreadSheet(string $filePath, string $sheetName): DataRepo {

    }

    public function fromWorkBook(string $filePath): DataRepo {

    }

    public function fromCSV(string $filePath): DataRepo {

    }

    private function createTable(): void {
        
    }

    private function insertData(array $data): void {

    }

    public function toArray(): array {

    }

    public function toSpreadSheet(string $filePath, string $sheetName): DataRepo {

    }

    public function toWorkBook(string $filePath): DataRepo {

    }

    public function toCSV(string $filePath): DataRepo {

    }

}