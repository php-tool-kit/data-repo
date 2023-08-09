<?php

namespace Ptk\DataRepo\Table;

use Ptk\DataRepo\Field;

class Table {

    public function __construct(string $name, Field ...$fields) {

    }

    public function addField(Field $field): Table {

    }

    public function getSQL(): string {

    }

    public function getFields(): array {

    }

    public function getFieldNames(): array {

    }

    public function getFieldTypes(): array {

    }
}