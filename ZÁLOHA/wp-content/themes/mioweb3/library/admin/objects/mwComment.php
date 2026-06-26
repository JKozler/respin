<?php

class mwComment
{

	private $_wpComment;

	public function __construct($comment)
	{
		$this->_wpComment = $comment;
	}

	public function getId(): int
	{
		return $this->_wpComment->comment_ID;
	}

	public function getContent(): string
	{
		return $this->_wpComment->comment_content;
	}
	public function getName(): string
	{
		return $this->_wpComment->comment_content;
	}

	public function getParentId(): string
	{
		return $this->_wpComment->comment_parent;
	}

	public function getAuthorId()
	{
		return $this->_wpComment->user_id;
	}
	public function getAuthorName($real = false): string
	{
		return $this->_wpComment->comment_author ?: ($real ? '' : __('Anonym', 'cms'));
	}
	public function getAuthorEmail(): string
	{
		return $this->_wpComment->comment_author_email ?: '';
	}
	public function getAuthorWeb(): string
	{
		return $this->_wpComment->comment_author_url ?: '';
	}
	public function getAuthorIP(): string
	{
		return $this->_wpComment->comment_author_IP ?: '';
	}
	public function getAuthorAvatar($size = 60)
	{
		return get_avatar($this->_wpComment, $size);
	}

	public function getPostId(): string
	{
		return $this->_wpComment->comment_post_ID;
	}

	public function getKarma(): int
	{
		return $this->_wpComment->comment_karma;
	}

	public function getType(): int
	{
		return $this->_wpComment->comment_type;
	}

	public function isApproved(): int
	{
		return $this->_wpComment->comment_approved !== '0' ? 1 : 0;
	}

	public function isSpam(): int
	{
		return $this->_wpComment->comment_approved == 'spam' ? 1 : 0;
	}

	public function isTrashed(): int
	{
		return $this->_wpComment->comment_approved == 'trash' ? 1 : 0;
	}

	public function getStatus(): string
	{
		return $this->_wpComment->comment_approved;
	}

	public function getDateCreated(): string
	{
		return $this->_wpComment->comment_date;
	}

	public function getCommentDate(): string
	{
		return mwPrintDate(strtotime($this->getDateCreated()));
	}

	public function getWpComment(): WP_Comment
	{
		return $this->_wpComment;
	}

	public function getMeta($meta)
	{
		return get_comment_meta($this->getId(), $meta, true);
	}

	public function getUrl(): string
	{
		return self::getCommentLink($this->getId());
	}

	public static function getCommentLink($id)
	{
		return get_comment_link($id);
	}

	public static function setStatus($id, $status = '1')
	{
		return wp_update_comment([
			'comment_ID' => $id,
			'comment_approved' => $status,
		]);
	}

	public static function deleteAll($status = 'trash')
	{
		global $wpdb;
		if ($wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = '" . $status . "'") != false) {
			$wpdb->query("OPTIMIZE TABLE $wpdb->comments");
		}
	}

	/**
	 * Get instance by ID.
	 */
	public static function getOneById(int $id, bool $forceRecache = false): ?self
	{
		$com = get_comment($id);
		if ($com) {
			try {
				return static::createNew($com, $forceRecache);
			} catch (MwsException $e) {
				mwlog(sprintf(__('Nepodařilo se vytvořit instanci komentáře s ID: %s', 'cms'), $id, $e->getMessage()), MWLL_ERROR);
			}
		}

		return null;
	}

	public static function createNew(WP_Comment $comment, bool $forceUpdateCache = false): ?self
	{
		if ($forceUpdateCache || !($obj = MwObjectCache::get(self::class, $comment->comment_ID))) {
			$obj = new self($comment);
			MwObjectCache::add($obj, $obj->getId());
		}

		return $obj;
	}

	public static function getAll($args = [], $paged = false): array
	{
		$default_args = [
			'no_found_rows' => false,
		];

		$query_args = array_merge($default_args, $args);

		$q = new WP_Comment_Query($query_args);

		if ($paged) {
			return [
				'items' => array_map(function ($comment) {
						return static::createNew($comment);
				}, $q->comments),
				'pages' => $q->max_num_pages,
				'count' => $q->found_comments,
			];
		}

		return array_map(function ($comment) {
				return static::createNew($comment);
		}, $q->comments);
	}

	public static function getNotApprovedCount()
	{
		$commentCounts = wp_count_comments();

		return $commentCounts->moderated;
	}

	public static function getCount($status)
	{
		$commentCounts = wp_count_comments();

		return $commentCounts->$status;
	}

	public function getSettingActions()
	{
		return $this->isTrashed() ? ['restore', 'show_page', 'delete'] : ['edit', 'show_page', 'delete'];
	}

	public function getActions($statusFilter = 'all')
	{
		$actions = [];
		$class = 'mw_comment_change_status';

		if ($this->isSpam()) {
			$actions[] = [
				'text' => __('Není spam', 'cms'),
				'class' => $class,
				'attrs' => 'data-status="1" data-remove="1" data-comment="' . $this->getId() . '"',
			];
		} elseif (!$this->isTrashed()) {
			$removeAttr = $statusFilter != 'all' ? ' data-remove="1"' : '';

			$actions[] = [
				'text' => __('Zamítnout', 'cms'),
				'class' => $class . ' mw_comment_change_status_reject',
				'attrs' => 'data-status="0" data-comment="' . $this->getId() . '"' . $removeAttr,
			];

			$actions[] = [
				'text' => __('Schválit', 'cms'),
				'class' => $class . ' mw_comment_change_status_approve',
				'attrs' => 'data-status="1" data-comment="' . $this->getId() . '"' . $removeAttr,
			];

			$actions[] = [
				'text' => __('Odpovědět', 'cms'),
				'class' => 'mw_comment_list_reply',
				'attrs' => 'data-comment="' . $this->getId() . '"',
			];

			$actions[] = [
				'text' => __('Spam', 'cms'),
				'class' => $class . ' mw_comment_change_status_spam',
				'attrs' => 'data-status="spam" data-remove="1" data-comment="' . $this->getId() . '"',
			];
		}

		return $actions;
	}

	public static function registerComments()
	{
		$mwArgs = [
			'service_class' => 'mwSettingObjectService_Comments',
			'class' => 'mwComment',
			'object_type' => 'comment',
			'supports' => ['search', 'trash'],
			'filter' => [
				[
					'id' => 'status',
					'content' => '',
					'title' => __('Stav', 'cms'),
					'items' => [
						'' => __('Vše', 'cms'),
						'0' => __('Ke schválení', 'cms'),
						'1' => __('Schválené', 'cms'),
						'spam' => __('Spam', 'cms'),
					],
				],
				[
					'id' => 'source',
					'object_id' => 'post',
					'title' => __('Pro', 'cms'),
					'type' => 'hidden',
				],
			],
			'bulk_actions' => [
				[
					'action' => 'reject',
					'title' => __('Odmítnout', 'cms'),
				],
				[
					'action' => 'approve',
					'title' => __('Schválit', 'cms'),
				],
				[
					'action' => 'spam',
					'title' => __('Označit jako spam', 'cms'),
				],
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Komentáře', 'cms'),
				'add_item' => __('Přidat komentář', 'cms'),
				'edit_item' => __('Upravit komentář', 'cms'),
				'new_item' => __('Nový komentář', 'cms'),
				'delete' => __('Smazat komentář', 'cms'),
				'empty' => __('Nebyl nalezen žádný komentář', 'cms'),
				'notfound' => __('Komentář s tímto ID nebyl nalezen', 'cms'),
				'trash_title' => __('Koš komentářů', 'cms'),
			],

		];

		mwSetting()->registerObject('comments', $mwArgs);
	}


}

class mwSettingObjectService_Comments extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Autor', 'cms'),
				],
				[
					'content' => __('Komentář', 'cms'),
				],
				[
					'content' => __('Stránka', 'cms'),
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();

		$status = $trash ? 'trash' : $filter['status'] ?? 'all';

		$query_args = [
			'number' => $perPage,
			'offset' => ($page - 1) * $perPage,
			'paged' => $page,
			'search' => $filter['s'] ?? '',
			'status' => $status,
		];

		if (isset($filter['source'])) {
			$query_args['post_id'] = $filter['source'];
		}

		$comments = mwComment::getAll($query_args, true);

		$args['pagination'] = [
			'pages' => $comments['pages'],
			'count' => $comments['count'],
		];

		foreach ($comments['items'] as $item) {
			$args['rows'][] = $this->getTableListRow($item);
		}

		$args['html_after'] = '<div id="mw_setting_comment_reply_container">' . $this->getReplyForm() . '</div>';

		return $args;
	}

	function getTableListRow($item)
	{
		$post = mwPost::getOneById($item->getPostId());
		$filter = $this->object()->getSavedListFilter();

		$author = '<div class="mw_comment_list_author">';
		$author .= $item->getAuthorAvatar(40);
		$author .= '<div class="mw_comment_list_author_info">';
		$author .= $item->getAuthorName();
		$author .= '<span>' . $item->getCommentDate() . '</span>';
		$author .= '</div>';
		$author .= '</div>';

		$page = '<div class="mw_comment_list_page">';
		$page .= '<a class="mw_comment_count_box" href="' . $this->object()->getUrl('source=' . $post->getId()) . '" title="' . __('Zobrazit komentáře stránky', 'cms') . '">' . $post->getCommentCount() . '</a>';
		$page .= '<a class="mw_link" href="' . $this->getItemUrl($item->getId()) . '" title="' . __('Zobrazit stránku', 'cms') . '">' . $post->getName() . '</a>';
		$page .= '</div>';

		$actions = $item->getActions(isset($filter['status']) && $filter['status'] ? $filter['status'] : 'all');

		$content = '';
		if ($item->getParentId()) {
			$parentComment = $this->getItem($item->getParentId());
			$content .= '<div class="mw_comment_list_is_reply">';
			$content .= __('Odpověď na', 'cms');
			if ($parentComment) {
				$content .= ' <a href="' . $parentComment->getUrl() . '">' . $parentComment->getAuthorName() . '</a>';
			} else {
				$content .= ' <span class="mw_red_text">' . __('smazaný komentář', 'cms') . '</span>';
			}
			$content .= '</div>';
		}
		$content .= '<p>' . $item->getContent() . '</p>';
		$content .= '<div class="mw_comment_list_actions">';

		foreach ($actions as $action) {
			$content .= mwAdminComponents::link($action, $action['class']);
		}

		$content .= '</div>';

		$settingActions = $item->isSpam() || $item->isTrashed() ? ['delete'] : ['edit', 'show_page', 'delete'];

		return [
			'bulk_id' => $item->getId(),
			'class' => $item->isApproved() ? '' : 'mw_comment_not_approved',
			'cols' => [
				[
					'content' => $author,
				],
				[
					'content' => $content,
				],
				[
					'content' => $page,
				],
				[
					'content' => mwSetting::printSettingActions($item->getSettingActions(), $item->getId(), $this->object()),
					'align' => 'right',
				],
			],
		];
	}

	public function getReplyForm()
	{
		$content = mwAdminComponents::textarea([
			'name' => 'comment_content',
			'rows' => 8,
		], '');

		$content .= wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce', true, false);

		return $content;
	}

	function printFormSidebar($item, $add = false, $inPopup = false): string
	{
		$content = '<div class="mw_setting_object_detail_sidebar">';

		$content .= '<div class="mw_setting_sidebar_box">';

		$status = $item->getStatus();

		$content .= mwAdminComponents::statusSelect([
			'title' => __('Status', 'cms'),
			'show_list' => true,
			'input' => 'comment[comment_approved]',
			'list' => [
				'1' => [
					'text' => __('Schválený', 'cms'),
					'status' => 'ok',
					'icon' => 'check',
				],
				'0' => [
					'text' => __('Ke schválení', 'cms'),
					'status' => 'x',
					'icon' => 'clock',
				],
				'spam' => [
					'text' => __('Spam', 'cms'),
					'status' => 'fail',
					'icon' => 'x',
				],
			],
		], $status, 'mw_setting_sidebar_visibility');

		if (!$add) {
			$content .= $this->getInfoList($item);

			if (!$inPopup) {
				$content .= $this->getDetailActionList($item);
			}
		}
		$content .= '</div>';

		// GDPR
		$content .= '<div class="mw_setting_sidebar_box">';
		$content .= mwAdminComponents::title([
			'text' => __('Souhlas se zpracováním osobních údajů', 'cms'),
		]);
		$accept = $item->getMeta('_mw_comment_gdpr');
		$content .= !empty($accept) && $accept['time'] ? __('Souhlas udělen', 'cms_member') . ': ' . mwPrintDate($accept['time']) : __('Souhlas neudělen', 'cms_member');
		$accept_text = $accept['text'] ?? __(' Souhlas s účelem zpracování je evidován u původního zdroje.', 'cms_member');
		$content .= mwAdminComponents::tooltip([
			'icon' => 'i',
			'tooltip_align' => 'left',
			'text' => $accept_text,
		]);

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	public function fastAddReturn($item, $type, $name = ''): string
	{
		$content = '';

		if ($type == 'table_row') {
			$row = $this->getTableListRow($item);
			$content = mwAdminComponents::tabletr($row, true);
		}

		return $content;
	}

	public function getItemUrl($id): string
	{
		return mwComment::getCommentLink($id);
	}

	public function bulkActions($list, $action)
	{
		foreach ($list as $id) {
			if ($action == 'delete') {
				$this->delete($id);
			} elseif ($action == 'reject') {
				mwComment::setStatus($id, 0);
			} elseif ($action == 'approve') {
				mwComment::setStatus($id, 1);
			} elseif ($action == 'restore') {
				mwComment::setStatus($id, 1);
			} elseif ($action == 'spam') {
				mwComment::setStatus($id, 'spam');
			}
		}
	}

	public function add($tosave, $fast = false): ?int
	{
		$commentParent = $this->getItem($tosave['comment_parent']);

		$commentId = wp_insert_comment([
			'comment_author' => mwSetting()->currentUser()->getName(),
			'comment_author_email' => mwSetting()->currentUser()->getEmail(),
			'comment_author_url' => mwSetting()->currentUser()->getWebsite(),
			'comment_content' => $tosave['comment_content'],
			'comment_parent' => $commentParent->getId(),
			'comment_post_ID' => $commentParent->getPostId(),
			'user_id' => mwSetting()->currentUser()->getId(),
		]);

		if ($commentId) {
			return $commentId;
		} else {
			mwMessages()->error(__('Komentář se nepodařilo uložit', 'cms'));
		}

		return null;
	}

	public function save($itemId, $tosave)
	{
		$comment = $tosave['comment'];
		$comment['comment_ID'] = $itemId;
		$saved = wp_update_comment($comment, true);
		if (is_wp_error($saved)) {
			mwMessages()->error($saved->get_error_message());
		}
	}

	public function restore($id): bool
	{
		return (bool) mwComment::setStatus($id, 1);
	}

	public function emptyTrash()
	{
		mwComment::deleteAll('trash');
	}

	public function delete($id, $force_delete = false)
	{
		wp_delete_comment($id);
	}

	public function getInTrashCount()
	{
		return mwComment::getCount('trash');
	}

}
