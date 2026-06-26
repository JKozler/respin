<?php
namespace Mioweb\Lib;
use mwAdminComponents;
use MwVariables;
use Nette\Utils\Strings;
use Nette\Http\UrlScript;
use Nette\Utils\Validators;

class Email
{
	private const MAXIMUM_ATTACHMENT_SIZE_MB = 12;

	/** @var null|int */
	private ?int $_id;

	/** @var string */
	private string $_subject;

	/** @var string */
	private string $_text;

	/** @var string */
	private string $_type;

	/** @var string */
	private string $_module;

	/** @var int */
	private int $_item_id;

	/** @var null|string */
	private ?string $_attachment;

	private bool $disabled;

	function __construct(?int $id, string $subject, string $text, ?string $attachment = '', string $module = '', string $type = '', int $itemId = 0)
	{
		$this->_id = $id;
		$this->_subject = $subject;
		$this->_text = $text;
		$this->_module = $module;
		$this->_type = $type;
		$this->_item_id = $itemId;
		$this->_attachment = $attachment;
	}

	public function getId(): ?int
	{
		return $this->_id;
	}

	public function getSubject(): string
	{
		return $this->_subject;
	}

	public function getText(): string
	{
		return $this->_text;
	}

	public function getType(): string
	{
		return $this->_type;
	}

	public function getAttachment(): ?string
	{
		return $this->_attachment;
	}

	public function getModule(): string
	{
		return $this->_module;
	}

	public function getItemId(): string
	{
		return $this->_item_id;
	}

	public static function emailsField(array $emails, array $field, string $name): void
	{
		foreach ($emails as $email) {
			$inputName = $name . '[' . $email->getType() . ']';

			echo '<div class="mw_setting_box mw_email_field_item">';
				echo '<div class="mw_setting_box_head">';
					echo mwAdminComponents::input([
						'type' => 'hidden',
						'name' => $inputName . '[id]',
					], $email->getId());
					echo $field['content'][$email->getType()]['title'];
				echo '</div>';

				echo '<div class="mw_setting_box_content">';

					self::emailField($email, $field, $inputName, $field['content'][$email->getType()] ?? null);

				echo '</div>';
			echo '</div>';
		}
	}

	public static function emailField(self $email, array $field, string $inputName, ?array $defaultContent): void
	{
		echo '<div class="set_form_row"><div class="sublabel">' . __('Předmět', 'cms') . '</div>';
		echo mwAdminComponents::input([
				'type' => 'text',
				'name' => $inputName . '[subject]',
		], $email->getSubject(), 'mw_email_subject_editor');
		echo '</div>';

		echo '<div class="set_form_row"><div class="sublabel">' . __('Obsah emailu', 'cms') . '</div>';
		echo mwAdminComponents::textarea([
			'name' => $inputName . '[content]',
			'rows' => 25,
		], $email->getText(), 'mw_email_content_editor');

		echo '<div class="mw_email_field_footer">';
		$variablesClass = $field['variables_class'] ?? 'MwVariables';
		$type = $field['variables_list'] ?? 'email';
		echo $variablesClass::variableListPop($type, __('Následující proměnné budou v e-mailu nahrazeny skutečnými daty konkrétní objednávky. Můžete tak do e-mailu vložit veškeré potřebné informace o konkrétním členství.', 'mwshop'));
		if ($defaultContent) {
			$isChanged = !Strings::compare(Strings::normalize($defaultContent['subject'] ?? ''), Strings::normalize($email->getSubject()))
					|| !Strings::compare(Strings::normalize($defaultContent['text'] ?? ''), Strings::normalize($email->getText()));

			$jsonDefaultContent = htmlspecialchars(json_encode(str_replace('\t', '', $defaultContent)), ENT_QUOTES);

			echo mwAdminComponents::iconLink([
					'text' => __('Obnovit výchozí', 'mwshop'),
					'icon' => 'refresh-cw',
					'attrs' => 'title="' . __('Obnoví texty e-mailů do výchozích přednastavených hodnot', 'mwshop') . '" data-name="' . $inputName . '" data-content="' . $jsonDefaultContent . '"',
			], 'mw_reset_email_content' . ($isChanged ? '' : ' cms_nodisp'));
		}
		echo '</div>';
		echo '</div>';

		if (isset($field['attachment']) && $field['attachment']) {
			echo '<div class="set_form_row"><div class="sublabel">' . __('Příloha emailu', 'cms') . '</div>';
			cms_generate_field_upload_file($inputName . '[attachment]', '', $email->getAttachment());
			echo '</div>';
		}
	}

	public static function saveEmailsSetting(int $itemId, string $module, array $emails): void
	{
		foreach ($emails as $type => $email) {
			$emailData = [
				'item_id' => $itemId,
				'text' => $email['content'],
				'subject' => $email['subject'],
				'attachment' => $email['attachment'] ?? null,
				'type' => $type,
				'in_module' => $module,
			];

			if (isset($email['id']) && $email['id']) {
				MWDB()->update('mw_emails', $emailData, [
					'email_id' => $email['id'],
				]);
			} else {
				MWDB()->insert('mw_emails', $emailData);
			}
		}
	}

	public static function checkHtmlTags(string $emailId, array $email): string
	{
		$errorText = '';

		if (isset($email['subject']) && $email['subject'] !== strip_tags($email['subject'], '<b><i><strong>')) {
			$errorText .=
				sprintf(__('Předmět emailu <strong>%s</strong> obsahuje nepovolené HTML tagy.', 'cms'), $emailId) . '<br>'
				. sprintf(__('Povolené tagy jsou pouze %s.', 'cms'), htmlspecialchars('<b>, <i> a <strong>')) . '<br>';
		}
		if (isset($email['content']) && $email['content'] !== strip_tags($email['content'], '<b><i><strong>')) {
			$errorText .=
				sprintf(__('V obsahu emailu <strong>%s</strong> jsou nepovolené HTML tagy.', 'cms'), $emailId) . '<br>'
				. sprintf(__('Povolené tagy jsou pouze %s.', 'cms'), htmlspecialchars('<b>, <i> a <strong>')) . '<br>';
		}

		return $errorText;
	}

	public static function checkAttachment(string $emailId, array $email): string
	{
		$errorText = '';
		if (isset($email['attachment'])) {
			$file = $email['attachment'];
			if (Validators::isUrl($file)) {
				$url = new UrlScript($file);
				$file = rtrim(ABSPATH, '/') . '/' . ltrim($url->getPath(), '/');
			}

			if (file_exists($file)) {
				$actualSize = filesize($file);

				if ($actualSize > self::MAXIMUM_ATTACHMENT_SIZE_MB * pow(2, 20)) {
					$errorText = sprintf(
						__('Překročena maximální povolená velikost přílohy v emailu <strong>%s</strong> (%d MB) . <br>', 'cms'),
						$emailId,
						self::MAXIMUM_ATTACHMENT_SIZE_MB
					);
				}
			}
		}

		return $errorText;
	}

	public static function getAll(string $modul, int $itemId): array
	{
		$emails = MWDB()->getRows('mw_emails', 'in_module = "' . $modul . '" AND item_id = ' . $itemId);

		$ret = [];
		foreach ($emails as $email) {
			$ret[$email->type] = new self($email->email_id, $email->subject, $email->text, $email->attachment, $email->in_module, $email->type, $email->item_id);
		}

		return $ret;
	}

}
