<?php

use GlpiPlugin\Tender\Config;
use Config as GlpiConfig;

include('../../../inc/includes.php');

$config = new Config();
if (isset($_POST["add"])) {
   $config->check(-1, CREATE, $_POST);

   $config->add($_POST);

   Html::back();

}
 else if (isset($_POST["update"])) {
   if($_POST['_new_accounting_template']) {

      $tmp_dir = GLPI_TMP_DIR . '/';
      $tmp_name = $_POST["_new_accounting_template"][0];
      $tmp_file = $tmp_dir . $tmp_name;
      $new_name = str_replace($_POST['_prefix_new_accounting_template'], '', $tmp_name);
      $accounting_template = GLPI_ROOT .'/files/_plugins/tender/docx/' . $new_name;
      rename($tmp_file, $accounting_template);
      GlpiConfig::setConfigurationValues('plugin:tender',                 [
            'accounting_template_name' => $new_name,
            'accounting_template_path' => $accounting_template,
      ]);
   }

   $config->update($_POST);
   Html::back();

} else {
   Html::header(
      __("Tender", "tender"),
      $_SERVER['PHP_SELF'],
      "config",
      "tender",
      "tender"
   );
   $config->showForm(1);
   Html::footer();
}