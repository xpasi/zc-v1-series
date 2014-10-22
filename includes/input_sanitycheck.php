<?php

namespace zencart\inoculate;

/*
 * Run a range of tests agains input variables
 */

$contaminated = (isset($_FILES['GLOBALS']) || isset($_REQUEST['GLOBALS'])) ? true : false;

if (!$contaminated) {
	foreach([
	  'GLOBALS',
	  '_COOKIE',
	  '_ENV',
	  '_FILES',
	  '_GET',
	  '_POST',
	  '_REQUEST',
	  '_SERVER',
	  '_SESSION',
	  'HTTP_COOKIE_VARS',
	  'HTTP_ENV_VARS',
	  'HTTP_GET_VARS',
	  'HTTP_POST_VARS',
	  'HTTP_POST_FILES',
	  'HTTP_RAW_POST_DATA',
	  'HTTP_SERVER_VARS',
	  'HTTP_SESSION_VARS',
	  'autoLoadConfig',
	  'mosConfig_absolute_path',
	  'hash',
	  'main',
	] as $key) {
	  if (isset($_GET[$key]) || isset($_POST[$key]) || isset($_COOKIE[$key])) {
	    $contaminated = true;
	    break;
	  }
	}
}

/*
 * Length check for various values
*/

if (!$contaminated) {
  foreach([
      'main_page',
      'cPath',
      'products_id',
      'language',
      'currency',
      'action',
      'manufacturers_id',
      'pID',
      'pid',
      'reviews_id',
      'filter_id',
      'zenid',
      'sort',
      'number_of_uploads',
      'notify',
      'page_holder',
      'chapter',
      'alpha_filter_id',
      'typefilter',
      'disp_order',
      'id',
      'key',
      'music_genre_id',
      'record_company_id',
      'set_session_login',
      'faq_item',
      'edit',
      'delete',
      'search_in_description',
      'dfrom',
      'pfrom',
      'dto',
      'pto',
      'inc_subcat',
      'payment_error',
      'order',
      'gv_no',
      'pos',
      'addr',
      'error',
      'count',
      'error_message',
      'info_message',
      'cID',
      'page',
      'credit_class_error_code',
    ] as $key) {
    if (isset($_GET[$key]) && !is_array($_GET[$key])) {
    	$len = 43;
    	if (in_array($key, array('zenid', 'error_message', 'payment_error'))) $len = 255;
      if (($len > 0 && strlen($_GET[$key]) > $len) || (substr($_GET[$key], 0, 4) == 'http' || strstr($_GET[$key], '//'))) {
        $contaminated = true;
        break;
      }
    }
  }
  unset($len);
}

if (isset($key)) unset($key);

if ($contaminated) {
  header('HTTP/1.1 406 Not Acceptable');
  trigger_error('Contaminated value in input detected', E_USER_ERROR);
}
unset($contaminated);