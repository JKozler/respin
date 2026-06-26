<?php

class mwPage extends mwPost
{

	private $_content;

	public static function getAll($args = [], $paged = true): array
	{
		$default_args = [
			'post_type' => 'page',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		];

		$query_args = array_merge($default_args, $args);

		return self::getPages($query_args);
	}

	/*
	public static function getAll($args = [], $paged=true): array
	{

		$default_args = [
			'post_type' => 'page',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		];

		$query_args = array_merge($default_args, $args);

		return self::getQuery($query_args, $paged);
	} */

	public static function getPages($args = [])
	{
		global $wpdb;

		$post_status = $args['post_status'] ?? ['publish'];
		$post_type = $args['post_type'] ?? 'page';
		$parent = $args['parent'] ?? -1;
		$hierarchical = $args['hierarchical'] ?? false;
		$orderby = $args['orderby'] ?? 'post_title';

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

		$query = "SELECT ID, post_author, post_date, post_date_gmt, post_excerpt, post_title, post_status, comment_status, ping_status, post_password, post_name, post_modified, post_modified_gmt, post_parent, menu_order, post_type, comment_count FROM $wpdb->posts WHERE ($where_post_type) $where";

		if (isset($args['exclude']) && (bool) $args['exclude']) {
			$exclude = is_array($args['exclude']) ? implode(',', $args['exclude']) : $args['exclude'];
			$query .= ' AND ID NOT IN (' . $exclude . ')';
		}

		$query .= ' ORDER BY ' . $orderby . ' ASC';

		$pages = $wpdb->get_results($query);

		// Sanitize before caching so it'll only get done once.
		$num_pages = count($pages);
		for ($i = 0; $i < $num_pages; $i++) {
			$pages[$i ] = sanitize_post($pages[$i ], 'raw');
		}

		if ($hierarchical) {
			$pages = get_page_children(0, $pages);
		}

		// Convert to mwPage instances.
		$pages = array_map(function ($page) {
			$wpPost = get_post($page);

			return static::createNew($wpPost);
		}, $pages);

		return $pages;
	}

	public function getContent(): string
	{
		if (!$this->_content) {
			$this->_content = MWDB()->getLayer($this->getID(), $this->getPostType());
		}

		return $this->_content;
	}

	public function setContent($content)
	{
		//$this->_postContent = $content;
		$this->_content = $content;
		MWDB()->updatePost(['ID' => $this->getID(), 'post_content' => $content]);
		MWDB()->setLayer($this->getID(), $this->getPostType(), $content);
	}

	public function getTemplate(): ?string
	{
		$template = get_post_meta($this->getId(), 've_page_template', true);

		return $template['directory'] ?? null;
	}

	public function isFrontPage(): bool
	{
		return get_option('show_on_front') === 'page' && (int) get_option('page_on_front') === $this->getId();
	}

	public function isBlogFrontPage(): bool
	{
		return get_option('show_on_front') === 'page' && (int) get_option('page_for_posts') === $this->getId();
	}

	public static function registerPageObject()
	{
		$mwArgs = [
			'service_class' => 'mwSettingObjectService_Page',
			'class' => 'mwPage',
			'object_type' => 'page',
			'allow_add' => true,
			'supports' => ['thumbnail','visibility','export','search','duplicate','visualeditor','comments','password_protected'],
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Stránka', 'cms'),
				'add_item' => __('Přidat stránku', 'cms'),
				'edit_item' => __('Upravit stránku', 'cms'),
				'new_item' => __('Nová stránka', 'cms'),
				'delete' => __('Smazat stránku', 'cms'),
				'empty' => __('Nebyla nalezena žádná stránka', 'cms'),
				'notfound' => __('Stránka s tímto ID nebyla nalezena', 'cms'),
			],

		];

		mwSetting()->registerObject('page', $mwArgs);
	}

}

class mwSettingObjectService_Page extends mwSettingObjectService
{

}
