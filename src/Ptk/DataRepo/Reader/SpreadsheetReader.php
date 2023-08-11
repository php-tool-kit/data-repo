<?php

namespace Ptk\DataRepo\Reader;

use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Reader para planilhas eletrônicas.
 *
 * @author Everton da Rosa <everton3x@gmail.com>
 */
class SpreadsheetReader {

    /**
     * Carrega uma instância de uma pasta de trabalho.
     * 
     * @param string $filepath
     * @return Spreadsheet
     */
    public static function loadSpreadsheet(string $filepath): Spreadsheet {
        $wb = IOFactory::load($filepath);
        return $wb;
    }
    
    /**
     * Retorna os dados de uma planilha.
     * 
     * @param Spreadsheet $wb
     * @param int|string $sheet
     * @return array<mixed>
     */
    public static function readDataFromSheet(Spreadsheet $wb, int|string $sheet): array {
        $data = [];
        if (is_int($sheet)){
            $data = $wb->getSheet($sheet)->toArray();
        }
        if (is_string($sheet)){
            $data = $wb->getSheetByNameOrThrow($sheet)->toArray();
        }
        return $data;
    }
    
}
