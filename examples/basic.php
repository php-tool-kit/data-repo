<?php

require_once '../vendor/autoload.php';

use Ptk\DataRepo\DataRepo;
use Ptk\DataRepo\Table\Table;
use Ptk\DataRepo\Field\Field;
use Ptk\DataRepo\Field\FieldTypes;

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

$repo = new DataRepo();

$tblPessoas = new Table('pessoas');
$tblPessoas
    ->addField('id', FieldTypes::Int, unique: true, nullable: false)
    ->addField('nome', nullable: false, unique: true)
    ->addField('idade', FieldTypes::Int)
    ->addField('salario', FieldTypes::Real)
    ->addField('ativo', FieldTypes::Int)
;

$repo->addTable($tblPessoas);

$tblPessoas->insertData($data);