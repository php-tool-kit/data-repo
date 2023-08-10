<?php

namespace Ptk\DataRepo\Table;

use PDO;
use Exception;
use \Ptk\DataRepo\DataRepo;
use \Ptk\DataRepo\Field\FieldTypes;
use \Ptk\DataRepo\Field\FieldCollates;

/**
 * Representa uma tabela dentro do repositório.
 * 
 * @author Everton da Rosa <everton3x@gmail.com>
 */
class Table {
    
    /**
     * 
     * @var string Nome da tabela.
     */
    private string $name;
    
    /**
     * 
     * @var array<string, mixed> Definição dos campos da tabela.
     */
    private array $fieldsDef = [];
    
    /**
     * 
     * @var bool A tabela é temporária?
     */
    private bool $temporary = false;
    
    /**
     * 
     * @var string Nome do schema no qual a tabela será incluída.
     */
    private string $schema = 'main';
    
    /**
     * 
     * @var bool Determina se será ou não usada IF NOT EXISTS ao criar a tabela.
     */
    private bool $createIfNotExists = true;
    
    /**
     * 
     * @var DataRepo O repositório no qual a tabela está anexada.
     */
    private DataRepo $repo;
    
    /**
     * Construtor de classe.
     * 
     * @param string $name Nome da tabela. Deve obedecer à conveção de nomenclatura do SQLite.
     */
    public function __construct(string $name) {
        $this->name = $name;
    }
    
    /**
     * Define se a tabela é temporária ou não.
     * @param bool $value
     * @return Table
     */
    public function temporaryTable(bool $value): Table {
        $this->temporary = $value;
        return $this;
    }
    
    /**
     * Diz se a tabela é temporária ou não.
     * @return bool
     */
    public function isTemporary(): bool {
        return $this->temporary;
    }
    
    /**
     * Define se a criação será pulada se a tabela já existir.
     * 
     * @param bool $value
     * @return Table
     */
    public function createIfNotExists(bool $value): Table {
        $this->createIfNotExists = $value;
        return $this;
    }
    
    /**
     * Adiciona um novo campo à tabela (apenas antes dela ser criada no repositório).
     * 
     * @param string $name Nome do campo.
     * @param FieldTypes $type Tipo do campo (um dos tipos suportados pelo SQlite).
     * @param bool $nullable Se o campo pode ser NULL.
     * @param bool $unique Se o campo é único ou não.
     * @param mixed $default Valor padrão para o campo.
     * @param FieldCollates|null $collate Um dos collations suportados pelo SQLite.
     * @return Table
     */
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
    
    /**
     * Construtor da string de definição de colunas.
     * 
     * Método auxiliar para Table::getSQL
     * 
     * @return string
     */
    private function buildColDef(): string {
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

    /**
     * Construtor do código SQL para a criação da tabela.
     * 
     * @return string
     */
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
    
    /**
     * Método auxiliar para vincular a tabela ao repositório.
     * 
     * Geralmente não é preciso utilizar esse método.
     * 
     * Ele é ustilizado por DataRepo::addTable
     * 
     * @param DataRepo $repo
     * @return void
     */
    public function setRepo(DataRepo $repo): void {
        $this->repo = $repo;
    }

    /**
     * Retorna a lista de nomes de campos da tabela.
     * 
     * @return array<string>
     */
    public function getFieldNames(): array {
        $names = [];
        foreach ($this->fieldsDef as $field){
            $names[] = $field['name'];
        }
        return $names;
    }

    /**
     * Retorna a lista de tipos dos campos da tabela.
     * 
     * @return array<FieldTypes>
     */
    public function getFieldTypes(): array {
        $types = [];
        foreach ($this->fieldsDef as $field){
            $types[] = $field['type'];
        }
        return $types;
    }
    
    /**
     * Retorna o nome da tabela.
     * 
     * @return string
     */
    public function getTableName(): string {
        return $this->name;
    }
    
    /**
     * Insere dados na tabela.
     * 
     * Os dados devem estar organizados num array bidimensional por linhas/colunas.
     * 
     * @param array<mixed> $data
     * @return Table
     * @throws Exception
     */
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