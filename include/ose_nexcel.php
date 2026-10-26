<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/include/main_config_session.php';
include $_SERVER['DOCUMENT_ROOT'] . '/include/util_core.php';
include $_SERVER['DOCUMENT_ROOT'] . '/include/util.php';
include $_SERVER['DOCUMENT_ROOT'] . '/include/main_config_db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/include/main_config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/include/main_config_lang.php';

// PhpSpreadsheet autoload (composer)
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;

// 권한체크
$mAuth = validMenu($mid, "", $db);
if ($mAuth == "") {
    exit;
}

if ($userlevel < substr($mAuth, 0, 1)) {
    exit;
}

$CanRead = "Y";
if ($CanRead !== "Y") {
    exit;
}

$ExcelHeader = getParam("ExcelHeader");
$ExcelTitle  = getParam("ExcelTitle") . ".xlsx";
$ExcelType   = getParam("ExcelType");
$ExcelQuery  = getParam("ExcelQuery");
$ExcelWidth  = getParam("ExcelWidth");

addLog($userno, "엑셀다운로드: " . $ExcelTitle, $db);

function excel_column_char(int $i): string {
    if ($i < 26) {
        return chr(65 + $i);
    } else {
        return chr(65 + (int)floor($i / 26) - 1) . chr(65 + ($i % 26));
    }
}

$headers   = explode(",", $ExcelHeader);
$celltype  = explode(",", $ExcelType);
$cellwidth = explode(",", $ExcelWidth);

if (count($headers) !== count($celltype) || count($headers) !== count($cellwidth)) {
    exit("Excel setting mismatch");
}

$last_char = excel_column_char(count($headers) - 1);

// -----------------------------------------------
// PhpSpreadsheet 객체 생성 (PHPExcel → Spreadsheet)
// -----------------------------------------------
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 기본 폰트 설정
$spreadsheet->getDefaultStyle()->getFont()->setName('맑은 고딕')->setSize(10);

// 전체 열 가운데 정렬
$sheet->getStyle("A:{$last_char}")->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 헤더 행 배경색
$sheet->getStyle("A1:{$last_char}1")->getFill()
      ->setFillType(Fill::FILL_SOLID)
      ->getStartColor()->setARGB('FFF3F3F3'); // ARGB: FF + RRGGBB

// 헤더 및 열 너비 설정
for ($i = 0; $i < count($headers); $i++) {
    $col   = excel_column_char($i);
    $cwidth = round(((float)$cellwidth[$i]) * 10 / 75, 3);

    $sheet->setCellValue($col . '1', $headers[$i]);
    $sheet->getColumnDimension($col)->setWidth($cwidth);
}

// -----------------------------------------------
// 데이터 출력
// -----------------------------------------------
$seq    = 1;
$result = mysqli_query($db, $ExcelQuery);

if (!$result) {
    exit(mysqli_error($db));
}

while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
    $rowNum = $seq + 1;

    $sheet->setCellValue('A' . $rowNum, $seq);

    for ($i = 1; $i < count($headers); $i++) {
        $col     = excel_column_char($i);
        $cellRef = $col . $rowNum;
        $exValue = $row[$i] ?? '';

        // 날짜 포맷 (B)
        if ($celltype[$i] === 'B') {
            $exValue = FormatDate($exValue);

        // 타임스탬프 → 날짜시간 (T)
        } elseif ($celltype[$i] === 'T') {
            $exValue = (!empty($exValue) && is_numeric($exValue))
                ? date('Y-m-d H:i:s', (int)$exValue)
                : '';

        // 하이퍼링크 (L)
        } elseif ($celltype[$i] === 'L') {
            $nValue = explode('|@|', $exValue);
            if (
                isset($nValue[1], $nValue[2]) &&
                $nValue[1] !== '' &&
                $nValue[2] !== ''
            ) {
                $sheet->getCell($cellRef)->setHyperlink(
                    new Hyperlink(
                        'https://tutoring.yonsei.ac.kr/downloadfile.php?mid=A01&lang=k&uid=' . $nValue[1]
                    )
                );
            }
            $exValue = $nValue[0] ?? '';
        }

        // 숫자 포맷 (F: 소수, D: 정수)
        if ($celltype[$i] === 'F') {
            $sheet->getStyle($cellRef)->getNumberFormat()
                  ->setFormatCode('#,##0.00');
        } elseif ($celltype[$i] === 'D') {
            $sheet->getStyle($cellRef)->getNumberFormat()
                  ->setFormatCode('#,##0');

        // 좌측 정렬 (NL)
        } elseif ($celltype[$i] === 'NL') {
            $sheet->getStyle($cellRef)->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $sheet->setCellValue($cellRef, $exValue);
    }

    $seq++;
}

// -----------------------------------------------
// 파일 출력
// -----------------------------------------------
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . rawurlencode($ExcelTitle) . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer->save('php://output');
exit;