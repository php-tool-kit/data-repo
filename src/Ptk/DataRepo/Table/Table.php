<?php

namespace Ptk\DataRepo\Table;

use PDO;
use Exception;
use Ptk\DataRepo\DataRepo;
use Ptk\DataRepo\Field\FieldTypes;
use Ptk\DataRepo\Field\FieldCollates;
use Ptk\DataRepo\Reader\SpreadsheetReader;


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
    private ?DataRepo $repo = null;
    
    /**
     * 
     * @var array<mixed> Cache para dados a serem inseridos, usado quando a tabela não foi inserida em um repositório.
     * 
     */
    private array $cache = [];
    
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
        if ($this->hasCache()) $this->insertData ();
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
     * Insere dados do cache na tabela.
     * 
     * @return void
     * @throws Exception
     */
    private function insertData(): void {
        if (is_null($this->repo)) throw new Exception('Tabela sem repositório anexado.');
        $data = $this->cache;
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
        $this->cache = [];
    }
    
    /**
     * Insere dados a partir de um array bidimensional linha/coluna.
     * 
     * @param array<mixed> $data
     * @return Table
     */
    public function fromArray(array $data): Table {
        $this->cache = $data;
        if ($this->repo instanceof DataRepo){
            $this->insertData();
        }
        return $this;
    }

    /**
     * Indica se existem dados em cache.
     * 
     * @return bool
     */
    private function hasCache(): bool {
        if ($this->cache === []) return false;
        return true;
    }
    
    /**
     * Retorna todos os dados da tabela em um array bidimensional linha/coluna.
     * 
     * Se a tabela ainda não foi anexada, retorna o que existir em cache.
     * 
     * @return array<mixed>
     */
    public function toArray(): array {
        if (is_null($this->repo)) return $this->cache;
        
        $sql = "SELECT * FROM {$this->name}";
        $stmt = $this->repo->query($sql);
        if ($stmt) return $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [];
    }
    
    /**
     * Carrega os dados a partir de uma planilha de uma pasta de trabalho.
     * 
     * 
     * @param string $filepath O caminho para a pasta de trabalho.
     * @param int|string $sheet O número (0-indexed) ou o nome da planilha.
     * @return Table
     */
    public function fromSpreadsheet(string $filepath, int|string $sheet): Table {
        $wb = SpreadsheetReader::loadSpreadsheet($filepath);
        $this->fromArray(SpreadsheetReader::readDataFromSheet($wb, $sheet));
        return $this;
    }
    
    
    
}