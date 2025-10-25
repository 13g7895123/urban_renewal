<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Excel 匯出共用服務
 *
 * 提供統一的 Excel 匯出功能，避免各 Controller 重複程式碼
 */
class ExcelExportService
{
    /**
     * @var Spreadsheet
     */
    private $spreadsheet;

    /**
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    private $sheet;

    /**
     * @var int
     */
    private $currentRow = 1;

    /**
     * 建構函式
     */
    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    /**
     * 設定文件屬性
     *
     * @param string $creator 建立者
     * @param string $title 標題
     * @param string $subject 主旨
     * @param string $description 描述
     * @return self
     */
    public function setDocumentProperties(string $creator, string $title, string $subject = '', string $description = ''): self
    {
        $this->spreadsheet->getProperties()
            ->setCreator($creator)
            ->setTitle($title)
            ->setSubject($subject)
            ->setDescription($description);

        return $this;
    }

    /**
     * 設定標題列（大標題，通常跨多欄）
     *
     * @param string $title 標題文字
     * @param string $startCol 起始欄位 (例如: 'A')
     * @param string $endCol 結束欄位 (例如: 'G')
     * @param int $fontSize 字型大小
     * @return self
     */
    public function setTitle(string $title, string $startCol = 'A', string $endCol = 'G', int $fontSize = 16): self
    {
        $this->sheet->setCellValue($startCol . $this->currentRow, $title);
        $this->sheet->mergeCells($startCol . $this->currentRow . ':' . $endCol . $this->currentRow);
        $this->sheet->getStyle($startCol . $this->currentRow)->getFont()->setBold(true)->setSize($fontSize);
        $this->sheet->getStyle($startCol . $this->currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $this->currentRow++;

        return $this;
    }

    /**
     * 新增資訊列（鍵值對格式，例如：會議名稱：XXX）
     *
     * @param string $label 標籤
     * @param string $value 值
     * @param string $labelCol 標籤欄位 (例如: 'A')
     * @param string $valueCol 值欄位 (例如: 'B')
     * @param string|null $mergeToCol 合併到哪一欄 (例如: 'D')
     * @return self
     */
    public function addInfoRow(string $label, string $value, string $labelCol = 'A', string $valueCol = 'B', ?string $mergeToCol = null): self
    {
        $this->sheet->setCellValue($labelCol . $this->currentRow, $label);
        $this->sheet->setCellValue($valueCol . $this->currentRow, $value);

        if ($mergeToCol) {
            $this->sheet->mergeCells($valueCol . $this->currentRow . ':' . $mergeToCol . $this->currentRow);
        }

        $this->currentRow++;

        return $this;
    }

    /**
     * 新增空白列
     *
     * @param int $count 空白列數量
     * @return self
     */
    public function addEmptyRows(int $count = 1): self
    {
        $this->currentRow += $count;

        return $this;
    }

    /**
     * 新增區段標題
     *
     * @param string $title 標題文字
     * @param int $fontSize 字型大小
     * @return self
     */
    public function addSectionTitle(string $title, int $fontSize = 14): self
    {
        $this->sheet->setCellValue('A' . $this->currentRow, $title);
        $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setBold(true)->setSize($fontSize);

        $this->currentRow++;

        return $this;
    }

    /**
     * 新增表格標頭
     *
     * @param array $headers 標頭陣列 (例如: ['欄位1', '欄位2', '欄位3'])
     * @param bool $withBorder 是否加上邊框
     * @param bool $withBackground 是否加上背景色
     * @return self
     */
    public function addTableHeader(array $headers, bool $withBorder = true, bool $withBackground = true): self
    {
        $col = 'A';
        $endCol = chr(ord('A') + count($headers) - 1);

        foreach ($headers as $header) {
            $this->sheet->setCellValue($col . $this->currentRow, $header);
            $col++;
        }

        // 設定樣式
        $range = 'A' . $this->currentRow . ':' . $endCol . $this->currentRow;
        $this->sheet->getStyle($range)->getFont()->setBold(true);

        if ($withBackground) {
            $this->sheet->getStyle($range)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD3D3D3');
        }

        if ($withBorder) {
            $this->sheet->getStyle($range)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }

        $this->currentRow++;

        return $this;
    }

    /**
     * 新增表格資料列
     *
     * @param array $data 資料陣列（二維陣列）
     * @param bool $withBorder 是否加上邊框
     * @param int|null $columnCount 欄位數量（用於邊框範圍）
     * @return self
     */
    public function addTableData(array $data, bool $withBorder = true, ?int $columnCount = null): self
    {
        foreach ($data as $row) {
            $col = 'A';

            foreach ($row as $value) {
                $this->sheet->setCellValue($col . $this->currentRow, $value);
                $col++;
            }

            if ($withBorder) {
                $endCol = $columnCount ? chr(ord('A') + $columnCount - 1) : chr(ord($col) - 1);
                $range = 'A' . $this->currentRow . ':' . $endCol . $this->currentRow;
                $this->sheet->getStyle($range)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            }

            $this->currentRow++;
        }

        return $this;
    }

    /**
     * 新增單一資料列
     *
     * @param array $rowData 單列資料
     * @param bool $bold 是否粗體
     * @param bool $withBorder 是否加上邊框
     * @param int|null $columnCount 欄位數量
     * @return self
     */
    public function addRow(array $rowData, bool $bold = false, bool $withBorder = false, ?int $columnCount = null): self
    {
        $col = 'A';

        foreach ($rowData as $value) {
            $this->sheet->setCellValue($col . $this->currentRow, $value);
            $col++;
        }

        $endCol = $columnCount ? chr(ord('A') + $columnCount - 1) : chr(ord($col) - 1);
        $range = 'A' . $this->currentRow . ':' . $endCol . $this->currentRow;

        if ($bold) {
            $this->sheet->getStyle($range)->getFont()->setBold(true);
        }

        if ($withBorder) {
            $this->sheet->getStyle($range)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }

        $this->currentRow++;

        return $this;
    }

    /**
     * 自動調整欄寬
     *
     * @param string $startCol 起始欄位 (例如: 'A')
     * @param string $endCol 結束欄位 (例如: 'G')
     * @return self
     */
    public function autoSizeColumns(string $startCol = 'A', string $endCol = 'Z'): self
    {
        foreach (range($startCol, $endCol) as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this;
    }

    /**
     * 儲存檔案到指定路徑
     *
     * @param string $filename 檔案名稱（不含路徑）
     * @param string|null $directory 目錄路徑（預設為 WRITEPATH/exports）
     * @return string 完整檔案路徑
     */
    public function saveToFile(string $filename, ?string $directory = null): string
    {
        if ($directory === null) {
            $directory = WRITEPATH . 'exports/';
        }

        // 確保目錄存在
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filepath = $directory . $filename;

        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    /**
     * 直接輸出到瀏覽器（下載）
     *
     * @param string $filename 檔案名稱
     * @return void
     */
    public function download(string $filename): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Expose-Headers: Content-Disposition');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * 取得 Spreadsheet 物件（進階使用）
     *
     * @return Spreadsheet
     */
    public function getSpreadsheet(): Spreadsheet
    {
        return $this->spreadsheet;
    }

    /**
     * 取得 Worksheet 物件（進階使用）
     *
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * 取得當前列號
     *
     * @return int
     */
    public function getCurrentRow(): int
    {
        return $this->currentRow;
    }

    /**
     * 設定當前列號
     *
     * @param int $row 列號
     * @return self
     */
    public function setCurrentRow(int $row): self
    {
        $this->currentRow = $row;

        return $this;
    }
}
