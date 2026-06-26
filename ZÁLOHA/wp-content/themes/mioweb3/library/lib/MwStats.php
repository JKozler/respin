<?php
define('STATISTICS_TABLE_NAME', 'mw_statistics');

function mwStats(): mioweb
{
   return MwStats::instance();
}

class MwStats
{

	protected static $_instance = null;

	public function getAll($type)
	{
		$this->createTable();

		global $wpdb;
		$resutl = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{STATISTICS_TABLE_NAME} ORDER BY stats_day");
	}

	public function get($type, $from, $to)
	{
		$this->createTable();

		global $wpdb;
		$resutl = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{STATISTICS_TABLE_NAME} WHERE stats_day >= {$from} AND stats_day <= {$to} ORDER BY stats_day");
	}

	public function save($type, $day, $data)
	{
		$this->createTable();

		global $wpdb;
	}

	public function recalc($type, $from, $to)
	{
	}

	public function recalcAll($type)
	{
		global $wpdb;
	}

	public function createTable()
	{
		global $wpdb;

		if (!MWDB()->tableExist(STATISTICS_TABLE_NAME)) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . STATISTICS_TABLE_NAME . ' ('
				. 'stat_id int(11) NOT NULL AUTO_INCREMENT,'
				  . 'stat_day date NOT NULL,'
				  . 'stat_type varchar(50) NOT NULL,'
				  . 'stat_data longtext NOT NULL,'
				  . 'stat_need_recalc tinyint(1) NOT NULL,'
				  . "PRIMARY KEY (stat_id), INDEX (stat_day), INDEX (stat_type), INDEX (stat_need_recalc)) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);

			return empty($wpdb->last_error);
		}

		return true;
	}

	/** @return MwStats Returns singleton instance of mioweb. */
	public static function instance(): mioweb
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}
}
