<?php

namespace Ptk\DataRepo\Field;

enum FieldTypes: string {
    case Null = 'NULL';
    case Int = 'INTEGER';
    case Real = 'REAL';
    case Text = 'TEXT';
    case Blob = 'BLOB';
    
}