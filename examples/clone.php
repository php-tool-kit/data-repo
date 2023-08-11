<?php

// Reutilizando uma mesma Tabela em dois repositórios.

require_once '../vendor/autoload.php';

use Ptk\DataRepo\DataRepo;
use Ptk\DataRepo\Table\Table;
use Ptk\DataRepo\Field\Field;
use Ptk\DataRepo\Field\FieldTypes;

// Dados de exemplo.
$data1 = [
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
$repo1 = new DataRepo('repo1.db');

// Cria a tabela "pessoas"
$tblPessoas1 = new Table('pessoas');

// Adiciona os campos à tabela "pessoas".
$tblPessoas1
    ->addField('id', FieldTypes::Int, unique: true, nullable: false)
    ->addField('nome', nullable: false, unique: true)
    ->addField('idade', FieldTypes::Int)
    ->addField('salario', FieldTypes::Real)
    ->addField('ativo', FieldTypes::Int)
;

// Adiciona a tabela ao repositório.
$repo1->addTable($tblPessoas1);

// Insere os dados na tabela.
$tblPessoas1->fromArray($data1);

// Cria um outro repositório.
$repo2 = new DataRepo('repo2.db');

// Clonando a primeira tabela.
// Essa nova tabela ainda estará vinculada ao $repo1
$tblPessoas2 = clone $tblPessoas1;

// Ao adicionar a tabela ao $repo2, aí sim temos realmente uma nova tabela.
$repo2->addTable($tblPessoas2);

// Mais dados de exemplo
$data2 = [
    [
        'id' => 6,
        'nome' => 'Teodoro',
        'idade' => 80,
        'salario' => 0.0,
        'ativo' => false
    ]
];

// Precisamos reinserir os dados iniciais se quisermos, pois o clone copia apenas a instância de Table, não os dados no SQLite.
$tblPessoas2->fromArray($data1);
$tblPessoas2->fromArray($data2);