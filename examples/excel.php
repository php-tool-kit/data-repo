<?php

// Lendo dados do Excel (ou outras planilhas eletrônicas).

require_once '../vendor/autoload.php';

use Ptk\DataRepo\DataRepo;
use Ptk\DataRepo\Table\Table;
use Ptk\DataRepo\Field\Field;
use Ptk\DataRepo\Field\FieldTypes;

// Cria o repositório.
$repo = new DataRepo();

// Cria a tabela
$tbl = new Table('exemplo');

// Adiciona os campos à tabela.
$tbl
    ->addField('id', FieldTypes::Int, unique: true, nullable: false)
    ->addField('nome', nullable: false, unique: true)
    ->addField('idade', FieldTypes::Int)
    ->addField('salario', FieldTypes::Real)
    ->addField('ativo', FieldTypes::Int)
;

// Adiciona a tabela ao repositório.
$repo->addTable($tbl);

// Insere os dados na tabela.
$tbl->fromSpreadsheet(realpath('sample_data\example1.xlsx'), 'Plan1');

// Busca os dados inseridos
print_r($tbl->toArray());