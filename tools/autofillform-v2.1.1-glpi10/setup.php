<?php
global $CFG_GLPI;
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

define ('PLUGIN_AUTOFILLFORM_VERSION', '2.1.1');
define('PLUGIN_AUTOFILLFORM_MIN_GLPI', '9.4');
define('PLUGIN_AUTOFILLFORM_MAX_GLPI', '11.0');

define("PLUGIN_AUTOFILLFORM_PATH", '/plugins/autofillform');
if (method_exists(Plugin::class, 'getWebDir')) {
    define('PLUGIN_AUTOFILLFORM_DIR', Plugin::getPhpDir('autofillform'));
} else {
    define("PLUGIN_AUTOFILLFORM_DIR", GLPI_ROOT . PLUGIN_AUTOFILLFORM_PATH);
}
if (method_exists(Plugin::class, 'getWebDir')) {
    define('PLUGIN_AUTOFILLFORM_WEB_DIR', Plugin::getWebDir('autofillform'));
} else {
   define('PLUGIN_AUTOFILLFORM_WEB_DIR', $CFG_GLPI['root_doc'] . PLUGIN_AUTOFILLFORM_PATH);
}

if (!defined("PLUGIN_FORMCREATOR_WEB_DIR")) {
    if (method_exists(Plugin::class, 'getWebDir')) {
        define('PLUGIN_FORMCREATOR_WEB_DIR', Plugin::getWebDir('formcreator'));
    } else {
       define('PLUGIN_FORMCREATOR_WEB_DIR', $CFG_GLPI['root_doc'] . '/plugins/formcreator');
    }
}

function plugin_init_autofillform() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $HEADER_LOADED;

   $PLUGIN_HOOKS['csrf_compliant']['autofillform'] = true;

   $plugin = new Plugin();
   if($plugin->isInstalled('autofillform') &&
         $plugin->isActivated('autofillform') &&
         Session::getLoginUserID()) {
      
        $isHelpdesk = plugin_formcreator_replaceHelpdesk();
        $isFormResponse = str_contains($_SERVER['SCRIPT_NAME'], PLUGIN_FORMCREATOR_WEB_DIR.'/front/formdisplay.php');

        if($isFormResponse) {
            $PLUGIN_HOOKS['add_javascript']['autofillform'][] = 'js/autofillform.js.php';
        }
   }
}

function plugin_version_autofillform() {

   $author = '<a href="https://www.saude.go.gov.br/"">Sesgo</a>';

   return [
      'name'           => __('Autofill for form', 'autofillform'),
      'version'        => PLUGIN_AUTOFILLFORM_VERSION,
      'author'         => $author,
      'license'        => 'MIT',
      'homepage'       => 'http://git.saude-go.net/arquitetura/php/glpiPlugin/autofillform',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_AUTOFILLFORM_MIN_GLPI,
            'max' => PLUGIN_AUTOFILLFORM_MAX_GLPI,
         ]
      ]
   ];
}

function plugin_autofillform_check_prerequisites() {
   $prerequisitesSuccess = true;

   if(version_compare(GLPI_VERSION, PLUGIN_AUTOFILLFORM_MIN_GLPI, 'lt') || version_compare(GLPI_VERSION, PLUGIN_AUTOFILLFORM_MAX_GLPI, 'gt')) {
      echo Plugin::messageIncompatible('core', PLUGIN_AUTOFILLFORM_MIN_GLPI, PLUGIN_AUTOFILLFORM_MAX_GLPI);
      $prerequisitesSuccess = false;
   }

   $plugin = new Plugin();
   if(!$plugin->isInstalled('formcreator')) {
      echo 'Instale e ative formcreator';
      $prerequisitesSuccess = false;
   }
   if(!$plugin->isActivated('formcreator')) {
      echo 'Ative formcreator';
      $prerequisitesSuccess = false;
   }

   return $prerequisitesSuccess;
}

function plugin_autofillform_check_config() {
    return true;
}
