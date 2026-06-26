<?php

class MwsTag extends mwTerm
{

	private string $_color;

	public function __construct($term)
	{
		parent::__construct($term);
		$meta = $this->getMeta('mw_product_tag');
		$this->_color = $meta['color'] ?? '';
	}

	public function getColor(): string
	{
		return $this->_color;
	}

	public static function printAdminLabel(MwsTag $term): string
	{
		$text = mwAdminComponents::input([
			'name' => 'taxonomy[' . $term->getTaxonomy() . '][]',
			'type' => 'hidden',
		], $term->getName());
		$text .= $term->getName();

		return mwAdminComponents::textLabel([
			'text' => $text,
			'color' => $term->getColor(),
			'close' => true,
			'close_attrs' => 'data-itemid="' . $term->getId() . '"',
		], 'mw_text_tag_big');
	}

	public static function fastAddProductTag_ajax()
	{
		$return = [];

		if (mwSetting()->verifyNonce('mw_save_setting_nonce')) {
			$object = mwSetting()->getObject(MWS_PRODUCT_TAG_SLUG);

			if ($object) {
				$tosave = $_POST;

				$item = mwSetting()->addNewObject($object, $tosave, true);

				if ($item) {
					$tag = self::printAdminLabel($item);

					$wItem = '<li class="mw_input_whisperer_item mw_input_whisperer_item_' . $item->getId() . ' whisperer_item_used">';
					$wItem .= '<a href="#" data-text="' . $item->getName() . '"><span style="background-color:' . $item->getColor() . '"></span>' . $item->getName() . '</a>';
					$wItem .= mwAdminComponents::textarea([
						'name' => '',
					], $tag, 'mws_product_tag_html cms_nodisp');
					$wItem .= '</li>';

					$return = [
						'tag' => $tag,
						'whisperer_item' => $wItem,
					];
				}
			}
		} else {
			mwMessages()->error(__('Došlo k chybě a uložení se nezdařilo. Prosím zkuste to znovu.', 'cms'));
		}

		$return['success'] = mwMessages()->success;
		$return['errors'] = mwMessages()->errors;
		$return['html'] = mwMessages()->writeHtml();

		wp_send_json($return);

		die();
	}

}
