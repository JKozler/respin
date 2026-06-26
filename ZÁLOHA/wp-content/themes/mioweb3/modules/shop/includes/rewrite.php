<?php
/**
 * Permalinks helper methods.
 * User: kuba
 * Date: 13.06.16
 * Time: 19:02
 */

class MwsRewrite
{

	public static function registerPermalinks($flush = false, $newPermProduct = null, $newPermProductCat = null)
	{
		mwshoplog(
			__METHOD__ . " flush=[$flush] newProduct=[" . ($newPermProduct === null ? '' : MWS()->getPermalink_Products($newPermProduct)) . ']'
			. ' newProdCat=[' . ($newPermProductCat === null ? '' : MWS()->getPermalink_ProductCat($newPermProductCat, $newPermProduct)) . ']',
			MWLL_DEBUG,
			'permalink'
		);

		//Mount product details bellow main shop page.
		/*      $shopId = MWS()->getHomePage();
		if($shopId > 0) {
		$shopSlug = get_post_field('post_name', $shopId);
		} else
		$shopSlug = '';
		$shopSlug = (empty($shopSlug) ? MWS_PRODUCT_SLUG : $shopSlug);*/

		global $wp_rewrite;

		$postType = MWS_PRODUCT_CAT_SLUG;
		$url = MWS()->getPermalink_ProductCat($newPermProductCat, $newPermProduct);
		mwshoplog('New category of product URL prefix: ' . $url, MWLL_INFO, 'permalink');
		$wp_rewrite->add_permastruct($postType, "{$url}/%$postType%", [
			'with_front' => true,
			'ep_mask' => EP_NONE,
			'pages' => false,
			'feeds' => false,
			'forcomments' => false,
			'walk_dirs' => false,
			'endpoints' => false,
		]);

		$postType = MWS_PRODUCT_SLUG;
		$url = MWS()->getPermalink_Products($newPermProduct);
		mwshoplog('New product URL prefix: ' . $url, MWLL_INFO, 'permalink');
		$wp_rewrite->add_permastruct($postType, "{$url}/%$postType%", [
			'with_front' => true,
			'ep_mask' => EP_NONE,
			'pages' => false,
			'feeds' => false,
			'forcomments' => false,
			'walk_dirs' => false,
			'endpoints' => false,
		]);

		if ($flush) {
			mwshoplog('Flushing rewrite rules', MWLL_INFO, 'permalink');
			$wp_rewrite->flush_rules();
		}
	}

	public static function removePermalinks($oldShopSlug = '')
	{
		global $wp_rewrite;

		$wp_rewrite->remove_permastruct(MWS_PRODUCT_SLUG);
		$wp_rewrite->remove_permastruct(MWS_PRODUCT_CAT_SLUG);

		/*      if(!empty($oldShopSlug)) {
		// Throw away all rules containing old shop slug name. They are invalid.
		$arr = array_filter($wp_rewrite->extra_rules_top,
		function($item)use($oldShopSlug) {
		return (strpos($item, $oldShopSlug) === false);
		}, ARRAY_FILTER_USE_KEY
		);
		$wp_rewrite->extra_rules_top = $arr;
		$arr = array_filter($wp_rewrite->extra_rules,
		function($item)use($oldShopSlug) {
		return (strpos($item, $oldShopSlug) === false);
		}, ARRAY_FILTER_USE_KEY
		);
		$wp_rewrite->extra_rules = $arr;
		}*/
	}

	public static function extendAdminPage()
	{
		if (MWS()->isCreated()) {
			// Add a section to the permalinks page
			add_settings_section(
				'mws-permalinks',
				__('Nastavení eshopu', 'mwshop'),
				['MwsRewrite', 'renderSettings'],
				'permalink'
			);

			// Add our settings
			add_settings_field(
				'mws_product_stg',
				__('Základní část URL produktu', 'mwshop'),
				['MwsRewrite', 'renderProduct'],
				'permalink',
				'mws-permalinks'
			);
			add_settings_field(
				'mws_product_category_stg',
				__('Základní část URL kategorie produktu', 'mwshop'),
				['MwsRewrite', 'renderProductCategory'],
				'permalink',
				'mws-permalinks'
			);

			static::save();
		}
	}

	private static function save()
	{
		if (!is_admin()) {
			return;
		}

		if (isset($_POST['permalink_structure'])) {
			$permalinks = get_option(MWS_OPTION_PERMALINKS);
			if (!$permalinks || !is_array($permalinks)) {
				$permalinks = [];
			}

			$post = $_POST[MWS_OPTION_PERMALINKS] ?? [];
			$permalinks['permalink_product'] = $post['permalink_product'] ?? null;
			$permalinks['permalink_product_category'] = $post['permalink_product_category'] ?? null;

			update_option(MWS_OPTION_PERMALINKS, $permalinks);
			MWS()->reloadPermalinks();

			mwshoplog(
				'Permalink names modified. New URLs are: '
				. MWS()->getPermalink_Products()
				. ' '
				. MWS()->getPermalink_ProductCat(),
				MWLL_INFO,
				'permalink'
			);
		}
	}

	public static function renderSettings()
	{
		//TODO Add some hints for users?
	}

	public static function renderProduct()
	{
		$field = [
			'title' => __('URL pro produky', 'mwshop'),
			'id' => 'permalink_product',
			'type' => 'permalink',
			'desc' => __('Zadáním hodnoty můžete změnit URL, pod kterou budou dostupné vaše produkty. Výchozí hodnota je ' .
				'"produkty", kdy produkty jsou pak dostupné na URL "www.mujweb.cz/produkty/mujprodukt".', 'mwshop'),
			'placeholder' => MWS_PERMALINK_PRODUCT_DEFAULT,
		];

		$permalinks = get_option(MWS_OPTION_PERMALINKS);
		$val = $permalinks['permalink_product'] ?? [];
		field_type_permalink($field, $val, MWS_OPTION_PERMALINKS, MWS_OPTION_SHOP_SETTING);
		if (!empty($field['desc'])) {
			echo '<span class="mw_description">' . $field['desc'] . '</span>';
		}
	}

	public static function renderProductCategory()
	{
		$field = [
			'title' => __('URL pro kategorie produktů', 'mwshop'),
			'id' => 'permalink_product_category',
			'type' => 'permalink',
			'desc' => __('Zadáním hodnoty můžete změnit URL, pod kterou budou dostupné vaše kategorie produktů. ' .
				'Výchozí hodnota je "kategorie-produktu", kdy kategorie jsou pak dostupné na URL ' .
				'"www.mujweb.cz/kategorie-produktu/mojekategorie".', 'mwshop'),
			'placeholder' => MWS_PERMALINK_PRODUCT_CAT_DEFAULT,
			'nested_text' => __('Zanořit pod URL produktu', 'mwshop'),
			'nested_parent_permalink' => 'permalink_product',
			'nested_placeholder' => MWS_PERMALINK_PRODUCT_CAT_NESTED_DEFAULT,
		];

		$permalinks = get_option(MWS_OPTION_PERMALINKS);
		$val = $permalinks['permalink_product_category'] ?? [];

		field_type_permalink($field, $val, MWS_OPTION_PERMALINKS, MWS_OPTION_SHOP_SETTING);
		if (!empty($field['desc'])) {
			echo '<span class="mw_description">' . $field['desc'] . '</span>';
		}
	}
}
