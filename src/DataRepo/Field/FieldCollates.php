<?php

namespace Ptk\DataRepo\Field;

enum FieldCollates: string {
    case Binary = 'BINARY';
    case NoCase = 'NOCASE';
    case RTrim = 'RTRIM';
    case UTF16 = 'UTF16';
    case UTF6CI = 'UTF6CI';
    
}