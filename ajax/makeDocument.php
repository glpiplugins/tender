<?php 
include_once ("../../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;
use PhpOffice\PhpWord\TemplateProcessor;
use Config as GlpiConfig;
use GlpiPlugin\Tender\DocumentTemplateModel;
use GlpiPlugin\Tender\InvoiceModel;
use Illuminate\Support\Arr;

$id = $_GET['id'];
$itemtype = $_GET['itemtype'];
$documentName = 'default';

$documentTemplate = DocumentTemplateModel::where('itemtype', $itemtype)->with('parameters')->first()->parameters->map(function ($item) {
  return collect($item)->only(['name', 'type', 'value'])->toArray();
})->toArray();

switch ($itemtype) {
  default:
    $document = InvoiceModel::find($id);
    $data = $document->getDocumentData();
    $documentTemplate = array_merge($documentTemplate, $document->getDocumentTemplate());
    $documentName = $document->name;
}


$configValues = GlpiConfig::getConfigurationValues('plugin:tender');
$templateProcessor = new TemplateProcessor($configValues['accounting_template_path']);

foreach ($documentTemplate as &$item) {

  $name = $item['name'];
  if (array_key_exists($name, $data)) {
      if (is_array($data[$name])) {
          $item['value'] = $data[$name];
      } else {
          $item['value'] = $data[$name];
      }
  }

    switch($item['type']) {
        case 'checkbox':
            $templateProcessor->setCheckbox($item['name'], boolval($item['value']));
            break;
        case 'array':
                for ($i = 0; $i <= 8; $i++) {
                  $keys = array_keys($item['value'][0]);
    
                  foreach($keys as $key) {
                    if($i >= count($item['value'])) {
                        $value = '';
                    } else {
                        $value = $item['value'][$i][$key];
        
                    }
                    if(is_null($value)) {
                        $value = '';
                    }
                    $templateProcessor->setValue($key . $i, htmlentities($value, ENT_QUOTES, 'UTF-8'));
                  }
                }
            break;
        case 'date':
            $value = DateTime::createFromFormat('Y-m-d', $item['value'])->format('d.m.Y');
            $templateProcessor->setValue($item['name'], htmlentities($value, ENT_QUOTES, 'UTF-8'));
        case 'current_date':
            $date = new DateTime();
            $value = $date->format('d.m.Y');
            $templateProcessor->setValue($item['name'], htmlentities($item['value'], ENT_QUOTES, 'UTF-8'));
            break;
        default:
            $value = $item['value'];
            $templateProcessor->setValue($item['name'], htmlentities($item['value'], ENT_QUOTES, 'UTF-8'));

    }

}

$documentName = htmlentities($documentName, ENT_QUOTES, 'UTF-8');

$tempFilePath = tempnam(sys_get_temp_dir(), 'Kontierung_') . '.docx';
$templateProcessor->saveAs($tempFilePath);

header('Content-Description: File Transfer');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header("Content-Disposition: attachment; filename=Kontierung_$documentName.docx");
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tempFilePath));
readfile($tempFilePath);

unlink($tempFilePath);
exit();
