<?php

/**
 * Set generic error reporting
 */


// Error reporting level to log. 
// Default: E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT
if (!defined('ERRORS_TO_LOG')) define('ERRORS_TO_LOG', E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
if (!defined('PAGES_TO_DEBUG')) define('PAGES_TO_DEBUG','*');
if (!defined('STRICT_ERROR_REPORTING')) define('STRICT_ERROR_REPORTING',false);



// In CLI mode, or display errors, or nothing
if (defined('IS_CLI') && IS_CLI == 'VERBOSE') {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
} elseif (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
  error_reporting(ERRORS_TO_LOG);
} else {
  error_reporting(0);
}


/**
 * Simple error logging to file through PHP's error handler system
 */
class zc_errors {
	public static $errors = [];
	protected static $file = false;
	private static $date = null;

	// Get logfile location
	public static function logfile() {
		if (!self::$file) {
			if (!defined('DIR_FS_LOGS')) {
			  $val = realpath(dirname(DIR_FS_SQL_CACHE . '/') . '/logs');
			  if (is_dir($val) && is_writable($val)) {
			    define('DIR_FS_LOGS', $val);
			  } else {
			  	// if /logs folder doesn't exist, use /cache folder instead
			    define('DIR_FS_LOGS', DIR_FS_SQL_CACHE);
			  }
			}
			/**
			 * Set path where the debug log file will be located
			 * Default value is: DIR_FS_LOGS . '/debug-YYYY-mm-dd.log'
			 * which puts it in the /logs/ folder eg.: /logs/debug-2014-10-22.log
			 * Remember to protect *.log files in your server configuration!
			*/
		  self::$file = DIR_FS_LOGS . '/debug-' . date('Y-m-d') . '.log';
		}
		return self::$file;
	}

	// Save errors to log file
	public static function save() {
		global $current_page_base;
		// Log on all pages or on selected pages only
		$pages_to_debug = explode(',',PAGES_TO_DEBUG);
		if (in_array('*', $pages_to_debug) || in_array($current_page_base, $pages_to_debug)) {
			if (self::$date === null) self::$date = date('Y-m-d H:i:s');
			@ini_set('log_errors_max_len', 0);	// unlimited length of message output
			if (count(self::$errors)) {
				foreach (self::$errors as $e) {
					error_log(self::$date . ' ' . self::level_name($e['type']) . ' ' . $e['file'] . ' (' . $e['line'] . '): ' . $e['text'] . "\n", 3, self::logfile());
				}
			}
		}
	}

	// Add errors to error array
	public static function err($err) {
		self::$errors[] = $err;
	}

	// Convert error level integers to a meaningful name
	private static function level_name($type) {
    $error_names = [
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_CORE_ERROR => 'E_COMPILE_ERROR',
        E_CORE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    ];
    if (isset($error_names[$type])) return $error_names[$type];
    return 'UNKNOWN';
	}

	public static function display() {
		if (STRICT_ERROR_REPORTING && count(self::$errors)) {
			foreach (self::$errors as $e) {
				echo self::level_name($e['type']) . ' :: ' . $e['file'] . ' (' . $e['line'] . '): ' . $e['text'] . "<br />"; // this really should be thought out!
			}
		}
	}

}



// Wrapper function for error reporting (trigger_error() will call this)
function zc_error_handler($errno, $errstr, $errfile, $errline) {
	zc_errors::err([
		'type' => $errno,
		'text' => $errstr,
		'file' => $errfile,
		'line' => $errline,
	]);
}

// Set error handler to use our own function
set_error_handler('zc_error_handler');

// Handle fatal errors the same as non-fatal
register_shutdown_function(function() {
	$e = error_get_last();
	if ($e["type"] == E_ERROR) zc_error_handler($e["type"], $e["message"], $e["file"], $e["line"]);
	zc_errors::save(); // Save to log file
	zc_errors::display(); // Display errors
});