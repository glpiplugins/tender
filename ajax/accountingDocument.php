
<?php 
include_once ("../../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;
use Config as GlpiConfig;

    $configValues = GlpiConfig::getConfigurationValues('plugin:tender');
    $templateProcessor = new TemplateProcessor($configValues['accounting_template_path']);

    $templateProcessor->setValue('Fachbereich', $_GET['Fachbereich']);
    $templateProcessor->setValue('Abteilung', $_GET['Abteilung']);
    $date = new DateTime();
    $templateProcessor->setValue('Datum', $date->format('d.m.Y'));
    $templateProcessor->setValue('Summe', $_GET['sum']);
    $templateProcessor->setValue('Haushaltsjahr', $_GET['Haushaltsjahr']);
    $templateProcessor->setCheckbox('Ansatz', boolval($_GET['Ansatz']));
    $templateProcessor->setCheckbox('Aufwand', boolval($_GET['Aufwand']));
    $templateProcessor->setValue('Faelligkeit', $_GET['Faelligkeit']);
    $templateProcessor->setValue('Buchungstext', $_GET['Buchungstext']);
    $itemCount = count($_GET['Kosten']);

    for ($i = 0; $i <= 8; $i++) {
        if($i >= $itemCount) {
            $PSK = '';
            $Auftrag = '';
            $Betrag = '';
        } else {
            $PSK = $_GET['Kosten'][$i]['PSK'];
            $Auftrag = $_GET['Kosten'][$i]['Auftrag'];
            $Betrag = $_GET['Kosten'][$i]['Betrag'];
        }
        $templateProcessor->setValue('PSK' . $i + 1, $PSK);
        $templateProcessor->setValue('Auftrag' . $i + 1, $Auftrag);
        $templateProcessor->setValue('Betrag' . $i + 1, $Betrag);
    }
    $invoiceName = $_GET['Rechnungsnummer'];
    // Speichere das bearbeitete Dokument
    header("Content-Disposition: attachment; filename=Kontierung_$invoiceName.docx");
    $templateProcessor->saveAs('php://output');
