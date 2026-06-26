<?php
class mwHelp
{

	public function __construct()
	{
	}

	public static function getHelpLink($id)
	{
		$link_array = [
			'block' => MW_HELP_URL . 'article/860-prace-s-radky',

			've_header' => MW_HELP_URL . 'article/678-hlavicka-webu',
			've_footer' => MW_HELP_URL . 'article/679-paticka-webu',
			've_appearance' => MW_HELP_URL . 'article/681-pozadi-a-formatovani-na-webu',

			'page_set' => MW_HELP_URL . 'article/684-zakladni-nastaveni-stranky',
			'page_seo' => MW_HELP_URL . 'article/685-seo-nastaveni-stranky',
			'page_facebook' => MW_HELP_URL . 'article/686-facebook-atributy-stranky',
			'mw_page_codes' => MW_HELP_URL . 'article/689-nastaveni-kodu-platnych-pouze-pro-stranku',
			'page_redirect' => MW_HELP_URL . 'article/690-presmerovani-stranky',
			'page_statistics' => MW_HELP_URL . 'article/688-a-b-testovani-dvou-verzi-stranky',
			've_popup' => MW_HELP_URL . 'article/687-pop-upy-vyskakovaci-okna-na-strance',
			'page_member' => MW_HELP_URL . 'article/717-nastaveni-clenske-stranky',
			'member_sections' => MW_HELP_URL . 'article/254-co-je-clenska-sekce-na-webu',

			'web_option_basic' => MW_HELP_URL . 'article/693-zakladni-nastaveni-webu',
			'web_option_codes' => MW_HELP_URL . 'article/1010-vlastni-kody-pro-web',
			'web_option_license' => MW_HELP_URL . 'article/693-zakladni-nastaveni-webu',
			'web_option_smtp' => MW_HELP_URL . 'article/1080-nastaveni-smtp',
			'web_option_others' => MW_HELP_URL . 'article/412-jak-nastavit-cookie-listu-na-webu',
			'seo_basic' => MW_HELP_URL . 'article/697-seo-nastaveni-webu',
			'web_option_affiliate' => MW_HELP_URL . 'article/704-affiliate-nastaveni-provizniho-systemu',
			'web_option_gdpr' => MW_HELP_URL . 'article/705-gdpr-v-miowebu',
			'mw_event' => MW_HELP_URL . 'article/228-element-kalendar-akci',
			'mw_event_category' => MW_HELP_URL . 'article/228-element-kalendar-akci',
			'mw_web_template' => MW_HELP_URL . 'article/275-sablony-v-miowebu-a-jak-s-nimi-pracovat',
			'social_option' => MW_HELP_URL . 'article/696-nastaveni-zobrazovani-webu-na-socialnich-sitich',
			've_popups' => MW_HELP_URL . 'article/936-jak-vytvaret-pop-upy-vyskakovaci-okna',

			'mw_custom_fonts' => MW_HELP_URL . 'article/694-jak-vlozit-vlastni-google-font-do-miowebu',
			'web_template' => MW_HELP_URL . 'article/935-sablony-v-miowebu-a-jak-s-nimi-pracovat',
			'import_export_web' => MW_HELP_URL . 'article/937-import-export-webu',

			've_connect_se' => MW_HELP_URL . 'article/838-propojeni-se-smartemailingem-nastrojem-na-email-marketing',
			've_connect_fapi' => MW_HELP_URL . 'article/839-propojeni-s-fapi-nastrojem-na-online-prodej-a-fakturaci',
			've_google_api' => MW_HELP_URL . 'article/820-element-mapa',

			'blog_comments' => MW_HELP_URL . 'article/827-zakladni-nastaveni-blogu',
			'blog_sidebars' => MW_HELP_URL . 'article/928-sidebary-a-widgety-na-blogu',
			'blog_popups' => MW_HELP_URL . 'article/936-jak-vytvaret-pop-upy-vyskakovaci-okna',
			//'mw_blog_seo'=>MW_HELP_URL.'category/920-seo-optimalizace-pro-vyhledavace',
			'blog_facebook' => MW_HELP_URL . 'article/686-facebook-atributy-stranky',
			'category' => MW_HELP_URL . 'article/59-kategorie-rubriky-na-blogu',
			'post_tag' => MW_HELP_URL . 'article/63-pouziti-tagu-v-clancich-na-blogu',

			'blog_appearance' => MW_HELP_URL . 'article/828-nastaveni-vzhledu-blogu',
			'blog_header' => MW_HELP_URL . 'article/1012-hlavicka-blogu',
			'blog_footer' => MW_HELP_URL . 'article/1019-paticka-blogu',

			'member_option' => MW_HELP_URL . 'article/710-nastaveni-clenske-sekce',
			'appearancemember_option' => MW_HELP_URL . 'article/711-jak-nastavit-vzhled-clenske-sekce',

			'mw_eshop_setting' => MW_HELP_URL . 'article/941-zakladni-nastaveni-eshopu',
			'eshop_popups' => MW_HELP_URL . 'article/936-jak-vytvaret-pop-upy-vyskakovaci-okna',
			'eshop_comparers' => MW_HELP_URL . 'article/945-napojeni-na-srovnavace-cen',
			'mw_eshop_codes' => MW_HELP_URL . 'article/1011-vlastni-kody-v-eshopu',

			//'eshop_appearance'=>MW_HELP_URL.'article/941-zakladni-nastaveni-eshopu',
			'eshop_header' => MW_HELP_URL . 'article/678-hlavicka-webu',
			'eshop_footer' => MW_HELP_URL . 'article/679-paticka-webu',

			've_buttons' => MW_HELP_URL . 'article/1014-tlacitka-pro-cely-web-3-0',
			'campaign_basic' => MW_HELP_URL . 'article/736-navod-na-vytvoreni-kampane-na-webu-30',
		];

		return $link_array[$id] ?? '';
	}

}
