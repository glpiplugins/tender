<?php 
include_once ("../../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;
use Config as GlpiConfig;
use GlpiPlugin\Tender\InvoiceModel;

$configValues = GlpiConfig::getConfigurationValues('plugin:tender');
$templateProcessor = new TemplateProcessor($configValues['accounting_template_path']);

// Set values from $_GET array, make sure they are properly encoded
$templateProcessor->setValue('Fachbereich', htmlentities($_GET['Fachbereich'], ENT_QUOTES, 'UTF-8'));
$templateProcessor->setValue('Abteilung', htmlentities($_GET['Abteilung'], ENT_QUOTES, 'UTF-8'));
$date = new DateTime();
$templateProcessor->setValue('Datum', $date->format('d.m.Y'));
$templateProcessor->setValue('Summe', htmlentities($_GET['sum'], ENT_QUOTES, 'UTF-8'));
$templateProcessor->setValue('Haushaltsjahr', htmlentities($_GET['Haushaltsjahr'], ENT_QUOTES, 'UTF-8'));
$templateProcessor->setCheckbox('Ansatz', boolval($_GET['Ansatz']));
$templateProcessor->setCheckbox('Aufwand', boolval($_GET['Aufwand']));
$templateProcessor->setValue('Faelligkeit', htmlentities($_GET['Faelligkeit'], ENT_QUOTES, 'UTF-8'));
$templateProcessor->setValue('Buchungstext', htmlentities($_GET['Buchungstext'], ENT_QUOTES, 'UTF-8'));
$itemCount = count($_GET['Kosten']);

for ($i = 0; $i <= 8; $i++) {
    if($i >= $itemCount) {
        $PSK = '';
        $Auftrag = '';
        $Betrag = '';
    } else {
        $PSK = htmlentities($_GET['Kosten'][$i]['PSK'], ENT_QUOTES, 'UTF-8');
        $Auftrag = htmlentities($_GET['Kosten'][$i]['Auftrag'], ENT_QUOTES, 'UTF-8');
        $Betrag = htmlentities($_GET['Kosten'][$i]['Betrag'], ENT_QUOTES, 'UTF-8');
    }
    $templateProcessor->setValue('PSK' . ($i + 1), $PSK);
    $templateProcessor->setValue('Auftrag' . ($i + 1), $Auftrag);
    $templateProcessor->setValue('Betrag' . ($i + 1), $Betrag);
}
$invoiceName = htmlentities($_GET['Rechnungsnummer'], ENT_QUOTES, 'UTF-8');

// Speichere das bearbeitete Dokument lokal, um die Kodierung zu überprüfen
$tempFilePath = tempnam(sys_get_temp_dir(), 'Kontierung_') . '.docx';
$templateProcessor->saveAs($tempFilePath);

// Schicke das Dokument zum Download
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header("Content-Disposition: attachment; filename=Kontierung_$invoiceName.docx");
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tempFilePath));
readfile($tempFilePath);

// Lösche die temporäre Datei
unlink($tempFilePath);
exit();
