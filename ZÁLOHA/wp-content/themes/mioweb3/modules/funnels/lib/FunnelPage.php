<?php
namespace Mioweb\Funnel;
use mwPage;

class FunnelPage extends mwPage
{

	public static function getAllForFunnel(?int $funnelId, array $args = [])
	{
		global $wpdb;

		$post_status = $args['post_status'] ?? ['publish'];
		$post_type = 'page';
		$parent = $args['parent'] ?? -1;
		$hierarchical = $args['hierarchical'] ?? false;
		$page = $args['paged'] ?? 1;
		$perPage = $args['number'] ?? -1;

		if ($parent > 0) {
			$hierarchical = false;
		}

		// Make sure we have a valid post status.
		if (!is_array($post_status)) {
			$post_status = explode(',', $post_status);
		}
		if (array_diff($post_status, get_post_stati())) {
			return false;
		}

		if (count($post_status) === 1) {
			$where_post_type = $wpdb->prepare('post_type = %s AND post_status = %s', $post_type, reset($post_status));
		} else {
			$post_status = implode("', '", str_replace(' ', '', $post_status));
			$where_post_type = $wpdb->prepare("post_type = %s AND post_status IN ('$post_status')", $post_type);
		}

		$where = '';
		if (is_array($parent)) {
			$post_parent__in = implode(',', array_map('absint', (array) $parent));
			if (!empty($post_parent__in)) {
				$where .= " AND post_parent IN ($post_parent__in)";
			}
		} elseif ($parent >= 0) {
			$where .= $wpdb->prepare(' AND post_parent = %d ', $parent);
		}

		if (isset($args['where'])) {
			$where .= ' ' . $args['where'];
		}

		// member page columns
		$columns = 'fp_id';
		// post columns
		$columns .= ', ID, post_author, post_date, post_date_gmt, post_excerpt, post_title, post_status, comment_status, ping_status, post_password, post_name, post_modified, post_modified_gmt, post_parent, menu_order, post_type, comment_count';

		if ($funnelId) {
			$where .= ' AND fp_funnel_id = ' . $funnelId;
		}
		$query = "SELECT $columns FROM " . $wpdb->prefix . "mw_funnel_pages, $wpdb->posts WHERE fp_page_id = ID AND ($where_post_type) $where";


		if (isset($args['exclude']) && (bool) $args['exclude']) {
			$exclude = is_array($args['exclude']) ? implode(',', $args['exclude']) : $args['exclude'];
			$query .= ' AND ID NOT IN (' . $exclude . ')';
		}

		$query .= ' ORDER BY ' . ($args['orderby'] ?? 'menu_order, post_date ASC');

		// Limit.
		if ($perPage > 0) {
			$query .= $wpdb->prepare(' LIMIT %d, %d', $perPage * ($page - 1), $perPage);
		}

		$pages = $wpdb->get_results($query);

		// Sanitize before caching so it'll only get done once.
		$num_pages = count($pages);
		for ($i = 0; $i < $num_pages; $i++) {
			$pages[$i ] = sanitize_post($pages[$i ], 'raw');
		}

		if ($hierarchical) {
			$pages = get_page_children(0, $pages);
		}

		// Convert to MemberPage instances.
		//$pages = array_map( [FunnelPage::class, 'createOne'], $pages );
		$pages = array_map(function ($page) {
			$wpPost = get_post($page);

			return static::createNew($wpPost);
		}, $pages);

		return $pages;
	}

	public function getPageSelectorPageIcons(array $icons): array
	{
		$icons[] = [
			'icon' => 'filter',
			'text' => __('Stránka cesty zákazníka', 'cms_ve'),
		];

		return $icons;
	}

}
