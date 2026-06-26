<?php
use Mioweb\Member\MemberLevel;
use Mioweb\Member\MemberPage;
use Mioweb\Member\MonthMembership;
use Mioweb\Lib\Email;

class MwMemberSection
{

	private $_id;

	private $_name;

	private $_dashboardPageId;

	private $_loginPageId;

	private $_noAccessPageId;

	private $_extendPageId;

	private $_expirePageId;

	private $_hideEvergreen;

	private $_sendNotifications;

	private $_notificationEmail;

	private $_appearance = null;

	private $_popups = null;

	private $_header = null;

	private $_footer = null;

	private $_emails = null;

	private $_levels = null;

	private $_months = null;

	public function __construct($member)
	{
		$this->_id = $member->id;
		$this->_name = $member->name;
		$this->_dashboardPageId = $member->dashboard_page_id;
		$this->_loginPageId = $member->login_page_id;
		$this->_noAccessPageId = $member->noaccess_page_id;
		$this->_extendPageId = $member->extend_page_id;
		$this->_expirePageId = $member->expire_page_id;
		$this->_hideEvergreen = (bool) $member->hide_evergreen;
		$this->_notificationEmail = $member->notification_email;
		$this->_sendNotifications = (bool) $member->send_notifications;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getName(): string
	{
		return $this->_name ?: __('(Bez názvu)', 'cms');
	}

	public function getDashboardId(): ?int
	{
		return $this->_dashboardPageId;
	}

	public function getUrl(string $default = ''): string
	{
		return $this->getDashboardId() ? get_permalink($this->getDashboardId()) : '';
	}

	public function getLoginId(): ?int
	{
		return $this->_loginPageId;
	}

	public function getLoginUrl(): string
	{
		return $this->getLoginId() ? get_permalink($this->getLoginId()) : $this->getUrl();
	}

	public function getNoAccessId(): ?int
	{
		return $this->_noAccessPageId;
	}
	public function getNoAccessUrl(): string
	{
		return $this->getNoAccessId() ? get_permalink($this->getNoAccessId()) : '';
	}

	public function getExtendId(): ?int
	{
		return $this->_extendPageId;
	}

	public function getExtendUrl(): string
	{
		return $this->getExtendId() ? get_permalink($this->getExtendId()) : '';
	}

	public function getExpireId(): ?int
	{
		return $this->_expirePageId;
	}
	public function getExpireUrl(): string
	{
		return $this->getExpireId() ? get_permalink($this->getExpireId()) : '';
	}

	public function getLogoutUrl(): string
	{
		return wp_logout_url($this->getLoginUrl());
	}

	public function hideEvergreen(): bool
	{
		return $this->_hideEvergreen;
	}

	public function sendNotifications(): bool
	{
		return $this->_sendNotifications;
	}
	public function getNotificationEmail(): string
	{
		return $this->_notificationEmail;
	}

	public function getEmail(string $type): Email
	{
		if ($this->_emails === null) {
			$this->_emails = Email::getAll('member', $this->getId());
		}

		return $this->_emails[$type] ?? new Email(null, '', '', '', '', 0);
	}

	public function getLevels(): array
	{
		if ($this->_levels === null) {
			$this->_levels = MemberLevel::getAll($this->getId());
		}

		return $this->_levels;
	}

	public function hasLevels(): bool
	{
		return (bool) count($this->getLevels());
	}

	public function getMonths(): array
	{
		if ($this->_months === null) {
			$this->_months = MonthMembership::getAllMonths($this->getId());
		}

		return $this->_months;
	}

	public function hasMonths(): bool
	{
		return count($this->getMonths()) ? true : false;
	}

	public function getAppearanceSetting(): array
	{
		return MWDB()->getOption('mwms_appearance_' . $this->getId(), []);
	}

	public function getFooterSetting(): array
	{
		return MWDB()->getOption('mwms_footer_' . $this->getId(), []);
	}

	public function getHeaderSetting(): array
	{
		return MWDB()->getOption('mwms_header_' . $this->getId(), []);
	}

	public function getPopupsSetting(): array
	{
		return MWDB()->getOption('mwms_popups_' . $this->getId(), []);
	}

	function getPages($args = [])
	{
		return MemberPage::getMemberPages($this->getId(), $args);
	}

	public static function getOneById(int $memberId, bool $forceUpdateCache = false): ?self
	{
		$member = MWDB()->getTableRow('mw_member_sections', 'id = ' . $memberId);

		return $member ? static::createNew($member, $forceUpdateCache) : null;
	}

	public static function createNew(stdClass $member, bool $forceUpdateCache = false): ?self
	{
		if ($forceUpdateCache || !($obj = MwObjectCache::get(static::class, $member->id))) {
			$obj = new self($member);
			MwObjectCache::add($obj, $obj->getId());
		}

		return $obj;
	}

	public static function getAll(): array
	{
		$members = MWDB()->getRows('mw_member_sections', '', 'id DESC');

		$ret = [];
		foreach ($members as $member) {
			$ms = static::createNew($member);
			$ret[$ms->getId()] = $ms;
		}

		return $ret;
	}

	public function toSettingArray(): array
	{
		return [
			'member_basic' => [
				'name' => $this->getName(),
				'dashboard_page_id' => $this->getDashboardId(),
				'login_page_id' => $this->getLoginId(),
				'noaccess_page_id' => $this->getNoAccessId(),
				'extend_page_id' => $this->getExtendId(),
				'expire_page_id' => $this->getExpireId(),
				'hide_evergreen' => $this->hideEvergreen() ? 1 : 0,
				'send_notifications' => $this->sendNotifications() ? 1 : 0,
				'notification_email' => $this->getNotificationEmail(),
				'levels' => $this->levelsToSettingArray(),
			],
			'member_appearance' => $this->getAppearanceSetting(),
			'member_header' => $this->getHeaderSetting(),
			'member_footer' => $this->getFooterSetting(),
			'member_popups' => $this->getPopupsSetting(),
		];
	}

	public function levelsToSettingArray(): array
	{
		$levels = [];
		foreach ($this->getLevels() as $level) {
			$levels[] = $level->toSettingArray();
		}

		return $levels;
	}

	public static function registerMemberSections(): void
	{
		$mwArgs = [
			'service_class' => 'mwSettingObjectService_MemberSection',
			'class' => 'MwMemberSection',
			'object_type' => 'member_section',
			'allow_add' => true,
			'labels' => [
				'title' => __('Členské sekce', 'cms_member'),
				'add_item' => __('Přidat členskou sekci', 'cms_member'),
				'edit_item' => __('Upravit členskou sekci', 'cms_member'),
				'new_item' => __('Nová členská sekce', 'cms_member'),
				'delete' => __('Smazat členskou sekci', 'cms_member'),
				'empty' => __('Nebyla nalezena žádná členská sekce', 'cms_member'),
				'notfound' => __('Členská sekce nebyla nalezena', 'cms'),
			],

		];
		mwSetting()->registerObject('member_sections', $mwArgs);
	}

}

class mwSettingObjectService_MemberSection extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Název', 'cms_member'),
				],
				[
					'content' => __('Akce', 'cms_member'),
					'align' => 'right',
				],
			],
		];

		$members = mwMemberModule()->getMemberSections();

		foreach ($members as $item) {
			$name = '<a class="mw_link" href="' . $this->object()->getEditUrl($item->getId()) . '">' . $item->getName() . '</a>';

			if (!$item->getDashboardId()) {
				$name .= mwAdminComponents::tooltip([
					'icon' => '!',
					'text' => __('Tato členská sekce nemá nastavenou žádnou stránku jako nástěnku. Pro správné fungování musíte členské sekci nastavit nástěnku.', 'cms_member'),
				], 'mw_tooltip_alert');
			}


			$args['rows'][] = [
				'cols' => [
					[
						'content' => $name,
					],
					[
						'content' => mwSetting::printSettingActions(['edit', 'delete'], $item->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function printTitle($item = null): string
	{
		if (isset($_GET['edit'])) {
			$onright = '<div class="mw_member_setting_api_key">';
			$onright .= __('API klíč', 'cms_member') . ': <strong>' . mwMemberModule()->getApiKey() . '</strong>';
			$onright .= '</div>';

			$args = [
				'text' => $this->object()->getLabel('edit_item'),
				'onright' => $onright,
			];
		} else {
			$args = [
				'text' => $this->object()->getLabel('title') . mwSetting()->getHelpLink($this->object()->getId()),
			];

			$args['onright'] = mwAdminComponents::button([
				'button_text' => $this->object()->getLabel('add_item'),
				'icon' => 'plus',
				'attrs' => 'data-object="' . $this->object()->getId() . '" data-title="' . $this->object()->getLabel('add_item') . '"',
			], 'mw_member_fast_add');
		}

		return mwAdminComponents::title($args, 'h2');
	}

	public function printForm($item, $add = false)
	{
		$itemId = $item->getId();

		$main_tabs = [
			[
				'id' => 'setting',
				'name' => __('Nastavení', 'cms_member'),
			],
			[
				'id' => 'appearance',
				'name' => __('Vzhled', 'cms_member'),
			],
		];

		echo '<div class="mw_onedit_action mw_big_tabs_container" data-type="tabs">';
		echo mwAdminComponents::tabs([
			'tabs' => $main_tabs,
			'group' => 'member_section_tab',
		], '', 'mw_big_tabs');
		echo '</div>';

		$meta_set = $this->object()->getSetting();

		$j = 1;
		foreach ($main_tabs as $mtab) {
			echo '<div id="member_section_tab_' . $mtab['id'] . '" class="mw_tab member_section_tab_container ' . ($j == 1 ? 'active' : '') . '">';

			$tabs = [];
			foreach ($meta_set as $set) {
				if ($set['group'] == $mtab['id']) {
					$tabs[] = [
						'id' => $set['tab_id'] ?? $set['id'],
						'name' => $set['title'],
					];
				}
			}
			echo '<div class="mw_setting_tabs_container mw_onedit_action" data-type="tabs">';
			echo mwAdminComponents::tabs([
				'tabs' => $tabs,
				'group' => 'mw_ms_setting_' . $mtab['id'] . '_tab',
			], '', 'mw_setting_tabs');
			echo '</div>';

			echo '<div class="mw_setting_object_detail_content">';

			echo '<div class="mw_setting_object_detail_form">';

			$i = 1;
			$meta = $item->toSettingArray();
			foreach ($meta_set as $set) {
				if ($set['group'] == $mtab['id']) {
					echo '<div id="mw_ms_setting_' . $mtab['id'] . '_tab_' . ($set['tab_id'] ?? $set['id']) . '" class="mw_tab mw_ms_setting_' . $mtab['id'] . '_tab_container ' . ($i == 1 ? 'active' : '') . '">';
					write_meta($set['fields'], $meta[$set['id']] ?? [], $set['id'], $set['id'], $itemId);
					$i++;
					echo '</div>';
				}
			}

			echo '</div>';

			echo '</div>';

			echo '</div>';
			$j++;
		}

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="object_id" value="' . $this->object()->getId() . '"/>';
		echo '<input type="hidden" name="item_id" value="' . $itemId . '"/>';
	}

	function add($tosave, $fast = false): int
	{
		$newMemberSectionId = MWDB()->insert('mw_member_sections', [
			'name' => $tosave['member_basic']['name'],
		]);

		if ($newMemberSectionId) {
			MwSellingApi()->sendMemberInfo();

			// default emails
			$setting = $this->object()->getSetting('member_emails');
			$defaultEmails = mwSetting()->getDefaultSetting($setting['fields']);
			Email::saveEmailsSetting($newMemberSectionId, 'member', $defaultEmails['emails']);

			// default header
			$setting = $this->object()->getSetting('member_header');
			$defaultSet = mwSetting()->getDefaultSetting($setting['fields']);
			MWDB()->setOption('mwms_header_' . $newMemberSectionId, $defaultSet);

			// default footer
			$setting = $this->object()->getSetting('member_footer');
			$defaultSet = mwSetting()->getDefaultSetting($setting['fields']);
			MWDB()->setOption('mwms_footer_' . $newMemberSectionId, $defaultSet);

			// default appearance
			$setting = $this->object()->getSetting('member_appearance');
			$defaultSet = mwSetting()->getDefaultSetting($setting['fields']);
			MWDB()->setOption('mwms_appearance_' . $newMemberSectionId, $defaultSet);
		}

		return $newMemberSectionId;
	}

	public function save($itemId, $tosave)
	{
		// save member section
		$status = MWDB()->update('mw_member_sections', [
			'name' => $_POST['member_basic']['name'],
			'dashboard_page_id' => $_POST['member_basic']['dashboard_page_id'] ?: null,
			'login_page_id' => $_POST['member_basic']['login_page_id'] ?: null,
			'noaccess_page_id' => $_POST['member_basic']['noaccess_page_id'] ?: null,
			'extend_page_id' => $_POST['member_basic']['extend_page_id'] ?: null,
			'expire_page_id' => $_POST['member_basic']['expire_page_id'] ?: null,
			'hide_evergreen' => isset($_POST['member_basic']['hide_evergreen']) ? 1 : 0,
			'send_notifications' => isset($_POST['member_basic']['send_notifications']) ? 1 : 0,
			'notification_email' => $_POST['member_basic']['notification_email'] ?: '',
		], [
			'id' => $itemId,
		]);

		if ($status !== false) {

			mwlog(MWLS_MEMBER, 'Member section ID:' . $itemId . ' (' . $_POST['member_basic']['name'] . ') saved.');

			// save levels
			$doNotDelete = [];
			foreach ($_POST['member_basic']['levels'] ?? [] as $level) {
				$levelData = [
					'member_section_id' => $itemId,
					'name' => $level['name'],
					'noaccess_text' => $level['noaccess_text'],
					'noaccess_page_id' => $level['noaccess_page_id'] ?: null,
					'extend_page_id' => $level['extend_page_id'] ?: null,
					'expire_page_id' => $level['expire_page_id'] ?: null,
					'show_level_pages' => isset($level['show_level_pages']) ? 1 : 0,
				];

				if ($level['id']) {
					MWDB()->update('mw_member_section_levels', $levelData, [
						'id' => $level['id'],
					]);
					$doNotDelete[] = $level['id'];
					mwlog(MWLS_MEMBER, 'Member section ID:' . $itemId . ' level ID: ' . $level['id'] . ' (' . $level['name'] . ') saved.');
				} else {
					$newLeveId = MWDB()->insert('mw_member_section_levels', $levelData);
					$doNotDelete[] = $newLeveId;
					mwlog(MWLS_MEMBER, 'Member section ID:' . $itemId . ' level ID: ' . $newLeveId . ' (' . $level['name'] . ') created.');
				}
			}

			// delete levels
			foreach (MemberLevel::getAll($itemId) as $level) {
				if (!in_array($level->getId(), $doNotDelete)) {
					MWDB()->delete('mw_member_section_levels', [
						'id' => $level->getId(),
					]);
					mwlog(MWLS_MEMBER, 'Member section ID:' . $itemId . ' level ID: ' . $level->getId() . ' deleted.');
				}
			}

			// save emails
			Email::saveEmailsSetting($itemId, 'member', $_POST['member_emails'] ?? []);

			// save appearance
			MWDB()->setOption('mwms_appearance_' . $itemId, $_POST['member_appearance'] ?? []);
			// save footer
			MWDB()->setOption('mwms_footer_' . $itemId, $_POST['member_footer'] ?? []);
			// save header
			MWDB()->setOption('mwms_header_' . $itemId, $_POST['member_header'] ?? []);
			// save popups
			MWDB()->setOption('mwms_popups_' . $itemId, $_POST['member_popups'] ?? []);
		} else {
			mwMessages()->error(__('Členská sekce se nepodařila uložit.', 'cms_member'));
		}

		mwSetting::saveUsed($tosave);
	}

	public function checkData($tosave, $itemId = 0, $fast = false, bool $add = false): bool
	{
		if (isset($tosave['member_basic']['dashboard_page_id']) && $tosave['member_basic']['dashboard_page_id']) {
			$row = MWDB()->getTableRow('mw_member_sections', 'id != ' . $itemId . ' AND (dashboard_page_id = ' . $tosave['member_basic']['dashboard_page_id'] . ' OR login_page_id = ' . $tosave['member_basic']['dashboard_page_id'] . ')');

			if ($row) {
				mwMessages()->error(__('Stránka, kterou jste nastavili jako nástěnka je použita již u jiné členské sekce. Prosím nastavte jinou.', 'cms'));

				return false;
			}
		}
		/* zakomentováno protože uživatelé používají rozcestník v členských sekcích a nastavují u všech členských sekcích stejnou přihlašovací stránku.
		if (isset($tosave['member_basic']['login_page_id']) && $tosave['member_basic']['login_page_id']) {
			$row = MWDB()->getTableRow('mw_member_sections', 'id != ' . $itemId . ' AND (dashboard_page_id = ' . $tosave['member_basic']['login_page_id'] . ' OR login_page_id = ' . $tosave['member_basic']['login_page_id'] . ')');

			if ($row) {
				mwMessages()->error(__('Stránka, kterou jste nastavili jako přihlašovací je použita již u jiné členské sekce. Prosím nastavte jinou.', 'cms'));

				return false;
			}
		}*/

		return true;
	}

	function delete($id, $force_delete = false)
	{
		// delete section
		MWDB()->delete('mw_member_sections', [
			'id' => $id,
		]);

		// delete appearance
		MWDB()->deleteOption('mwms_appearance_' . $id);
		// delete footer
		MWDB()->deleteOption('mwms_footer_' . $id);
		// delete header
		MWDB()->deleteOption('mwms_header_' . $id);
		// delete popups
		MWDB()->deleteOption('mwms_popups_' . $id);

		// delete emails
		MWDB()->delete('mw_emails', [
			'item_id' => $id,
			'in_module' => 'member',
		]);
	}

}
