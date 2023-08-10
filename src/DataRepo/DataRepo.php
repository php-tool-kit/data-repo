<?php

namespace Ptk\DataRepo;

use PDO;
use PDOException;
use Exception;

use Ptk\DataRepo\Table\Table;

class DataRepo extends PDO {
    
    private array $tables = [];
    
    public function __construct(string $local = ':memory:', ?string $userName = null, ?string $password = null, ?array $options = null) {
        $dsn = "sqlite:$local";
        parent::__construct($dsn, $userName, $password, $options);
        parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function addTable(Table $table): DataRepo {
        $this->exec($table->getSQL());
        $this->tables[$table->getTableName()] = $table;
        $table->setRepo($this);
        return $this;
    }
    
    public function getTable(string $tableName): Table {
        $this->throwIfTableNotExists($tableName);
        return $this->tables[$tableName];
    }
    
    public function tableExists(string $tableName): bool {
        $stmt = $this->prepare('SELECT name FROM sqlite_master WHERE type="table" AND name=:tableName;');
        $stmt->execute(['tableName' => $tableName]);
        $count = sizeof($stmt->fetchAll());
        if ($count == 0 ) return false;
        if ($count == 1 ) return true;
        throw new PDOException("Quantidade de tabelas é maior que 1: $count");
    }
    
    private function throwIfTableNotExists(string $tableName): void {
        if (!$this->tableExists($tableName)) throw new Exception("Tabela $tableName não existe!");
    }

    

}