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
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function loadSpreadsheet(string $filepath): Spreadsheet {
        $wbook = IOFactory::load($filepath);
        return $wbook;
    }
    
    /**
     * Retorna os dados de uma planilha.
     * 
     * @param Spreadsheet $wbook
     * @param int|string $sheet
     * @return array<mixed>
     */
    public static function readDataFromSheet(Spreadsheet $wbook, int|string $sheet): array {
        $data = [];
        if (is_int($sheet)){
            $data = $wbook->getSheet($sheet)->toArray();
        }
        if (is_string($sheet)){
            $data = $wbook->getSheetByNameOrThrow($sheet)->toArray();
        }
        return $data;
    }
    
}
