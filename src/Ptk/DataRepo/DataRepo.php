<?php

namespace Ptk\DataRepo;

use Exception;
use PDO;
use PDOException;
use Ptk\DataRepo\Table\Table;

/**
 * Repositório de dados correpondendo a um banco de dados do SQLite.
 * 
 * @author Everton da Rosa <everton3x@gmail.com>
 */
class DataRepo extends PDO {

    /**
     * 
     * @var array<Table> Lista das tabelas.
     */
    private array $tables = [];

    /**
     * Construtor da classe.
     * 
     * @param string $local Local de armazenamento da tabela. O padrão é na memória. Se fornecido um caminho de arquivo, ele será utilizado. Deve ser compatível com o PDO DSN para SQLite.
     * @param string|null $userName
     * @param string|null $password
     * @param array<mixed>|null $options
     */
    public function __construct(string $local = ':memory:', ?string $userName = null, ?string $password = null, ?array $options = null) {
        $dsn = "sqlite:$local";
        parent::__construct($dsn, $userName, $password, $options);
        parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Cria uma nova tabela no repositório.
     * 
     * @param Table $table
     * @return DataRepo
     */
    public function addTable(Table $table): DataRepo {
        $this->exec($table->getSQL());
        $this->tables[$table->getTableName()] = $table;
        $table->setRepo($this);
        return $this;
    }

    /**
     * Retorna uma tabela específica.
     * 
     * @param string $tableName
     * @return Table
     */
    public function getTable(string $tableName): Table {
        $this->throwIfTableNotExists($tableName);
        return $this->tables[$tableName];
    }

    /**
     * Indica se uma tabela existe ou não no repositório.
     * 
     * @param string $tableName
     * @return bool
     * @throws PDOException
     */
    public function tableExists(string $tableName): bool {
        $stmt = $this->prepare('SELECT name FROM sqlite_master WHERE type="table" AND name=:tableName;');
        $stmt->execute(['tableName' => $tableName]);
        $count = sizeof($stmt->fetchAll());
        if ($count == 0)
            return false;
        if ($count == 1)
            return true;
        throw new PDOException("Quantidade de tabelas é maior que 1: $count");
    }

    /**
     * Dispara uma exceção se a tabela não existir.
     * 
     * Serve como auxiliar em métodos que precisam falhar se a tabela não existir.
     * 
     * @param string $tableName
     * @return void
     * @throws Exception
     */
    private function throwIfTableNotExists(string $tableName): void {
        if (!$this->tableExists($tableName))
            throw new Exception("Tabela $tableName não existe!");
    }
}
