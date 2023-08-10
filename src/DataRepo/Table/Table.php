<?php

namespace Ptk\DataRepo\Table;

use PDO;
use Exception;
use \Ptk\DataRepo\DataRepo;
use \Ptk\DataRepo\Field\FieldTypes;
use \Ptk\DataRepo\Field\FieldCollates;

class Table {
    
    private string $name;
    private array $fieldsDef = [];
    
    private bool $temporary = false;
    private string $schema = 'main';
    private bool $createIfNotExists = true;
    
    private DataRepo $repo;
    

    public function __construct(string $name) {
        $this->name = $name;
    }
    

    public function temporaryTable(bool $value): Table {
        $this->temporary = $value;
        return $this;
    }
    
    public function isTemporary(): bool {
        return $this->temporary;
    }
    
    public function createIfNotExists(bool $value): Table {
        $this->createIfNotExists = $value;
        return $this;
    }
    
    public function addField(string $name, FieldTypes $type = FieldTypes::Text, bool $nullable = true, bool $unique = false, mixed $default = null, ?FieldCollates $collate = null): Table {
        $this->fieldsDef[$name] = [
            'name' => $name,
            'type' => $type,
            'nullable' => $nullable,
            'unique' => $unique,
            'default' => $default,
            'collate' => $collate
        ];
        return $this;
    }
    
    protected function buildColDef(): string {
        $colDef = [];
        foreach ($this->fieldsDef as $field){
            $notnull = '';
            $default = '';
            $unique = '';
            $collate = '';
            if (!$field['nullable']) $notnull = 'NOT NULL';
            if (!is_null($field['default'])) $default = "DEFAULT {$field['default']}";
            if ($field['unique']) $unique = 'UNIQUE';
            if (!is_null($field['collate'])) $collate = "COLLATE {$field['collate']->value}";
            $colDef[] = trim("{$field['name']} {$field['type']->value} $notnull $default $unique $collate");
        }
        return implode(', ', $colDef);
    }

    public function getSQL(): string {
        $strTemp = '';
        if($this->temporary) {
            $strTemp = 'TEMPORARY';
        }
        
        $strIfNotExists = '';
        if($this->createIfNotExists){
            $strIfNotExists = 'IF NOT EXISTS';
        }
        
        $sql = "CREATE TABLE $strTemp $strIfNotExists {$this->schema}.{$this->name} ({$this->buildColDef()})";
        
        return $sql;
    }
    
    public function setRepo(DataRepo $repo): void {
        $this->repo = $repo;
    }

    public function getFieldNames(): array {
        $names = [];
        foreach ($this->fieldsDef as $field){
            $names[] = $field['name'];
        }
        return $names;
    }

    public function getFieldTypes(): array {
        $types = [];
        foreach ($this->fieldsDef as $field){
            $types[] = $field['type'];
        }
        return $types;
    }
    
    public function getTableName(): string {
        return $this->name;
    }
    
    public function insertData(array $data): Table {
        $strFields = implode(', ', $this->getFieldNames());
        $valueKeys = [];
        foreach ($this->getFieldNames() as $colName){
            $valueKeys[] = ":$colName";
        }
        $strValueKeys = implode(', ', $valueKeys);
        $sql = "INSERT INTO {$this->name} ($strFields) VALUES ($strValueKeys);";
        $this->repo->beginTransaction();
        try {
            $stmt = $this->repo->prepare($sql);
            foreach ($data as $row){
                $stmt->execute($row);
            }
            $this->repo->commit();
        } catch (Exception $ex) {
            $this->repo->rollBack();
            throw $ex;
        }
        
        return $this;
    }
    
}