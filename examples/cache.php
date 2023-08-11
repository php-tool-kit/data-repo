<?php
// Exemplo usando cache de dados.
// O cache de dados somente é usado se forem inseridos dados em uma tabela sem que ela esteja anexada a um repositório.
// Caso sejam inseridos dados em uma tabela sem que ela esteja anexa a um repositório, os dados ficam em cache na tabela (nãoestão disponíveis para outras operações).
// Quando a tabela for anexada a um repositório (a tabela é criada no SQLite), os dados são inseridos e ficam disponíveis para outras operações.

require_once '../vendor/autoload.php';

use Ptk\DataRepo\DataRepo;
use Ptk\DataRepo\Table\Table;
use Ptk\DataRepo\Field\Field;
use Ptk\DataRepo\Field\FieldTypes;

// Dados de exemplo.
$data = [
    [
        'id' => 1,
        'nome' => 'Maria',
        'idade' => 23,
        'salario' => 5000.0,
        'ativo' => true
    ],
    [
        'id' => 2,
        'nome' => 'José',
        'idade' => 32,
        'salario' => 7000.0,
        'ativo' => true
    ],
    [
        'id' => 3,
        'nome' => 'Paulo',
        'idade' => 53,
        'salario' => 10000.0,
        'ativo' => false
    ],
    [
        'id' => 4,
        'nome' => 'Aparício',
        'idade' => 67,
        'salario' => 12000.0,
        'ativo' => true
    ],
    [
        'id' => 5,
        'nome' => 'Lourdes',
        'idade' =>18,
        'salario' => 3000.0,
        'ativo' => true
    ],
];

// Cria o repositório.
$repo = new DataRepo();

// Cria a tabela "pessoas"
$tblPessoas = new Table('pessoas');

// Adiciona os campos à tabela "pessoas".
$tblPessoas
    ->addField('id', FieldTypes::Int, unique: true, nullable: false)
    ->addField('nome', nullable: false, unique: true)
    ->addField('idade', FieldTypes::Int)
    ->addField('salario', FieldTypes::Real)
    ->addField('ativo', FieldTypes::Int)
;

// Insere os dados na tabela.
// Os dados ficam em cache (não disponíveis).
$tblPessoas->fromArray($data);

// Adiciona a tabela ao repositório.
// Os dados são adicionados à tabela efetivamente.
$repo->addTable($tblPessoas);

