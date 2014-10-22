<?php
/**
 * application_top.php Common actions carried out at the start of each page invocation.
 *
 * Initializes common classes & methods. Controlled by an array which describes
 * the elements to be initialised and the order in which that happens.
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:
 */

/**
 * Inoculate against hack attempts which waste CPU cycles
 */
require('includes/input_sanitycheck.php');


/**
 * boolean used to see if we are in the admin script, obviously set to false here.
 */
define('IS_ADMIN_FLAG', false);
/**
 * integer saves the time at which the script started.
 */
define('PAGE_PARSE_START_TIME', microtime());
@ini_set("arg_separator.output","&");
@ini_set("html_errors","0");
/**
 * Load the local configuration parameters if they exists - mainly for developers
 */
if (file_exists('includes/local/configure.php')) {
  include('includes/local/configure.php');
}

/**
 * set the level of error reporting
 */
require('includes/error_reporting.php');

/*
 * Get time zone info from PHP config
 */
@date_default_timezone_set(date_default_timezone_get());
/**
 * check for and include load application parameters
 */
if (file_exists('includes/configure.php')) {
  /**
   * load the main configure file.
   */
  include('includes/configure.php');
} else {
  $problemString = 'includes/configure.php not found';
  require('includes/templates/template_default/templates/tpl_zc_install_suggested_default.php');
  exit;
}
/**
 * if main configure file doesn't contain valid info (ie: is dummy or doesn't match filestructure, display assistance page to suggest running the installer)
 */
if (!defined('DIR_FS_CATALOG') || !is_dir(DIR_FS_CATALOG.'includes/classes')) {
  $problemString = 'includes/configure.php file contents invalid.  ie: DIR_FS_CATALOG not valid or not set';
  require('includes/templates/template_default/templates/tpl_zc_install_suggested_default.php');
  exit;
}
/**
 * check for and load system defined path constants
 */
if (file_exists('includes/defined_paths.php')) {
  /**
   * load the system-defined path constants
   */
  require('includes/defined_paths.php');
} else {
  trigger_error('/includes/defined_paths.php file not found.', E_USER_ERROR);
  exit;
}
/**
 * include the extra_configures files
 */
if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_configures')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('~^[^\._].*\.php$~i', $zv_file) > 0) {
      /**
       * load any user/contribution specific configuration files.
       */
      include(DIR_WS_INCLUDES . 'extra_configures/' . $zv_file);
    }
  }
  $za_dir->close();
  unset($za_dir);
}
$systemContext = 'store';
$autoLoadConfig = array();
if (isset($loaderPrefix)) {
 $loaderPrefix = preg_replace('/[^a-z_]/', '', $loaderPrefix);
} else {
  $loaderPrefix = 'config';
}
$loader_file = $loaderPrefix . '.core.php';
require('includes/initsystem.php');
/**
 * determine install status
 */
if (( (!file_exists('includes/configure.php') && !file_exists('includes/local/configure.php')) ) || (DB_TYPE == '') || (!file_exists('includes/classes/db/' .DB_TYPE . '/query_factory.php')) || !file_exists('includes/autoload_func.php')) {
  $problemString = 'includes/configure.php file empty or file not found, OR wrong DB_TYPE set, OR cannot find includes/autoload_func.php which suggests paths are wrong or files were not uploaded correctly';
  require('includes/templates/template_default/templates/tpl_zc_install_suggested_default.php');
  header('location: zc_install/index.php');
  exit;
}
/**
 * load the autoloader interpreter code.
*/
require('includes/autoload_func.php');

// get customer's unique IP that external gateway does not touch
$customers_ip_address = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['customers_ip_address'])) {
  $_SESSION['customers_ip_address'] = $customers_ip_address;
}
