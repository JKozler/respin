<?php

namespace Mioweb\Member;

use MwObjectCache;

class MemberLevel
{

	private $_id;

	private $_name;

	private $_noAccessPageId = null;

	private $_noAccessText;

	private $_extendPageId = null;

	private $_expirePageId = null;

	private $_isVisible;

	public function __construct($level)
	{
		$this->_id = $level->id;
		$this->_name = $level->name ?: __('(Bez názvu)', 'cms');
		$this->_isVisible = (bool) $level->show_level_pages;
		$this->_noAccessPageId = $level->noaccess_page_id;
		$this->_noAccessText = $level->noaccess_text;
		$this->_extendPageId = $level->extend_page_id;
		$this->_expirePageId = $level->expire_page_id;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function getNoAccessText(): string
	{
		return $this->_noAccessText;
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

	public function getExpireId(): ?int
	{
		return $this->_expirePageId;
	}

	public function isVisible()
	{
		return $this->_isVisible;
	}

	public static function getOneById(int $levelId, bool $forceUpdateCache = false): ?self
	{
		$row = MWDB()->getTableRow('mw_member_section_levels', 'id = ' . $levelId);

		return $row ? static::createNew($row, $forceUpdateCache) : null;
	}

	public static function getAll(?int $memberSectionId): array
	{
		global $wpdb;
		$where = '';
		if ($memberSectionId) {
			$where = 'AND member_section_id = ' . $memberSectionId;
		}
		$levels = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'mw_member_section_levels' . ' WHERE 1 = 1 ' . $where . ' ORDER BY id');

		$ret = [];
		foreach ($levels as $level) {
			$ret[] = static::createNew($level);
		}

		return $ret;
	}

	public static function createNew(object $level, bool $forceUpdateCache = false): ?self
	{
		if ($forceUpdateCache || !($obj = MwObjectCache::get(static::class, $level->id))) {
			$obj = new self($level);
			MwObjectCache::add($obj, $obj->getId());
		}

		return $obj;
	}

	public function toSettingArray()
	{
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'noaccess_text' => $this->getNoAccessText(),
			'noaccess_page_id' => $this->getNoAccessId(),
			'extend_page_id' => $this->getExtendId(),
			'expire_page_id' => $this->getExpireId(),
			'show_level_pages' => $this->isVisible() ? 1 : 0,
		];
	}

	public static function registerMemberSectionLevels(): void
	{
		$mwArgs = [
			'service_class' => 'mwSettingObjectService',
			'class' => 'Mioweb\Member\MemberLevel',
		];
		mwSetting()->registerObject('member_levels', $mwArgs);
	}

}
