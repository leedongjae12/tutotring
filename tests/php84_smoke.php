<?php
require dirname(__DIR__) . '/include/util_core.php';
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
function check($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
$_POST = [];
$_GET = [];
check(getParam('missing') === '', 'Missing parameter');
$_POST['mid'] = ' post ';
check(getParam('mid') === 'post', 'POST parameter trim');
$_GET['mid'] = ' get ';
check(getParam('mid') === 'get', 'Existing GET precedence');
$_GET['mid'] = ['unexpected'];
check(getParam('mid') === '', 'Array parameter handling');
$_GET['mid'] = " O'Reilly ";
check(getParam('mid') === "O'Reilly", 'Preserve quotes without magic quotes');
$plain = 'user|@|name|@|7';
check(decrypt_str(encrypt_str($plain, $enc_key), $enc_key) === $plain, 'Encryption round trip');
require dirname(__DIR__) . '/vendor/autoload.php';
$sheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet->getActiveSheet()->setCellValue('A1', 'PHP 8.4');
$path = tempnam(sys_get_temp_dir(), 'tutotring-test-');
try {
    (new PhpOffice\PhpSpreadsheet\Writer\Xlsx($sheet))->save($path);
    $loaded = PhpOffice\PhpSpreadsheet\IOFactory::load($path);
    check($loaded->getActiveSheet()->getCell('A1')->getValue() === 'PHP 8.4', 'XLSX write/read');
    $loaded->disconnectWorksheets();
} finally {
    unlink($path);
    $sheet->disconnectWorksheets();
}
echo "Passed: input handling, encryption round trip, XLSX write/read.\n";
