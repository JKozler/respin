<?php

class mwUser
{

	private $_id;

	private $_wpUser;

	private $_lastLogin;

	private $_metas;

	function __construct(WP_User $user)
	{
		$this->_wpUser = $user;
		$this->_id = $user->ID;
	}

	function getId(): int
	{
		return $this->_id;
	}

	function getWpUser(): WP_User
	{
		return $this->_wpUser;
	}

	function getLogin(): string
	{
		return $this->_wpUser->user_login;
	}

	function getName(): string
	{
		$name = $this->_wpUser->first_name . ' ' . $this->_wpUser->last_name;

		return trim($name) ? $name : $this->_wpUser->user_login;
	}

	function getFirstName(): string
	{
		return $this->_wpUser->first_name;
	}

	function getLastName(): string
	{
		return $this->_wpUser->last_name;
	}

	function getFullName(): string
	{
		return $this->_wpUser->first_name . ' ' . $this->_wpUser->last_name;
	}

	function getDisplayName(bool $anonymize = false): string
	{
		if ($anonymize && ($position = strpos($this->_wpUser->display_name, '@')) !== false) {
			return substr($this->_wpUser->display_name, 0, $position);
		}

		return $this->_wpUser->display_name;
	}

	function getEmail(): string
	{
		return $this->_wpUser->user_email;
	}

	function getDescription(int $words = 0): string
	{
		if ($words) {
			return wp_trim_words($this->_wpUser->description, $words);
		}

		return $this->_wpUser->description;
	}

	function getDateCreated(): string
	{
		return mwPrintDate(strtotime($this->_wpUser->user_registered), 'datetime', true);
	}

	function getRole(): ?string
	{
		return $this->_wpUser->roles[0] ?? null;
	}

	function getWebsite(): string
	{
		return $this->_wpUser->user_url;
	}

	function getContactInfo($info): string
	{
		return $this->_wpUser->$info ?? '';
	}

	function getLastLoginTime(): string
	{
		if (!$this->_lastLogin) {
			$this->_lastLogin = $this->getUserMeta('mw_last_login');
		}

		return $this->_lastLogin;
	}

	function getLastLoginDate($type = 'date', $empty = '-'): string
	{
		$time = $this->getLastLoginTime();

		if (!$time) {
			return $empty;
		}

		if ($type == 'diff') {
			return __('před', 'cms') . ' ' . human_time_diff(current_time('timestamp'), $time);
		}

		return mwPrintDate($time);
	}

	public function getUrl()
	{
		return get_author_posts_url($this->_id);
	}

	public function getAvatar(int $size = 60): string
	{
		return get_avatar($this->_id, $size);
	}

	public function getAvatarUrl(array $args = []): string
	{
		return get_avatar_url($this->_id, $args);
	}

	public function getUserMeta($metaTitle = '')
	{
		return get_user_meta($this->_id, $metaTitle, true);
	}

	public function setUserMeta($metaTitle, $value, $prev_value = '')
	{
		return update_user_meta($this->_id, $metaTitle, $value, $prev_value);
	}

	function getSettingMeta(): array
	{
		$meta = [
			'user' => [
				'user_login' => $this->_wpUser->user_login,
				'user_email' => $this->_wpUser->user_email,
				'first_name' => $this->_wpUser->first_name,
				'last_name' => $this->_wpUser->last_name,
			],
			'informations' => [
				'description' => $this->_wpUser->description,
				'user_url' => $this->_wpUser->user_url,
			],
		];

		return $meta;
	}

	public static function getCurrent()
	{
		$user = wp_get_current_user();

		return static::createNew($user);
	}

	public function getEditUrl(): string
	{
		return mwSetting()->getObject('users')->getEditUrl($this->_id);
	}

	public static function addUser(array $userData): ?stdClass
	{
		// password
		$userData['user_pass'] = isset($userData['password']) && $userData['password'] ? $userData['password'] : wp_generate_password(12, false);

		// login
		$userData['user_login'] = isset($userData['user_login']) && $userData['user_login'] ? $userData['user_login'] : $userData['user_email'];

		// display name
		$firstName = $userData['first_name'] ?? '';
		$lastName = $userData['last_name'] ?? '';
		$displayName = $firstName . ' ' . $lastName;
		if (trim($displayName)) {
			$userData['display_name'] = $displayName;
		}

		$userId = wp_insert_user($userData);

		if (is_wp_error($userId)) {
			mwMessages()->error($userId->get_error_message());
		} else {
			return (object) [
				'id' => $userId,
				'password' => $userData['user_pass'],
			];
		}

		return null;
	}

	public static function updateUser(int $userId, array $userData): ?int
	{
		$userData['ID'] = $userId;

		// password
		if (isset($userData['password']) && $userData['password']) {
			$userData['user_pass'] = $userData['password'];
		}

		// display name
		$displayName = $userData['first_name'] . ' ' . $userData['last_name'];
		if (trim($displayName)) {
			$userData['display_name'] = $displayName;
		}

		$updated = wp_update_user($userData);

		if (is_wp_error($updated)) {
			mwMessages()->error($updated->get_error_message());
		} else {
			global $wpdb;
			$wpdb->update(
				$wpdb->users,
				['user_login' => $userData['user_login']],
				['ID' => $userId]
			);

			return $userId;
		}

		return null;
	}

	/**
	 * Get user instance by user ID.
	 */
	public static function getOneById(int $userId): ?self
	{
		return static::getOneBy($userId);
	}

	// possible fields: ID | slug | email | login
	public static function getOneBy($value, string $field = 'ID'): ?self
	{
		$user = get_user_by($field, $value);
		if ($user) {
			try {
				return static::createNew($user);
			} catch (MwsException $e) {
				mwlog(MWLS_GENERAL, sprintf(__('Nepodařilo se vytvořit instanci uživatele: %s', 'cms'), $user->ID, $e->getMessage()), MWLL_ERROR);
			}
		}

		return null;
	}

	/**
	 * Creates new instance of object.
	 */
	public static function createNew(WP_User $user): self
	{
		return new static($user);
	}

	public static function getAll($args = [], $paged = false): array
	{
		$default_args = static::getArgs();

		$query_args = array_merge($default_args, $args);

		$q = new WP_User_Query($query_args);
		$items = array_map(function ($user) {
			return static::createNew($user);
		}, $q->get_results());

		//echo $q->request;
		return $paged ? [
				'items' => $items,
				'pages' => ceil($q->get_total() / $args['number'] ?? 1),
				'count' => $q->get_total(),
		] : $items;
	}

	public static function getArgs()
	{
		return [
			'role__not_in' => 'member',
			'order' => 'ASC',
			'orderby' => 'display_name',
		];
	}

	public static function roleSelect($val, $name, $hide = [])
	{
		$all_roles = wp_roles()->roles;

		$options = [];
		foreach ($all_roles as $role => $details) {
			if (!in_array($role, $hide)) {
				$options[] = [
					'value' => $role,
					'name' => translate_user_role($details['name']),
				];
			}
		}

		if ($val === '') {
			$options[] = [
				'value' => $val,
				'name' => __('Bez přiřazené role', 'cms'),
			];
		}

		return mwAdminComponents::select([
			'name' => $name,
			'options' => $options,
		], $val);
	}

	public static function getContactMethods(): array
	{
		return wp_get_user_contact_methods();
	}

	public static function registerUserObject()
	{
		$args = [
			'service_class' => 'mwSettingObjectService_User',
			'class' => 'mwUser',
			'object_type' => 'user',
			'supports' => ['search'],
			'allow_add' => true,
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Uživatelé', 'cms'),
				'add_item' => __('Přidat uživatele', 'cms'),
				'edit_item' => __('Upravit uživatele', 'cms'),
				'new_item' => __('Nový uživatel', 'cms'),
				'empty' => __('Nebyl nalezen žádný uživatel', 'cms'),
				'notfound' => __('Uživatel nebyl nalezen', 'cms'),
			],

		];

		mwSetting()->registerObject('users', $args);
	}

}

class mwSettingObjectService_User extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Uživatelské jméno', 'cms'),
				],
				[
					'content' => __('Jméno', 'cms'),
				],
				[
					'content' => __('Email', 'cms'),
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();
		$search = isset($filter['s']) && $filter['s'] ? '*' . $filter['s'] . '*' : '';

		$users = mwUser::getAll([
			'paged' => $page,
			'number' => $perPage,
			'search' => $search,
		], true);

		$args['pagination'] = [
			'pages' => $users['pages'],
			'count' => $users['count'],
		];

		$currentUser = mwUser::getCurrent();

		foreach ($users['items'] as $user) {
			$actions = $user->getId() == $currentUser->getId() ? ['edit'] : ['edit', 'delete'];

			$args['rows'][] = [
				'bulk_id' => $user->getId(),
				'cols' => [
					[
						'content' => mwAdminComponents::link([
							'text' => $user->getAvatar(30) . ' <span>' . $user->getLogin() . '</span>',
							'link' => $this->object()->getEditUrl($user->getId()),
						], 'mw_link mw_user_list_detail_link'),
					],
					[
						'content' => $user->getName(),
					],
					[
						'content' => $user->getEmail(),
					],
					[
						'content' => mwSetting()->printSettingActions($actions, $user->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function printForm($item, $add = false)
	{
		$itemId = $item ? $item->getId() : '';

		$meta_set = mwSetting()->getUserSetting();

		$tabs = [];
		foreach ($meta_set as $set) {
			$tabs[] = [
				'id' => $set['id'],
				'name' => $set['title'],
			];
		}
		echo '<div class="mw_setting_tabs_container mw_onedit_action" data-type="tabs">';
		echo mwAdminComponents::tabs([
			'tabs' => $tabs,
			'group' => 'mw_object_setting_tab',
		], '', 'mw_setting_tabs');
		echo '</div>';

		echo '<div class="mw_setting_object_detail_content">';
		echo '<div class="mw_setting_object_detail_form">';

		$meta = $itemId ? $item->getSettingMeta() : [];

		$i = 1;
		foreach ($meta_set as $set) {
			$setName = $set['name'] ?? $set['id'];
			echo '<div id="mw_object_setting_tab_' . $set['id'] . '" class="mw_tab mw_object_setting_tab_container ' . ($i == 1 ? 'active' : '') . '">';
			write_meta($set['fields'], $meta[$set['id']] ?? [], $setName, $setName, $itemId);
			$i++;
			echo '</div>';
		}

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="object_id" value="' . $this->object()->getId() . '"/>';
		if (!$add) {
				echo '<input type="hidden" name="item_id" value="' . $itemId . '"/>';
		}

		echo '</div>';

		echo $this->printFormSidebar($item, $add);

		echo '</div>';
	}

	function printFormSidebar($item, $add = false, $inPopup = false): string
	{
		$content = '<div class="mw_setting_object_detail_sidebar">';

		$content .= '<div class="mw_setting_sidebar_box">';

		$content .= '<div class="mw_setting_sidebar_main_set">';
		$content .= mwAdminComponents::title([
			'text' => __('Role', 'cms'),
		]);
		$content .= mwUser::roleSelect(($item ? ($item->getRole() ?? '') : 'administrator'), 'user[role]', $add ? ['member'] : []);
		$content .= '</div>';

		if (!$add) {
			$content .= $this->getInfoList($item);
			if (!$inPopup) {
				$content .= $this->getDetailActionList($item);
			}
		} else {
			$content .= '<div class="set_form_subrow">';
			$content .= mwAdminComponents::title([
				'text' => __('Informovat uživatele', 'cms'),
			]);
			$content .= mwAdminComponents::switch([
				'switch_label' => __('Poslat uživateli email s&nbsp;informací o jeho novém účtu', 'cms'),
				'name' => 'send_user_notification',
			], 1);
			$content .= '</div>';
		}
		$content .= '</div>';


		if ($item) {
			$content .= '<div class="mw_setting_sidebar_box">';
			$content .= mwAdminComponents::title([
				'text' => __('Profilový obrázek', 'cms'),
			]);
			$content .= '<div class="mw_setting_gravatar_sidebar">';
			$content .= $item->getAvatar(80);
			$content .= '<div>' . __('Profilový obrázek (avatar) lze nastavit na adrese', 'cms') . ' <a target="_blank" href="https://cs.gravatar.com/">gravatar.com</a></div>';
			$content .= '</div>';
			$content .= '</div>';
		}

		$content .= '</div>';

		return $content;
	}

	public function getInfoList($item): string
	{
		$content = '<div class="mw_setting_sidebar_info">';
		$content .= '<div class="mw_setting_sidebar_info_row">';
		$content .= '<span>' . __('Vytvořen', 'cms') . ':</span>';
		$content .= '<span>' . $item->getDateCreated() . '</span>';
		$content .= '</div>';
		$content .= '<div class="mw_setting_sidebar_info_row">';
		$content .= '<span>' . __('Naposledy', 'cms') . ':</span>';
		$content .= '<span>' . $item->getLastLoginDate('diff') . '</span>';
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	public function getDetailActionList($item): string
	{
		$currentUser = mwUser::getCurrent();
		$content = '<ul class="mw_setting_detail_action_list">';

		if ($this->object()->isPublic()) {
			$content .= '<li>';
			$content .= mwAdminComponents::iconLink([
				'icon' => 'file',
				'text' => __('Zobrazit stránku', 'cms'),
				'target' => '_blank',
				'link' => $this->getItemUrl($item->getId()),
			], 'mw_setting_action_link');
			$content .= '</li>';
		}

		if ($item->getId() != $currentUser->getId()) {
			$content .= '<li>';
			$content .= mwAdminComponents::iconLink([
				'icon' => 'trash-2',
				'text' => __('Smazat', 'cms'),
				'attrs' => 'data-id="' . $item->getId() . '" data-objectid="' . $this->object()->getId() . '"',
			], 'mw_setting_action_link mw_setting_detail_delete_item');
			$content .= '</li>';
		}
		$content .= '</ul>';

		return $content;
	}

	public function checkData($tosave, $itemId = 0, $fast = false, bool $add = false): bool
	{
		if (!isset($tosave['user']['user_login']) || !$tosave['user']['user_login']) {
			mwMessages()->error(__('Uživatelské jméno je povinná položka, prosím vyplňte jej', 'cms'));

			return false;
		} else {
			$user = mwUser::getOneBy($tosave['user']['user_login'], 'login');

			if ($user && $user->getId() != $itemId) {
				mwMessages()->error(__('Zvolené uživatelské jméno již bohužel existuje.', 'cms'));

				return false;
			}
		}

		if (!isset($tosave['user']['user_email']) || !$tosave['user']['user_email']) {
			mwMessages()->error(__('Email je povinná položka, prosím vyplňte jej', 'cms'));

			return false;
		}

		if (!is_email($tosave['user']['user_email'])) {
			mwMessages()->error(__('Emailová adresa je v chybném formátu', 'cms'));

			return false;
		}


		if (!$itemId) {
			if (isset($tosave['user']['password']) && !$tosave['user']['password']) {
				mwMessages()->error(__('Heslo je povinná položka, prosím vyplňte jej', 'cms'));

				return false;
			}
		}

		return true;
	}

	public function add($tosave, $fast = false): ?int
	{
		$user = mwUser::addUser($tosave['user']);

		if ($user) {
			if (isset($tosave['send_user_notification']) && $tosave['user']['role'] !== 'member') {
				wp_new_user_notification($user->id);
			}

			do_action('mw_add_user', $user->id, $user->password, $tosave);

			return $user->id;
		}

		return null;
	}

	public function save($itemId, $tosave)
	{
		$updated = mwUser::updateUser($itemId, $tosave['user']);

		if ($updated) {
			do_action('mw_save_user', $itemId, $tosave);
		}
	}

	public function delete($id, $force_delete = false)
	{
		$admins = get_users(['role' => 'administrator']);
		if (count($admins)) {
			wp_delete_user($id, $admins[0]->ID);
		}
	}

	public function getItemUrl($id): string
	{
		return get_author_posts_url($id);
	}

}
