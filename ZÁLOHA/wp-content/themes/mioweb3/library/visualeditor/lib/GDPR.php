<?php
namespace Mioweb\VisualEditor\Lib;

final class GDPR
{

	public static function getGdprSetting(): ?array
	{
		$gdpr = get_option('web_option_gdpr');

		return $gdpr ?: null;
	}

	public static function printConsent(string $formType, string $consent = '', string $linkText = '', bool $showEmpty = false): string
	{
		$gdpr = self::getGdprSetting();

		$class = ' mw_checkbox ve_form_required ve_form_checkbox ';
		$containerClass = '';

		if ($formType === 'comment') {
			$consent = $gdpr['comment_form_info'] ?? '';
			$linkText = $gdpr['comment_form_link_text'] ?? '';
			$class = '';
		} elseif ($formType === 'contact') {
			$consent = $gdpr['contact_form_info'] ?? '';
			$linkText = $gdpr['contact_form_link_text'] ?? '';
		}

		$field = '';

		if ($consent || $showEmpty) {
			if (isset($gdpr['gdpr_check'])) {
				$type = 'checkbox';
				$tag = 'label';
				$containerClass = 'mw_field_gdpr_accept_with_checkbox';
			} else {
				$type = 'hidden';
				$class = '';
				$tag = 'div';
			}

			$field .= '<' . $tag . ' class="mw_field_gdpr_accept ' . $containerClass . '">';
			$field .= '<input type="' . $type . '" value="' . $consent . '" name="mw_gdpr_consent" class="' . $class . '" required="required" />';
			$field .= '<span>' . $consent . '</span>';
			if (($linkText || $showEmpty) && isset($gdpr['gdpr_url']) && Link::create_link($gdpr['gdpr_url'], false) !== '') {
				$field .= ' <a href="' . Link::create_link($gdpr['gdpr_url'], false) . '" target="_blank">' . $linkText . '</a>';
			}
			$field .= '</' . $tag . '>';
		}

		return $field;
	}
}
