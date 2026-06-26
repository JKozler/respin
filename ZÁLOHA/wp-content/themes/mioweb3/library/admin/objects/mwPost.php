<?php

use Mioweb\VisualEditor\Lib\Image;

class mwPost
{

	protected $_post;

	private $_id;

	private $_visibility;

	private $_title;

	private $_excerpt;

	private $_parent;

	private $_slug;

	private $_postContent;

	private $_redirectSetting = null;

	public function __construct($post)
	{
		$this->_post = $post;
		$this->_id = $post->ID;
		$this->_visibility = $post->post_status == 'publish' ? 1 : 0;
		$this->_title = $post->post_title ?? __('(bez názvu)', 'cms');
		$this->_excerpt = $post->post_excerpt;
		$this->_parent = $post->post_parent;
		$this->_slug = $post->post_name;
		$this->_postContent = $post->post_content;
	}

	public function getPost(): WP_Post
	{
		return $this->_post;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getName(): string
	{
		return $this->_title;
	}

	public function getParentId(): int
	{
		return $this->_parent;
	}

	public function getPostType(): string
	{
		return $this->_post->post_type;
	}

	public function getObjectId(): string
	{
		return $this->_post->post_type;
	}

	public function getSlug(): string
	{
		return $this->_slug;
	}

	public function getAuthorId(): int
	{
		return $this->_post->post_author;
	}

	public function getAuthor(): ?mwUser
	{
		return mwUser::getOneById($this->_post->post_author);
	}

	public function getStatus(): string
	{
		return $this->_post->post_status;
	}

	public function getPostFormat(): string
	{
		if (!post_type_supports($this->_post->post_type, 'post-formats')) {
			return '';
		}

		$_format = get_the_terms($this->getId(), 'post_format');

		if (empty($_format)) {
			return '';
		}

		$format = reset($_format);

		return str_replace('post-format-', '', $format->slug);
	}

	public function getPassword(): string
	{
		return $this->_post->post_password;
	}

	public function isTrashed(): int
	{
		return $this->getStatus() === 'trash' ? 1 : 0;
	}

	public function isVisible(): int
	{
		return $this->_visibility;
	}
	public function getVisibilityStatus()
	{
		if ($this->isPasswordProtected()) {
			return 'password_protected';
		}

		if ($this->isFuture()) {
			return 'publish';
		}

		return $this->getStatus();
	}

	public function isDraft(): bool
	{
		return $this->getStatus() == 'draft';
	}

	public function isPrivate(): bool
	{
		return $this->getStatus() == 'private';
	}

	public function isFuture(): bool
	{
		return $this->getStatus() == 'future';
	}

	public function isSticky(): bool
	{
		return is_sticky($this->_id);
	}

	public function isCommentsOpen(): bool
	{
		return ($this->_post->comment_status === 'open');
	}

	public function isPasswordProtected(): bool
	{
		return $this->getPassword() ? true : false;
	}

	public function getExcerpt(int $wordCount = 0, bool $contentIfEmpty = false): string
	{
		$excerpt = $this->_excerpt;
		if (!$excerpt && $contentIfEmpty) {
			$excerpt = stripslashes($this->_postContent);
		}

		return $wordCount ? wp_trim_words($excerpt, $wordCount) : $excerpt;
	}

	public function getOrder(): int
	{
		return $this->_post->menu_order;
	}

	public function getCommentStatus(): string
	{
		return $this->_post->comment_status;
	}

	public function getCommentCount(): int
	{
		return $this->_post->comment_count;
	}

	public function getCommentsText(): string
	{
		$text = '';
		$count = $this->getCommentCount();
		if ($count > 4 || $count == 0) {
			$text = $count . ' ' . __('Komentářů', 'cms_ve');
		} elseif ($count > 1) {
			$text = $count . ' ' . __('Komentáře', 'cms_ve');
		} else {
			$text = $count . ' ' . __('Komentář', 'cms_ve');
		}

		return $text;
	}

	public function getContent(): string
	{
		return $this->_postContent;
	}

	public function setContent($content)
	{
		$this->_postContent = $content;
	}

	public function getDateCreated($type = 'datetime'): string
	{
		return mwPrintDate(strtotime($this->_post->post_date), $type);
	}

	public function getDateCreatedTimestamp(): int
	{
		return strtotime($this->_post->post_date);
	}

	public function getDateUpdated($type = 'datetime'): string
	{
		return mwPrintDate(strtotime($this->_post->post_modified), $type);
	}

	public function getTermIds(string $taxonomy): array
	{
		$termIds = [];
		$postTerms = wp_get_post_terms($this->getId(), $taxonomy);
		foreach ($postTerms as $pt) {
			$termIds[] = $pt->term_id;
		}

		return $termIds;
	}

	public function getUrl(): string
	{
		return get_permalink($this->_post);
	}

	// nice url for future and draft.
	public function getSampleUrl(): string
	{
		if ($this->getStatus() === 'future' || $this->getStatus() === 'draft') {
			require_once(ABSPATH . 'wp-admin/includes/post.php');
			$permalink_a = get_sample_permalink($this->_id);
			$permalink = preg_replace('/\%postname\%/', $permalink_a[1], $permalink_a[0]);
		} else {
			$permalink = get_permalink($this->_id);
		}

		return $permalink;
	}

	public function getRedirectSetting(): ?array
	{
		if ($this->_redirectSetting === null) {
			$this->_redirectSetting = get_post_meta($this->getId(), 'page_redirect', true) ?: null;
		}

		return $this->_redirectSetting;
	}

	public function isRedirected(): bool
	{
		return $this->getRedirectUrl() ? true : false;
	}

	public function getRedirectUrl(): string
	{
		$redirect = $this->getRedirectSetting();

		if (isset($redirect['redirect_url'])) {
			$link = $redirect['redirect_url'];
			if (isset($link['use_url'])) {
				return $link['link'];
			}

			if (isset($link['page']) && $link['page']) {
				return get_permalink($link['page']);
			}
		}

		return '';
	}

	public static function getQuery($query_args, $paged = false): array
	{
		$q = new WP_Query($query_args);

		if ($paged) {
			return [
				'items' => array_map(function (WP_Post $post) {
					return static::createNew($post);
				}, $q->posts),
				'pages' => $q->max_num_pages,
				'count' => $q->found_posts,
			];
		}

		return array_map(function (WP_Post $post) {
			return static::createNew($post);
		}, $q->posts);
	}

	public function createCopy($tosave)
	{
		$newItem = [
			'post_type' => $this->getPostType(),
			'post_status' => 'publish',
			'comment_status' => 'open',
			'post_title' => $tosave['post_title'] ?? $this->getName() . __('_kopie', 'cms'),
			'post_parent' => $tosave['post_parent'] ?? $this->getParentId(),
			'menu_order' => $tosave['menu_order'] ?? $this->getOrder(),
			'post_excerpt' => $tosave['post_excerpt'] ?? $this->getExcerpt(),
			'post_content' => $this->getContent(),
		];

		if (isset($tosave['post_name'])) {
			$newItem['post_name'] = $tosave['post_name'];
		}

		$itemId = MWDB()->insertPost($newItem);
		if ($itemId) {
			// content
			$layer = $this->getContent();
			$item = self::getOneById($itemId);
			$item->setContent($layer);

			// post metas
			foreach (MWDB()->getPostMeta($this->getId()) as $key => $val) {
				if ($key != '_edit_last' && $key != '_edit_lock' && $key != 'mioweb_campaign' && $key != 'mwf_funnel_id') {
					MWDB()->setPostMeta($itemId, $key, @unserialize($val[0]));
				}
			}

			return $item;
		}

		return null;
	}

	public function getThumbnailId(): int
	{
		return get_post_thumbnail_id($this->getId());
	}

	public function getThumbnail(): Image
	{
		return str_starts_with($this->getThumbnailId(), 'http')
			? Image::createByUrl($this->getThumbnailId())
			: Image::createById($this->getThumbnailId());
	}

	public function hasThumbnail(): bool
	{
		return has_post_thumbnail($this->_id);
	}

	public function getEditButton()
	{
		global $vePage;

		return $vePage->display->itemEditButton($this->getPostType(), $this->getId());
	}

	public function getSettingActions()
	{
		return $this->isTrashed() ? ['restore', 'delete'] : ['edit', 'show_page', 'delete'];
	}

	public function toPageSelectorItem(): mwPageSelectorItem
	{
		$actions = [];
		if (MwSetting()->getObject($this->getPostType())->isSupported('duplicate')) {
			$actions[] = 'copy';
		}
		$actions[] = 'delete';

		return new mwPageSelectorItem([
			'title' => $this->getName(),
			'url' => $this->getUrl(),
			'parent' => $this->getParentId(),
			'id' => $this->getId(),
			'status' => $this->getStatus(),
			'type' => $this->getPostType(),
			'actions' => $actions,
		]);
	}

	/**
	 * Get instance by ID.
	 */
	public static function getOneById(int $id, bool $forceRecache = false): ?self
	{
		$post = get_post($id);
		if ($post) {
			try {
				return static::createNew($post, $forceRecache);
			} catch (MwsException $e) {
				mwlog(sprintf(__('Nepodařilo se vytvořit instanci postu s ID: %d. Chyba: %s', 'cms'), $id, $e->getMessage()), MWLL_ERROR);
			}
		}

		return null;
	}

	public static function createNew(WP_Post $post, bool $forceUpdateCache = false): ?self
	{
		if ($forceUpdateCache || !($obj = MwObjectCache::get(static::class, $post->ID))) {
			$obj = new static($post);
			MwObjectCache::add($obj, $obj->getId());
		}

		return $obj;
	}

	public static function getAllIds($args): array
	{
		$default_args = [
			'post_type' => 'post',
			'post_status' => 'publish',
			'fields' => 'ids',
			'posts_per_page' => -1,
		];

		$query_args = array_merge($default_args, $args);

		$q = new WP_Query($query_args);

		return $q->posts;
	}

}
