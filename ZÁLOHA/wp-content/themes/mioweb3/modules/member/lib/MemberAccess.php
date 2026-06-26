<?php

namespace Mioweb\Member;

class MemberAccess
{

	private $_memberSection;

	private $_memberPage;

	private $_membership;

	private $_noAccessPageId = null;

	private $_noAccessPageRedirect = '';

	private $_noAccessMessage = '';

	private $_noAccessButtonLink = null;

	private $_noAccessButtonText = '';

	private $_showHomeLink = true;

	public function __construct(?Membership $membership, MemberPage $memberPage, \MwMemberSection $memberSection)
	{
		$this->_membership = $membership;
		$this->_memberPage = $memberPage;
		$this->_memberSection = $memberSection;

		$this->_noAccessButtonText = __('Získat přístup', 'cms_member');
	}

	public function setPageContent(?int $pageId): void
	{
		$this->_noAccessPageId = $pageId;
	}

	public function setPageRedirect(string $pageUrl): void
	{
		$this->_noAccessPageRedirect = $pageUrl;
	}

	public function setPageMessage(string $message, string $buttonLink = '', string $buttonText = '', bool $showHomeLink = true): void
	{
		$this->_noAccessMessage = $message;
		$this->_showHomeLink = $showHomeLink;

		if ($buttonLink) {
			$this->_noAccessButtonLink = $buttonLink;
		}
		if ($buttonText) {
			$this->_noAccessButtonText = $buttonText;
		}
	}

	public function checkAccess(): bool
	{
		// if no access
		if ($this->_membership === null) {
			$this->setPageRedirect($this->_memberSection->getNoAccessUrl());
			$this->setPageMessage(__('Do této členské sekce nemáte přístup.', 'cms_member'), '', '', false);

			return false;
		}

		// if is expired
		if ($this->_membership->isExpired()) {
			$this->setPageRedirect($this->_memberSection->getExpireUrl());
			$this->setPageMessage(
				__('Vaše členství již vypršelo.', 'cms_member'),
				$this->_memberSection->getExtendUrl(),
				__('Prodloužit členství', 'cms_member'),
				false
			);

			return false;
		}

		// if no level access
		if (!$this->_membership->hasLevelAccess($this->_memberPage->getLevels())) {
			$firstLevel = null;
			if ($this->_memberPage->getFirstLevel()) {
				$firstLevel = MemberLevel::getOneById($this->_memberPage->getFirstLevel());
			}

			$this->setPageContent($firstLevel ? $firstLevel->getNoAccessId() : null);
			$this->setPageMessage($firstLevel && $firstLevel->getNoAccessText() ? $firstLevel->getNoAccessText() : __('Pro přístup k této stránce nemáte dostatečné oprávnění.', 'cms_member'));

			return false;
		}

		// if level access expired
		/*
		if ($expiredLevel = $this->_membership->isLevelAccessExpired($this->_memberPage->getLevels()))
		{
			$this->setPageRedirect($expiredLevel->getExpireUrl());
			$this->setPageMessage(__('Vaše členství v této členské sekci již vypršelo.', 'cms_member'));

			return false;
		} */

		// if no month access
		if ($this->_memberPage->isMonth() && !$this->_membership->hasMonthAccess($this->_memberPage->getMonth()->getMonth())) {
			$this->setPageMessage(__('Pro přístup k této stránce nemáte dostatečné oprávnění.', 'cms_member'), $this->_memberPage->getMonthPageUrl());

			return false;
		}

		/* member has access but with restrictions */

		// checklist
		if ($this->_memberPage->getAccessType() === 'checklist' && !$this->_memberPage->isPreviousPageCompleted()) {
			$this->setPageMessage(
				__('Obsah této stránky je momentálně nedostupný. Bude uvolněn po splnění úkolů předešlé lekce. ', 'cms_member'),
				get_permalink($this->_memberPage->getForCheckListPageId()),
				__('Přejít na předešlou lekci', 'cms_member')
			);

			return false;
		}

		// available in future - evergreen, date, month
		if ($time = $this->_memberPage->isAvailableInFuture($this->_membership->getStart())) {
			$this->setPageMessage(__('Obsah této stránky je momentálně nedostupný. Bude uvolněn', 'cms_member') . ' <b>' . str_replace(' 00:00', '', date('d.m.Y H:i', $time)) . '</b>.');

			return false;
		}

		return true;
	}

	public function showNoAccessPage(): void
	{
		if ($this->_noAccessPageId) {
			global $vePage;
			$vePage->resetPageId($this->_noAccessPageId);
		} elseif ($this->_noAccessPageRedirect) {
			wp_redirect($this->_noAccessPageRedirect);
			die();
		} else {
			$this->printInfoPage();
		}
	}

	public function printInfoPage()
	{
		global $vePage;

		$content_text = '<p style="text-align: center;">' . $this->_noAccessMessage . '</p>';
		if ($this->_noAccessButtonLink) {
			$content_text .= '<p style="text-align: center;"><a class="ve_content_button ve_content_button_type_1 ve_content_button_style_basic ve_content_button_center ve_content_button_size_medium"  href="' . $this->_noAccessButtonLink . '">' . $this->_noAccessButtonText . '</a></p>';
			$content_text .= '<p style="text-align:center;">';
			if ($this->_showHomeLink) {
				$content_text .= __('nebo', 'cms_member') . ' ';
				$content_text .= '<a href="' . $this->_memberSection->getUrl() . '">' . __('přejít na nástěnku', 'cms_member') . '</a></p>';
			}
		} elseif ($this->_showHomeLink) {
			$content_text .= '<p style="text-align:center;"><a href="' . $this->_memberSection->getUrl() . '">' . __('Přejít na nástěnku', 'cms_member') . '</a></p>';
		}

		$vePage->display->template['directory'] = 'page/1/';
		$vePage->display->layer = mwMemberModule()->getDefaultLayer('noaccess', [
			'content_text' => $content_text,
		]);
	}

}
