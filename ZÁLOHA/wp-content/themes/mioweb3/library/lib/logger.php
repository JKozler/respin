<?php
/**
 * Mioweb logging capabilities. See {@link MwLogger} for detailed information.
 *
 * User: kuba
 * Date: 10.08.16
 * Time: 16:04
 */

/** WordPress Administration File API */
require_once(ABSPATH . 'wp-admin/includes/file.php');

/**
 * Mioweb Log Sources
 */

const MWLS_GENERAL = 'general';
const MWLS_MEMBER = 'member';
const MWLS_SHOP = 'shop';
const MWLS_FAPI = 'fapi';

/**
 * MIOWEB LOG LEVELS allow to set importance of a message.
 * The closer to 0 the value is, the more important the message is.
 *
 * Levels can be used filter out unnecessary messages. Only messages with higher importance then set importance will
 * go through. Less imporant messages will be filtered out.
 */

/** Mioweb log level - special constant to disable logging */
const MWLL_DISABLED = 0;
/** Mioweb log level - errors */
const MWLL_ERROR = 10;
/** Mioweb log level - warnings */
const MWLL_WARNING = 20;
/** Mioweb log level - info */
const MWLL_INFO = 30;
/** Mioweb log level - debug */
const MWLL_DEBUG = 40;
/** Mioweb log level - special case to inherit source level from global level */
const MWLL_USE_GLOBAL = -1;

/**
 * Add a message from a source into log. See {@link MwLogger::add} for further information.
 */
function mwlog($src, $message, $level = MWLL_INFO, $ctg = '')
{
	// Suppressed categories.
	if (in_array($ctg, ['save'])) {
		return;
	}

	global $mwLoggerInstance;
	$mwLoggerInstance->add($src, (empty($ctg) ? '' : '[' . strtoupper($ctg) . '] ') . $message, $level);
}

/** Add a flash message. */
function mwnotice($user = '', $admin = '')
{
}

/**
 * Logging support for Mioweb. It can write messages into file logs. Messages are output in separate files according to
 * their source. Every message has an importance level (error, warning, info, debug).
 *
 * Default settings can be redefined by defining following values.
 *     MW_LOG_DIR - absolute path to the folder where log files will be saved, default is 'wp_root/log/mioweb'
 *   MW_LOG_SINGLEFILENAME - define this and messages from all sources will be saved into a single file with this name;
 *                         - define it to empty string and each source will generate its own file
 *   MW_LOG_LEVEL - initial global logging level, default level is {@link MWLL_INFO}
 *
 * @class MwLogger
 * @property string $singleFilename If not empty then all messages are stored into a single filename with this name.
 *                                  Suffix ".log" is automatically appended.
 */
class MwLogger
{

	/** @var string Global level of logging. If a source does not use specific level then this level is used. */
	private $_globalLevel = MWLL_INFO;

	/** @var array List of levels for specific sources. */
	private $_srcLevel = [];

	/** @var array List of opened file handles. */
	private $_files = [];

	/** @var array List of files that can not be written. */
	private $_disabledFiles = [];

	/** @var bool Name of the composite log file. */
	private $_singleFilename = '';

	/** @var array Translation table from numbers to strings. */
	private $_levelStrings = [];

	public function __construct()
	{
		$this->singleFilename = defined('MW_LOG_SINGLEFILENAME') ? MW_LOG_SINGLEFILENAME : 'mioweb';
		if (defined('MW_LOG_LEVEL') && is_int(MW_LOG_LEVEL)) {
			$this->setGlobalLevel(MW_LOG_LEVEL);
		}

		$this->_levelStrings[MWLL_ERROR] = 'ERROR';
		$this->_levelStrings[MWLL_WARNING] = 'WARNING';
		$this->_levelStrings[MWLL_INFO] = 'INFO';
		$this->_levelStrings[MWLL_DEBUG] = 'DEBUG';
	}

	function __destruct()
	{
		$this->closeAll();
	}

	/** Global instance of the logger. */
	public static function instance()
	{
		global $mwLoggerInstance;

		return $mwLoggerInstance;
	}

	/**
	 * Add a message from a source into log.
	 *
	 * @param string $src Source of the message. Messages from different sources goes typically into separate files.
	 *                    See predefined constants like {@link MWLS_GENERAL}, {@link MWLS_MEMBER}, {@link MWLS_SHOP}.
	 *                    See {@link MwLogger::singleFilename} to compose a single log file.
	 * @param string $message Text of the message.
	 * @param int $level Importance of the message. There are predefined constants {@link MWLL_ERROR}, {@link MWLL_WARNING},
	 *                   {@link MWLL_INFO}, {@link MWLL_DEBUG}.
	 */
	public function add($src, $message, $level = MWLL_INFO)
	{
		$enabled = $this->getLevel($src);
		if (MWLL_DISABLED < $level && $level <= $enabled) {
			// Write log message.
			if ($this->open($src) && is_resource($this->_files[$src])) {
				$time = date_i18n('Ymd H:i:s');
				$result = fwrite(
					$this->_files[$src],
					$time
					. "\t" . ($this->_levelStrings[$level] ?? $level)
					. "\t" . sprintf('%6d', getmypid()) . (empty($this->_singleFilename) ? '' : ':' . $src)
					. "\t" . $message
					. "\n"
				);
			}
		}
	}

	/**
	 * Change source logging level. This overwrites global level. That means that even when global level says DISABLED,
	 * logging for a source can be still ENABLED.
	 *
	 * @param int $level New level. Use predefined "MWLL_" constants, like {@link MWLL_DISABLED}, {@link MWLL_USE_GLOBAL},
	 * {@link MWLL_ERROR} etc.
	 */
	public function setLevel($src, $level = MWLL_USE_GLOBAL)
	{
		$src = (string) $src;
		$level = (int) $level;
		if (empty($src)) {
			if ($level === MWLL_USE_GLOBAL) {
				return;
			}
			$this->_globalLevel = $level;
		} else {
			if ($level === MWLL_USE_GLOBAL) {
				unset($this->_srcLevel[$src]);
			} else {
				$this->_srcLevel[$src] = $level;
			}
		}
	}

	protected function getLevel($src)
	{
		$src = (string) $src;

		return empty($src) || !isset($this->_srcLevel[$src]) ? $this->_globalLevel : $this->_srcLevel[$src];
	}

	/**
	 * Change global logging level. This level can be overwritten for each source.
	 *
	 * @param int $level New level. Use predefined "MWLL_" constants, like {@link MWLL_DISABLED}, {@link MWLL_ERROR} etc.
	 */
	public function setGlobalLevel($level)
	{
		$this->setLevel('', $level);
	}

	function __get($name)
	{
		if ($name === 'singleFilename') {
			return $this->_singleFilename;
		}
	}

	function __set($name, $value)
	{
		if ($name === 'singleFilename') {
			if ($this->_singleFilename != $value) {
				$this->closeAll();
			}
			$this->_singleFilename = $value;
		}
	}

	/**
	 * Open output file for a source and save its handle.
	 *
	 * @param string $src Source name
	 * @param string $mode File mode
	 * @return bool Success
	 */
	protected function open($src, $mode = 'a')
	{
		// Quick scenario for already tested source
		if (isset($this->_files[$src])) {
			return true;
		}

		if (isset($this->_disabledFiles[$src])) {
			return false;
		}
		$logFilename = $this->getLogFilename($src);
		if ($this->_files[$src] = @fopen($logFilename, $mode)) {
			return true;
		} else {
			// Try to create folder at first
			$logDir = $this->getLogDir();
			if (file_exists($logDir) && is_dir($logDir) || wp_mkdir_p($logDir)) {
				if ($this->_files[$src] = @fopen($logFilename, $mode)) {
					// Add .htaccess protection for new folder.
					$this->protectAccessToLogDirectory(true);

					return true;
				}
			}
			$this->_disabledFiles[$src] = true; //Mark the file as disabled
		}
		unset($this->_files[$src]);

		return false;
	}

	/**
	 * Close opened file for a source.
	 *
	 * @param string $src Source name
	 * @return bool Success
	 */
	protected function close($src)
	{
		$result = false;
		if (is_resource($this->_files[$src])) {
			$result = fclose($this->_files[$src]);
			unset($this->_files[$src]);
		}
		unset($this->_disabledFiles[$src]);

		return $result;
	}

	/** Close all opened output files. */
	protected function closeAll()
	{
		foreach ($this->_files as $file) {
			if (is_resource($file)) {
				fclose($file);
			}
		}
		$this->_files = [];
		$this->_disabledFiles = [];
	}

	/**
	 * Get file log path for a source
	 *
	 * @param string $src
	 * @return string Absolute path to a file.
	 */
	private function getLogFilename($src)
	{
		$src = !empty($this->_singleFilename) ? $this->_singleFilename : 'mw_' . $src;

		$path = $this->getLogDir() . sanitize_file_name($src) . '.log';

		return $path;
	}

	/**
	 * Get directory where logs are stored. Default directory can be modified by defining "MW_LOG_DIR" with an absolute path
	 * with write access.
	 *
	 * @return string
	 */
	private function getLogDir()
	{
		return trailingslashit(MW_LOG_DIR);
	}

	/**
	 * Change protection of the log folder against public access.
	 *
	 * @param bool $enableProtection Set to true to enable .htaccess directory protection. Set false to disable it.
	 */
	public function protectAccessToLogDirectory($enableProtection)
	{
		$logDir = $this->getLogDir();
		if ($enableProtection) {
			// Add rules to the htaccess file
			if (!file_exists($logDir . '.htaccess')) {
				if ($file_handle = @fopen($logDir . '.htaccess', 'w')) {
					fwrite($file_handle, 'deny from all');
					fclose($file_handle);
				}
			}
		} else {
			// Don't enable protection
			if (file_exists($logDir . '.htaccess')) {
				unlink($logDir . '.htaccess');
			}
		}
	}

}

// Initialize global logging instance.
global $mwLoggerInstance;
$mwLoggerInstance = new MwLogger();

// Hruby test par scenaru
//if (false) {
//	$mwLoggerInstance->setGlobalLevel(MWLL_INFO);
//	mwlog(MWLS_GENERAL, 'test logu', MWLL_INFO);
//	mwlog(MWLS_GENERAL, 'test logu2', MWLL_ERROR);
//	$mwLoggerInstance->singleFilename = 'mioweb';
//	$mwLoggerInstance->setLevel(MWLS_GENERAL, MWLL_ERROR);
//	mwlog(MWLS_GENERAL, 'test logu single', MWLL_INFO);
//	mwlog(MWLS_GENERAL, 'test logu2 single', MWLL_ERROR);
//	//$mwLoggerInstance->protectAccessToLogDirectory(false);
//}

$mwLoggerInstance->singleFilename = 'mioweb';
