<?php
namespace Common\Spreadsheet;

/**
 * Manages the Read filter of Excel
 **/
class XlsReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{

    /**
     * ReadCell filter function
     *
     * @param string $column
     * @param int $row
     * @param string $worksheetName
     * @return bool
     */
    public function readCell($column, $row, $worksheetName = '')
    {
        // statement to pass lint
        if ($column === '' || $row <= 0 || $worksheetName === '<noinclude>') {
            return false;
        }
        // Read rows except heading/title row
        if ($row > 1) {
            return true;
        }
        return false;
    }

}
