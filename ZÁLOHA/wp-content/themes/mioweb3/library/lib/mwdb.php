<?php

use Mioweb\Lib\Exceptions\MWDBException;

/**
 * Class for setting
 */
function MWDB()
{
	return mwdb::instance();
}

class mwdb
{

	protected static $_instance = null;

	function __construct()
	{
	}

	public function sql(string $sql)
	{
		global $wpdb;

		return $wpdb->query($sql);
	}

	// table

	/** @throws MWDBException */
	public function createTable(string $tableName, string $sqlData, bool $addPrefix = true, bool $throw = true): bool
	{
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		if ($addPrefix) {
			$tableName = $wpdb->prefix . $tableName;
		}

		$sql = 'CREATE TABLE IF NOT EXISTS ' . $tableName . ' (' . $sqlData . ") ENGINE=InnoDB $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$lastError = $wpdb->last_error;
		$hasError = !empty($lastError);

		if ($hasError && $throw) {
			throw new MWDBException($lastError);
		}

		return !$hasError;
	}

	public function tableExist(string $tableName): bool
	{
		global $wpdb;

		return ($wpdb->get_var('SHOW TABLES LIKE "' . $wpdb->prefix . $tableName . '"') === $wpdb->prefix . $tableName);
	}

	public function getRows(string $tableName, $where = '', $order = '', $select = '*', $groupby = '')
	{
		global $wpdb;
		$orderby = $order ? ' ORDER BY ' . $order : '';
		$where = $where ? ' WHERE ' . $where : '';
		$groupby = $groupby ? ' GROUP BY ' . $groupby : '';

		return $wpdb->get_results('SELECT ' . $select . ' FROM ' . $wpdb->prefix . $tableName . $where . $groupby . $orderby);
	}

	public function getResults(string $sql)
	{
		global $wpdb;

		return $wpdb->get_results($sql);
	}

	public function getTableRow(string $tableName, $where = '')
	{
		global $wpdb;
		$where = $where ? ' WHERE ' . $where : '';

		return $this->getRow('SELECT * FROM ' . $wpdb->prefix . $tableName . $where);
	}

	public function getRow(string $sql)
	{
		global $wpdb;

		return $wpdb->get_row($sql);
	}

	public function insert(string $tableName, array $data, bool $addPrefix = true)
	{
		global $wpdb;

		if ($addPrefix) {
			$tableName = $wpdb->prefix . $tableName;
		}

		$wpdb->insert($tableName, $data);

		return $wpdb->insert_id;
	}

	public function insertRows(string $tableName, array $data, string $fields = '')
	{
		global $wpdb;

		$values = $this->formatValuesForInsert($data);

		$fields = $fields ? ' (' . $fields . ')' : '';

		$query = 'INSERT INTO ' . $wpdb->prefix . $tableName . $fields . ' VALUES ' . implode(',', $values);

		return $wpdb->query($query);
	}

	public function replace(string $tableName, array $data)
	{
		global $wpdb;

		return $wpdb->replace(
			$wpdb->prefix . $tableName,
			$data
		);
	}

	public function delete(string $tableName, array $where)
	{
		global $wpdb;

		return $wpdb->delete(
			$wpdb->prefix . $tableName,
			$where,
		);
	}

	public function update(string $tableName, array $data, array $where)
	{
		global $wpdb;

		return $wpdb->update(
			$wpdb->prefix . $tableName,
			$data,
			$where,
		);
	}

	// layer

	public function getLayer($itemId, $type = 'page')
	{
		global $wpdb;
		$result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='" . $type . "' AND vpl_post_id=" . $itemId);

		return $result->vpl_layer ?? '';
	}

	public static function setLayer($itemId, $type = 'page', $layer = '', $rewrite = true)
	{
		global $wpdb;

		if ($rewrite) {
			$result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='" . $type . "' AND vpl_post_id=" . $itemId);
			if ($wpdb->num_rows) {
				return $wpdb->update($wpdb->prefix . 've_posts_layer', ['vpl_layer' => $layer], ['vpl_post_id' => $itemId, 'vpl_type' => $type]);
			}
		}

		return self::addLayer($itemId, $type, $layer);
	}

	public static function addLayer($itemId, $type = 'page', $layer = '')
	{
		global $wpdb;

		return $wpdb->insert(
			$wpdb->prefix . 've_posts_layer',
			[
				'vpl_post_id' => $itemId,
				'vpl_type' => $type,
				'vpl_layer' => $layer,
			]
		);
	}

	public function deleteLayer($itemId, $type = 'page')
	{
		global $wpdb;

		return $wpdb->query('DELETE FROM ' . $wpdb->prefix . 've_posts_layer WHERE vpl_post_id=' . $itemId . " AND vpl_type='" . $type . "'");
	}

	// post

	// getPost

	public function updatePost($args)
	{
		return wp_update_post($args);
	}

	public function insertPost($args)
	{
		return wp_insert_post($args);
	}

	public function deletePost(int $postId, bool $force_delete = false)
	{
		return wp_delete_post($postId, $force_delete);
	}

	// post meta

	public function getPostMeta(int $postId, string $key = '', bool $single = false)
	{
		return get_post_meta($postId, $key, $single);
	}

	public function setPostMeta($postId, $key, $val) // for add and update post meta
	{
		return update_post_meta($postId, $key, $val);
	}

	public function deletePostMeta($postId, $key)
	{
		return delete_post_meta($postId, $key);
	}

	// options

	public function getOption(string $option, $default = false)
	{
		return get_option($option, $default);
	}

	public function setOption($option, $value)
	{
		return update_option($option, $value);
	}

	public function deleteOption($option)
	{
		return delete_option($option);
	}

	// terms

	// getTerm

	public function updateTerm($term_id, $taxonomy, $args = [])
	{
		return wp_update_term($term_id, $taxonomy, $args);
	}

	public function insertTerm($term_id, $taxonomy, $args = [])
	{
		return wp_insert_term($term_id, $taxonomy, $args);
	}

	public function deleteTerm($term_id, $taxonomy, $args = [])
	{
		return wp_delete_term($term_id, $taxonomy, $args);
	}

	// terms meta

	public function updateTermMeta($term_id, $meta_key, $meta_value, $prev_value = '')
	{
		return update_term_meta($term_id, $meta_key, $meta_value, $prev_value);
	}

	public function insertTermMeta($term_id, $meta_key, $meta_value, $unique = false)
	{
		return add_term_meta($term_id, $meta_key, $meta_value, $unique);
	}

	public function deleteTermMeta($term_id, $meta_key, $meta_value = '')
	{
		return delete_term_meta($term_id, $meta_key, $meta_value);
	}

	public function getTermMeta($term_id, $key = '', $single = false)
	{
		return get_term_meta($term_id, $key, $single);
	}

	/** @return mwdb Returns singleton instance of MioShop. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/** @param mixed[] $data */
	private function formatValuesForInsert(array $data): array
	{
		$values = [];

		foreach ($data as $value) {
			if (is_array($value)) {
				$valueFormatted = '';
				$first = true;

				foreach ($value as $item) {
					if (!$first) {
						$valueFormatted .= ',';
					}

					$first = false;
					$valueFormatted .= is_string($item) ? '"' . $item . '"' : $item;
				}
			} else {
				$valueFormatted = $value;
			}
			$values[] = '(' . $valueFormatted . ')';
		}

		return $values;
	}

}
