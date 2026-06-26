<?php declare(strict_types=1);

use Nette\Http\UrlScript;
use Nette\Utils\Validators;

class mwSettingPageService_eshopEmails extends mwSettingPageService
{
	private const MAXIMUM_ATTACHMENT_SIZE_MB = 12;

	private bool $save;

	private string $errorText;

	public function saveSetting($tosave)
	{
		$this->save = true;
		$this->errorText = '';

		foreach ($tosave as $emailId => $email) {
			if (MwsEmailType::isValidValue($emailId)) {
				$this->checkHtmlTags(MwsEmailType::getCaption($emailId), $email);

				if ($emailId === MwsEmailType::CustomEmails) {
					foreach ($email as $customEmail) {
						$this->checkHtmlTags(
							MwsEmailType::getCaption($emailId) . (isset($customEmail['name']) ? ' - ' . $customEmail['name'] : ''),
							$customEmail['email'] ?? $customEmail
							);
						$this->checkAttachment(
							MwsEmailType::getCaption($emailId) . (isset($customEmail['name']) ? ' - ' . $customEmail['name'] : ''),
							$customEmail['email'] ?? $customEmail
							);
					}
				} elseif ($emailId === MwsEmailType::NewOrder) {
					$this->checkAttachment(
						MwsEmailType::getCaption($emailId),
						$email
						);
				}
			}
		}
		if ($this->save) {
			MWDB()->setOption($this->settingPage()->getId(), $tosave);
		} else {
			mwMessages()->error($this->errorText);
		}
	}

	private function checkHtmlTags(string $emailId, array $email)
	{
		if (isset($email['subject']) && $email['subject'] !== strip_tags($email['subject'], '<b><i><strong>')) {
			$this->errorText .=
				sprintf(__('Předmět emailu <strong>%s</strong> obsahuje nepovolené HTML tagy.', 'cms'), $emailId) . '<br>'
				. sprintf(__('Povolené tagy jsou pouze %s.', 'cms'), htmlspecialchars('<b>, <i> a <strong>')) . '<br>';
			$this->save = false;
		}
		if (isset($email['content']) && $email['content'] !== strip_tags($email['content'], '<b><i><strong>')) {
			$this->errorText .=
				sprintf(__('V obsahu emailu <strong>%s</strong> jsou nepovolené HTML tagy.', 'cms'), $emailId) . '<br>'
				. sprintf(__('Povolené tagy jsou pouze %s.', 'cms'), htmlspecialchars('<b>, <i> a <strong>')) . '<br>';
			$this->save = false;
		}
	}

	private function checkAttachment(string $emailId, array $email)
	{
		if (isset($email['attachment'])) {
			$file = $email['attachment'];
			if (Validators::isUrl($file)) {
				$url = new UrlScript($file);
				$file = rtrim(ABSPATH, '/') . '/' . ltrim($url->getPath(), '/');
			}

			if (file_exists($file)) {
				$actualSize = filesize($file);

				if ($actualSize > self::MAXIMUM_ATTACHMENT_SIZE_MB * pow(2, 20)) {
					$this->errorText .= sprintf(
						__('Překročena maximální povolená velikost přílohy v emailu <strong>%s</strong> (%d MB) . <br>', 'cms'),
						$emailId,
						self::MAXIMUM_ATTACHMENT_SIZE_MB
					);
					$this->save = false;
				}
			}
		}
	}
}
