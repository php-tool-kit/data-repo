<?php

use PHPUnit\Framework\TestCase;
use Ptk\DataRepo\Table\Table;

final class TableTest extends TestCase {
    
    public function testAddAndGetFieldNames(): void {
        $t = new Table('test');
        $t->addField('f1');
        $t->addField('f2');
        $this->assertEquals($t->getFieldNames(), ['f1', 'f2']);
    }
    
    public function testIsTemporaryTable(): void {
        $t = new Table('test');
        $t->temporaryTable(true);
        $this->assertTrue($t->isTemporary());
    }
    
    public function testIsNotTemporaryTable(): void {
        $t = new Table('test');
        $t->temporaryTable(false);
        $this->assertFalse($t->isTemporary());
    }
    
    public function testIsNotTemporaryTableDefaultValue(): void {
        $t = new Table('test');
        $this->assertFalse($t->isTemporary());
    }
    
    public function testGetSqlBasic(): void {
        $t = new Table('test');
        $t->addField('f1');
        $t->addField('f2');
        $this->assertEquals($t->getSQL(), 'CREATE TABLE  IF NOT EXISTS main.test (f1 TEXT, f2 TEXT)');
    }
    
    public function testGetSqlForTemporaryTable(): void {
        $t = new Table('test');
        $t->addField('f1');
        $t->addField('f2');
        $t->temporaryTable(true);
        $this->assertEquals($t->getSQL(), 'CREATE TABLE TEMPORARY IF NOT EXISTS main.test (f1 TEXT, f2 TEXT)');
    }
}