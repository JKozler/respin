<?php

/**
 * Definition of custom post types, taxonomies and properties.
 *
 * Date: 04.02.16
 * Time: 15:07
 *
 * @since 1.0.0
 */

use Mioweb\Shop\mwSettingObjectService_Upsell;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Upsell;

/** Proceed with registration of post types, taxonomies etc. */
class MwsTypesRegistration
{

	public static function initClass()
	{
		// nothing to do
	}

	public static function registerAll()
	{
		static::registerPostTypes();
		static::registerCurrenciesAndCountries();
	}

	public static function registerCurrenciesAndCountries()
	{
		if (post_type_exists(MWS_CURRENCY_SLUG)) {
			return;
		}

		$mwArgs = [
			'service_class' => 'mwSettingObjectService_Currencies',
			'class' => 'MwsCurrency',
			'allow_add' => true,
			'public' => false,
			'labels' => [
				'title' => __('Měny', 'cms_member'),
				'add_item' => __('Přidat měnu', 'cms_member'),
				'edit_item' => __('Upravit měnu', 'cms_member'),
				'new_item' => __('Nová měna', 'cms_member'),
				'delete' => __('Smazat měnu', 'cms_member'),
				'empty' => __('Nebyla nalezena žádná měna', 'cms_member'),
				'notfound' => __('Měna nebyla nalezena', 'cms'),
			],

		];
		mwSetting()->registerObject(MWS_CURRENCY_SLUG, $mwArgs);

		$mwArgs = [
			'service_class' => 'mwSettingObjectService_ShippingCountries',
			'class' => 'MwsShippingCountry',
			'object_type' => 'shipping_country',
			'allow_add' => true,
			'fast_add' => true,
			'detail' => false,
			'public' => false,
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Země doručení', 'cms_member'),
				'add_item' => __('Přidat zemi', 'cms_member'),
				'edit_item' => __('Upravit zemi', 'cms_member'),
				'new_item' => __('Nová země', 'cms_member'),
				'delete' => __('Smazat zemi', 'cms_member'),
				'empty' => __('Nebyla nalezena žádná země', 'cms_member'),
				'notfound' => __('Země nebyla nalezena', 'cms'),
			],

		];
		mwSetting()->registerObject(MWS_SHIPPING_COUNTRY_SLUG, $mwArgs);
	}

	public static function registerForEshop()
	{
		if (post_type_exists(MWS_PRODUCT_CAT_SLUG)) {
			return;
		}

		// -------------- PRODUCT CATEGORIES --------------

		$labels = [
			'name' => _x('Kategorie eshopu', 'taxonomy general name', 'mwshop'),
			'singular_name' => _x('Kategorie eshopu', 'taxonomy singular name', 'mwshop'),
			'search_items' => __('Hledat kategorie', 'mwshop'),
			'all_items' => __('Všechny kategorie', 'mwshop'),
			'parent_item' => __('Nadřazená kategorie', 'mwshop'),
			'parent_item_colon' => __('Nadřazená kategorie:', 'mwshop'),
			'edit_item' => __('Upravit kategorii', 'mwshop'),
			'update_item' => __('Uložit kategorii', 'mwshop'),
			'add_new_item' => __('Přidat kategorii', 'mwshop'),
			'new_item_name' => __('Jméno nové kategorie', 'mwshop'),
			'menu_name' => __('Kategorie', 'mwshop'),
		];

		$wp_args = [
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => [
				'slug' => MWS()->getPermalink_ProductCat(),
				'with_front' => true,
				'ep_mask' => EP_NONE,
				'pages' => false,
				'feeds' => false,
				'forcomments' => false,
				'walk_dirs' => false,
				'endpoints' => false,
			],
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_Taxonomy',
			'class' => 'mwTerm',
			'allow_add' => true,
			'hierarchical' => true,
			'sortable' => true,
			'supports' => ['visibility','thumbnail','visualeditor'],
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Kategorie', 'cms'),
				'add_item' => __('Přidat kategorii', 'cms'),
				'edit_item' => __('Upravit kategorii', 'cms'),
				'new_item' => __('Nová kategorie', 'cms'),
				'empty' => __('Nebyla nalezena žádná kategorie', 'cms'),
				'notfound' => __('Kategorie nebyla nalezena', 'cms'),
			],

		];

		mwSetting()->registerTaxonomy(MWS_PRODUCT_CAT_SLUG, MWS_PRODUCT_SLUG, $mw_args, $wp_args);

		// -------------- PRODUCT CATEGORIES --------------

		$labels = [
			'name' => _x('Štítky produktů', 'taxonomy general name', 'mwshop'),
			'singular_name' => _x('Štítek produktu', 'taxonomy singular name', 'mwshop'),
			'search_items' => __('Hledat štítek', 'mwshop'),
			'all_items' => __('Všechny štítky', 'mwshop'),
			'edit_item' => __('Upravit štítek', 'mwshop'),
			'update_item' => __('Uložit štítek', 'mwshop'),
			'add_new_item' => __('Přidat štítek', 'mwshop'),
			'new_item_name' => __('Jméno nového štítku', 'mwshop'),
			'menu_name' => __('Štítky', 'mwshop'),
		];

		$wp_args = [
			'hierarchical' => false,
			'labels' => $labels,
			'show_ui' => false,
			'public' => false,
			'query_var' => true,
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_ProductTag',
			'class' => 'MwsTag',
			'allow_add' => true,
			'hierarchical' => false,
			'public' => false,
			'supports' => ['visibility'],
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Štítky', 'cms'),
				'add_item' => __('Přidat štítek', 'cms'),
				'edit_item' => __('Upravit štítek', 'cms'),
				'new_item' => __('Nový štítek', 'cms'),
				'empty' => __('Nebyl nalezen žádný štítek', 'cms'),
				'notfound' => __('Štítek nebyl nalezen', 'cms'),
			],

		];

		mwSetting()->registerTaxonomy(MWS_PRODUCT_TAG_SLUG, MWS_PRODUCT_SLUG, $mw_args, $wp_args);

		// -------------- PRODUCT PROPERTIES --------------
		$labels = [
			'name' => _x('Parametry produktu', 'post type general name for product', 'mwshop'),
			'singular_name' => _x('Parametr produktu', 'post type singular name for product', 'mwshop'),
			'menu_name' => _x('Parametry produktu', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Parametr produktu', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit nový', 'create new product', 'mwshop'),
			'add_new_item' => __('Vytvořit nový parametr', 'mwshop'),
			'new_item' => __('Nový parametr produktu', 'mwshop'),
			'edit_item' => __('Upravit parametr', 'mwshop'),
			'view_item' => __('Zobrazit parametr', 'mwshop'),
			'all_items' => __('Parametry produktu', 'mwshop'),
			'search_items' => __('Vyhledat parametry', 'mwshop'),
			'not_found' => __('Žádné parametry nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné parametry nenalezeny v koši.', 'mwshop'),
		];

		$wp_args = [
			'labels' => $labels,
			'description' => __('Parametry pro produkty Mioweb eshopu.', 'mwshop'),
			'taxonomies' => [],
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'supports' => ['title'],
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_ProductProperty',
			'class' => 'MwsProperty',
			'allow_add' => true,
			'public' => false,
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Parametry produktů', 'mwshop'),
				'add_item' => __('Přidat parametr', 'mwshop'),
				'edit_item' => __('Upravit parametr', 'mwshop'),
				'new_item' => __('Nový parametr', 'mwshop'),
				'delete' => __('Smazat parametr', 'mwshop'),
				'empty' => __('Nebyl nalezen žádný parametr produktu', 'mwshop'),
				'notfound' => __('Parametr produktu nebyl nalezen', 'cms'),
			],

		];

		mwSetting()->registerPostType(MWS_PROPERTY_SLUG, $mw_args, $wp_args);
	}

	/** Register all post types. */
	public static function registerPostTypes()
	{
		if (post_type_exists(MWS_PRODUCT_SLUG)) {
			return;
		}

		if (MWS()->isCreated()) {
			static::registerForEshop();
		}

		// -------------- PRODUCT --------------
		$labels = [
			'name' => _x('Produkty', 'post type general name for product', 'mwshop'),
			'singular_name' => _x('Produkt', 'post type singular name for product', 'mwshop'),
			'menu_name' => _x('Produkty', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Produkt', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit nový', 'create new product', 'mwshop'),
			'add_new_item' => __('Vytvořit nový produkt', 'mwshop'),
			'new_item' => __('Nový produkt', 'mwshop'),
			'edit_item' => __('Upravit produkt', 'mwshop'),
			'view_item' => __('Zobrazit produkt', 'mwshop'),
			'all_items' => __('Produkty', 'mwshop'),
			'search_items' => __('Vyhledat produkty', 'mwshop'),
			'parent_item_colon' => __('Nadřazené zboží:', 'mwshop'),
			'not_found' => __('Žádné produkty nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné produkty nenalezeny v koši.', 'mwshop'),
		];

		$taxonomies = MWS()->isCreated() ? [MWS_PRODUCT_CAT_SLUG, MWS_PRODUCT_TAG_SLUG] : [];

		$wp_args = [
			'labels' => $labels,
			'description' => __('Produkt Mioweb obchodu.', 'mwshop'),
			'taxonomies' => $taxonomies,
			'public' => MWS()->isCreated(),
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => 'mioweb',
			'query_var' => true,
			'rewrite' => [
				'slug' => MWS()->getPermalink_Products(),
				'with_front' => true,
				'ep_mask' => EP_PERMALINK | EP_COMMENTS,
				'pages' => false,
				'feeds' => false,
				'forcomments' => false,
				'walk_dirs' => false,
				'endpoints' => false,
			],
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 20,
			'menu_icon' => 'dashicons-tag',
			'supports' => ['title', 'thumbnail', 'page-attributes'/*, 'revisions'*/],
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_Product',
			'class' => 'MwsProduct',
			'allow_add' => true,
			'public' => MWS()->isCreated(),
			'supports' => ['thumbnail', 'search', 'duplicate'],
			'taxonomies' => $taxonomies,
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Produkty', 'mwshop'),
				'add_item' => __('Přidat produkt', 'mwshop'),
				'edit_item' => __('Upravit produkt', 'mwshop'),
				'new_item' => __('Nový produkt', 'mwshop'),
				'delete' => __('Smazat produkt', 'mwshop'),
				'empty' => __('Nebyl nalezen žádný produkt', 'mwshop'),
				'notfound' => __('Produkt nebyl nalezen', 'cms'),
			],

		];

		if (MWS()->isCreated()) {
			$mw_args['supports'][] = 'visualeditor';
			$mw_args['supports'][] = 'comments';
			$mw_args['supports'][] = 'visibility';

			$wp_args['supports'][] = 'comments';
		}

		mwSetting()->registerPostType(MWS_PRODUCT_SLUG, $mw_args, $wp_args);


		// -------------- PRODUCT VARIANT --------------
		$labels = [
			'name' => _x('Varianta produktu', 'post type general name for variant of product', 'mwshop'),
			'singular_name' => _x('Varianta produktu', 'post type singular name for variant of product', 'mwshop'),
			'menu_name' => _x('Varianty produktů', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Varianta produktu', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit novou', 'create new product', 'mwshop'),
			'add_new_item' => __('Vytvořit novou variantu produktu', 'mwshop'),
			'new_item' => __('Nová varianta produktu', 'mwshop'),
			'edit_item' => __('Upravit variantu produktu', 'mwshop'),
			'view_item' => __('Zobrazit variantu produktu', 'mwshop'),
			'all_items' => __('Všechny varianty produktů', 'mwshop'),
			'search_items' => __('Vyhledat variantu produktu', 'mwshop'),
			'parent_item_colon' => __('Nadřazený produkt:', 'mwshop'),
			'not_found' => __('Žádné varianty produktů nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné varianty produktů nenalezeny v koši.', 'mwshop'),
		];

		$args = [
			'labels' => $labels,
			'description' => __('Varianta produktu Mioweb obchodu.', 'mwshop'),
			'taxonomies' => [],
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => false,
			'rewrite' => false
			/*              array(
								'slug'=> MWS()->getPermalink_Products(),
								'with_front' => true,
								'ep_mask' => EP_NONE,
								'pages' => false,
								'feeds' => false,
								'forcomments' => false,
								'walk_dirs' => false,
								'endpoints' => false,
							)*/,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 21,
			'supports' => ['title',
				'thumbnail',
				'comments', 'page-attributes'],
		];

		register_post_type(MWS_VARIANT_SLUG, $args);

		// -------------- SALE FORM --------------

		if (MWS()->getSelectedGatewayId() == 'mioweb') {
			$wp_args = [
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => false,
				'query_var' => true,
				'capability_type' => 'post',
				'has_archive' => false,
				'hierarchical' => false,
				'supports' => ['title'],
			];

			$mw_args = [
				'service_class' => 'mwSettingObjectService_SaleForm',
				'class' => 'MwsForm',
				'allow_add' => true,
				'public' => false,
				'supports' => ['search','duplicate'],
				'labels' => [
					'title' => __('Prodejní formuláře', 'mwshop'),
					'add_item' => __('Přidat formulář', 'mwshop'),
					'edit_item' => __('Upravit formulář', 'mwshop'),
					'new_item' => __('Nový formulář', 'mwshop'),
					'delete' => __('Smazat formulář', 'mwshop'),
					'empty' => __('Nebyl nalezen žádný prodejní formulář', 'mwshop'),
					'notfound' => __('Prodejní formulář nebyl nalezen', 'mwshop'),
				],

			];

			mwSetting()->registerPostType(MWS_FORM_SLUG, $mw_args, $wp_args);
		}

		// -------------- UPSELLS --------------

		if (MWS()->getSelectedGatewayId() === 'mioweb') {
			$wp_args = [
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => false,
				'query_var' => true,
				'rewrite' => ['slug' => 'mwupsell'],
				'capability_type' => 'page',
				'has_archive' => false,
				'hierarchical' => false,
				'supports' => ['title', 'revisions'],
			];

			$mw_args = [
				'service_class' => mwSettingObjectService_Upsell::class,
				'class' => Upsell::class,
				'allow_add' => true,
				'public' => true,
				'supports' => ['visualeditor'],
				'labels' => [],
			];

			mwSetting()->registerPostType(MWS_UPSELL_SLUG, $mw_args, $wp_args);
		}

		// -------------- ORDER --------------
//		$labels = [
//			'name' => _x('Objednávky', 'post type general name for product', 'mwshop'),
//			'singular_name' => _x('Objednávka', 'post type singular name for product', 'mwshop'),
//			'menu_name' => _x('Objednávky', 'admin menu', 'mwshop'),
//			'name_admin_bar' => _x('Objednávka', 'add new on admin bar', 'mwshop'),
//			'add_new' => _x('Vytvořit objednávku', 'create new product', 'mwshop'),
//			'add_new_item' => __('Vytvořit novou objednávku', 'mwshop'),
//			'new_item' => __('Nová objednávka', 'mwshop'),
//			'edit_item' => __('Detail objednávky', 'mwshop'),
//			'view_item' => __('Zobrazit objednávku', 'mwshop'),
//			'all_items' => __('Objednávky', 'mwshop'),
//			'search_items' => __('Vyhledat objednávku', 'mwshop'),
//			'parent_item_colon' => __('Nadřazená objednávka:', 'mwshop'),
//			'not_found' => __('Žádné objednávky nenalezeny.', 'mwshop'),
//			'not_found_in_trash' => __('Žádné objednávky nenalezeny v koši.', 'mwshop'),
//		];
//
//		$wp_args = [
//			'labels' => $labels,
//			'description' => __('Objednávka Mioweb obchodu.', 'mwshop'),
//			'public' => true,
//			'publicly_queryable' => true,
//			'show_ui' => false,
//			'query_var' => true,
//			//'rewrite' => false,
//			'capability_type' => 'post',
//			'has_archive' => true,
//			'hierarchical' => false,
//			'menu_position' => 1,
//			'capabilities' => [
//				'create_posts' => 'do_not_allow',
//			],
//			'map_meta_cap' => true,
//			'supports' => ['title'],
//		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_Order',
			'class' => Order::class,
			'allow_add' => false,
			'supports' => ['visibility','search','export','archives'],
			'bulk_actions' => [
				/*
				[
					'action' => 'export',
					'title' => __('Exportovat', 'cms'),
				], */
				[
					'action' => 'createArchive',
					'title' => __('Archivovat', 'mwshop'),
				],
				[
					'action' => 'delete',
				],

			],
			'filter' => [
				[
					'id' => 'show',
					'content' => '',
					'title' => __('Stav', 'mwshop'),
					'items' => [
						'' => __('Všechny objednávky', 'mwshop'),
						MwsOrderStatus::Ordered . ',' . MwsOrderStatus::Processing => __('Všechny k vyřízení', 'mwshop'),
						MwsOrderStatus::Closed => __('Vyřízená', 'mwshop'),
						MwsOrderStatus::Processing => __('Vyřizuje se', 'mwshop'),
						MwsOrderStatus::Ordered => __('Nevyřízená', 'mwshop'),
						MwsOrderStatus::Cancelled => __('Stornovaná', 'mwshop'),
					],
				],
			],
			'labels' => [
				'title' => __('Objednávky', 'mwshop'),
				'edit_item' => __('Objednávka', 'mwshop'),
				'delete' => __('Smazat objednávku', 'cms'),
				'empty' => __('Nebyla nalezena žádná objednávka', 'mwshop'),
				'export_title' => __('Exportovat objednávky', 'cms'),
				'notfound' => __('Objednávka nebyla nalezena', 'mwshop'),
				'archives' => __('Archiv', 'mwshop'),
			],

		];

		mwSetting()->registerObject(MWS_ORDER_SLUG, $mw_args);

		// @TODO configure
		register_post_type(MWS_DOCUMENT_SLUG, [
			'public' => true,
			'show_ui' => false,
			'show_in_menu' => false,
		]);
		// @TODO configure
		register_post_type(MWS_PAYMENT_SLUG, [
			'public' => true,
			'show_ui' => false,
			'show_in_menu' => false,
		]);

		// -------------- SHIPPING --------------
		$labels = [
			'name' => _x('Doručování', 'post type general name for shipping', 'mwshop'),
			'singular_name' => _x('Doručování', 'post type singular name for shipping', 'mwshop'),
			'menu_name' => _x('Doručování', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Doručování', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit nové', 'create new shipping', 'mwshop'),
			'add_new_item' => __('Vytvořit nový způsob doručení', 'mwshop'),
			'new_item' => __('Nové doručování', 'mwshop'),
			'edit_item' => __('Upravit doručování', 'mwshop'),
			'view_item' => __('Zobrazit doručování', 'mwshop'),
			'all_items' => __('Způsoby doručení', 'mwshop'),
			'search_items' => __('Vyhledat doručování', 'mwshop'),
			'parent_item_colon' => __('Nadřazené doručování:', 'mwshop'),
			'not_found' => __('Žádné způsoby doručení nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné způsoby doručení nenalezeny v koši.', 'mwshop'),
		];

		$wp_args = [
			'labels' => $labels,
			'description' => __('Způsob doručování Mioweb obchodu.', 'mwshop'),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 2,
			'supports' => ['title'],
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_Shipping',
			'class' => 'MwsShipping',
			'allow_add' => true,
			'public' => false,
			'supports' => ['visibility'],
			'labels' => [
				'title' => __('Způsoby doručení', 'mwshop'),
				'add_item' => __('Přidat způsob doručení', 'mwshop'),
				'edit_item' => __('Upravit způsob doručení', 'mwshop'),
				'new_item' => __('Nový způsob doručení', 'mwshop'),
				'delete' => __('Smazat způsob doručení', 'mwshop'),
				'empty' => __('Nebyla nalezen žádný způsob doručení', 'mwshop'),
				'notfound' => __('Způsob doručení nebyl nalezen', 'cms'),
			],

		];

		mwSetting()->registerPostType(MWS_SHIPPING_SLUG, $mw_args, $wp_args);

		// payment methods

		$labels = [
			'name' => _x('Způsoby platby', 'post type general name for payment method', 'mwshop'),
			'singular_name' => _x('Způsob platby', 'post type singular name for payment method', 'mwshop'),
			'menu_name' => _x('Způsob platby', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Způsob platby', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit nový', 'create new shipping', 'mwshop'),
			'add_new_item' => __('Vytvořit nový způsob platby', 'mwshop'),
			'new_item' => __('Nový způsob platby', 'mwshop'),
			'edit_item' => __('Upravit způsob platby', 'mwshop'),
			'view_item' => __('Zobrazit způsob platby', 'mwshop'),
			'all_items' => __('Způsoby platby', 'mwshop'),
			'search_items' => __('Vyhledat způsoby platby', 'mwshop'),
			'parent_item_colon' => __('Nadřazené způsob platby:', 'mwshop'),
			'not_found' => __('Žádné způsoby platby nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné způsoby platby nenalezeny v koši.', 'mwshop'),
		];

		$wp_args = [
			'labels' => $labels,
			'description' => __('Způsob platby Mioweb obchodu.', 'mwshop'),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 2,
			'supports' => ['title'],
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_Payment',
			'class' => 'MwsPaymentMethod',
			'allow_add' => true,
			'public' => false,
			'supports' => ['visibility'],
			'labels' => [
				'title' => __('Způsoby platby', 'mwshop'),
				'add_item' => __('Přidat způsob platby', 'mwshop'),
				'edit_item' => __('Upravit způsob platby', 'mwshop'),
				'new_item' => __('Nový způsob platby', 'mwshop'),
				'delete' => __('Smazat způsob platby', 'mwshop'),
				'empty' => __('Nebyla nalezen žádný způsob platby', 'mwshop'),
				'notfound' => __('Způsob platby nebyl nalezen', 'cms'),
			],

		];

		mwSetting()->registerPostType(MWS_PAYMENT_METHOD_SLUG, $mw_args, $wp_args);

		// -------------- DISCOUNT CODES --------------
		$labels = [
			'name' => _x('Slevový kód', 'post type general name for discount code', 'mwshop'),
			'singular_name' => _x('Slevový kód', 'post type singular name for discount code', 'mwshop'),
			'menu_name' => _x('Slevový kód', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Slevový kód', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit nový', 'create new discount code', 'mwshop'),
			'add_new_item' => __('Vytvořit nový slevový kód', 'mwshop'),
			'new_item' => __('Nový slevový kód', 'mwshop'),
			'edit_item' => __('Upravit slevové kódy', 'mwshop'),
			'view_item' => __('Zobrazit slevové kódy', 'mwshop'),
			'all_items' => __('Slevové kódy', 'mwshop'),
			'search_items' => __('Vyhledat slevový kód', 'mwshop'),
			'parent_item_colon' => __('Nadřazený slevový kód:', 'mwshop'),
			'not_found' => __('Žádné slevové kódy nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné slevové kódy nenalezeny v koši.', 'mwshop'),
		];

		$wp_args = [
			'labels' => $labels,
			'description' => __('Slevové kódy Mioweb obchodu.', 'mwshop'),
			'taxonomies' => [MWS_DISCOUNT_CODE_SLUG],
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 2,
			'supports' => [],
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_DiscountCode',
			'class' => 'MwsDiscountCode',
			'allow_add' => true,
			'public' => false,
			'supports' => ['visibility','duplicate'],
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Slevové kódy', 'mwshop'),
				'add_item' => __('Přidat slevový kód', 'mwshop'),
				'edit_item' => __('Upravit slevový kód', 'mwshop'),
				'new_item' => __('Nový slevový kód', 'mwshop'),
				'delete' => __('Smazat slevový kód', 'mwshop'),
				'empty' => __('Nebyl nalezen žádný slevový kód', 'mwshop'),
				'notfound' => __('Slevový kód nebyl nalezen', 'cms'),
			],

		];

		mwSetting()->registerPostType(MWS_DISCOUNT_CODE_SLUG, $mw_args, $wp_args);
	}

}

MwsTypesRegistration::initClass();

class mwSettingObjectService_Product extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Produkt', 'cms'),
				],
				[
					'content' => !$trash && MWS()->isCreated() ? __('Viditelnost', 'cms') : '',
					'align' => 'center',
				],
				[
					'content' => __('Sklad', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Cena', 'cms'),
					'align' => 'right',
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();

		//$show = $filter['show']?? '';

		$search = $filter['s'] ?? '';

		$query = MwsProduct::getAll([
			'post_status' => $trash ? 'trash' : 'any',
			'posts_per_page' => $perPage,
			'paged' => $page,
			's' => $search,
		], true);

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		$settingActions = MWS()->isCreated() ? ['edit', 'show_page', 'duplicate', 'delete'] : ['edit', 'duplicate', 'delete'];

		foreach ($query['items'] as $product) {
			$args['rows'][] = [
				'bulk_id' => $product->getId(),
				'cols' => [
					[
						'content' => $trash ? $product->getName() : mwAdminComponents::link([
							'text' => $product->getThumbnail()->getImg() . ' <span>' . $product->getName() . '</span>',
							'link' => $this->object()->getEditUrl($product->getId()),
						], 'mw_link mws_product_list_detail_link'),
					],
					[
						'content' => !$trash && MWS()->isCreated() ? mwAdminComponents::checker($product->isVisible(), [
							'attrs' => 'data-id="' . $product->getId() . '" data-objectid="' . $this->object()->getId() . '"',
						], 'mw_checker_visibility') : '',
						'align' => 'center',
					],
					[
						'content' => $product->isStockEnabled() ? $product->getStockCount() : '&infin;',
						'align' => 'center',
					],
					[
						'content' => $product->htmlPriceSaleFull('mws_product_list_price', 1, ['salePercentage', 'vatExcluded', 'saleDuration', 'discount']),
						'align' => 'right',
					],
					[
						'content' => mwSetting::printSettingActions($settingActions, $product->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function isItemEditNotAllowed($item): bool
	{
		return $item->isVariant();
	}

	public function itemIsNotEditedMessage($item)
	{
		if ($item->isVariant()) {
			echo mwSetting::message404(__('Variantu produktu nelze samostatně editovat.', 'cms'), '<a href="' . $this->object()->getEditUrl($item->getParentId()) . '">' . __('Editovat rodičovský produkt', 'cms') . '</a>');
		}
	}

	function printFormSidebar($item, $add = false, $inPopup = false): string
	{
		$content = '<div class="mw_setting_object_detail_sidebar">';

		if ($this->object()->isSupported('visibility') || !$add) {
			$content .= '<div class="mw_setting_sidebar_box">';

			if ($this->object()->isSupported('visibility')) {
				$visibility = $this->getVisibility($item);
				$options = [
					'publish' => [
						'text' => __('Veřejné', 'cms'),
						'status' => 'ok',
						'icon' => 'check',
					],
					'private' => [
						'text' => __('Soukromé', 'cms'),
						'status' => 'x',
						'icon' => 'eye-off',
					],
				];

				if ($this->object()->isSupported('password_protected')) {
					$options['password_protected'] = [
						'text' => __('Chráněné heslem', 'cms'),
						'status' => 'processing',
						'icon' => 'lock',
					];
				}

				$content .= mwAdminComponents::statusSelect([
					'title' => __('Viditelnost', 'cms'),
					'show_list' => true,
					'input' => 'visibility',
					'list' => $options,
				], $visibility, 'mw_setting_sidebar_visibility');

				if ($this->object()->isSupported('password_protected')) {
					$content .= '<div class="mw_setting_password_protected_container ' . ($visibility != 'password_protected' ? 'cms_nodisp' : '') . '">';
					$content .= mwAdminComponents::input([
						'name' => 'post_password',
						'placeholder' => __('Zadejte heslo', 'cms'),
					], $item->getPassword(), 'mw_setting_password_protected_input');
					$content .= '</div>';
				}
			}

			if (MWS()->isCreated()) {
				$hideVal = $item ? ($item->hideInListings() ? 1 : 0) : 0;
				$content .= '<div class="mws_hide_in_listings_container mw_onedit_action" data-type="switch">';
				$content .= mwAdminComponents::switch([
					'name' => 'product[hide_in_listings]',
					'switch_label' => __('Skrýt z výpisu produktů', 'mwshop'),
				], $hideVal);
				$content .= '</div>';
			}

			if (!$add) {
				$content .= $this->getInfoList($item);

				if (!$inPopup) {
					$content .= $this->getDetailActionList($item);
				}
			}
			$content .= '</div>';
		}

		$content .= $this->printThumbWidget($item);

		$content .= $this->printStockWidget($item);

		if (MWS()->isCreated()) {
			$content .= $this->printOrderWidget($item);
		}

		$taxonomies = $this->object()->getTaxonomies();
		foreach ($taxonomies as $taxonomy) {
			$content .= $this->printTaxWidget($taxonomy, $item);
		}

		$content .= $this->printCommentsWidget($item);

		$content .= '</div>';

		return $content;
	}

	public function printStockWidget($item): string
	{
		$content = '<div class="mw_setting_sidebar_box">';

		$isStockEnabled = $item ? ($item->isStockEnabled() ? 1 : 0) : 0;

		$content .= '<div class="mw_onedit_action" data-type="product_stock_setting">';
		$content .= mwAdminComponents::switch([
			'name' => 'product[stock_enabled]',
			'switch_label' => '<h4 class="mw_title">' . __('Sledovat sklad', 'mwshop') . '</h4>',
		], $isStockEnabled, 'mws_stock_setting_switch');
		$content .= '</div>';

		$content .= '<div class="mws_stock_setting_container ' . ($isStockEnabled ? '' : 'cms_nodisp') . '">';

		$content .= '<div class="set_form_subrow cms_show_group_product_structure_type cms_show_group_product_structure_type_0 mw_onedit_action" data-type="number">';
		$content .= mwAdminComponents::inputNumber([
			'name' => 'product[' . MWS_OPTION_STOCKCOUNT . ']',
			'unit' => __('ks skladem', 'mwshop'),
			'step' => 1,
			'placeholder' => 0,
		], $item ? $item->getStockCount() : '');
		$content .= '</div>';

		$content .= '<div class="set_form_subrow mw_onedit_action" data-type="switch">';
		$content .= mwAdminComponents::switch([
			'name' => 'product[stock_allow_backorders]',
			'switch_label' => __('Povolit prodej i po vyprodání', 'mwshop'),
		], $item ? ($item->stockAllowBackorders() ? 1 : 0) : 0);
		$content .= '</div>';

		$content .= '</div>'; //mws_stock_setting_container
		$content .= '</div>';

		return $content;
	}

	public function printOrderWidget($item): string
	{
		$content = '<div class="mw_setting_sidebar_box">';

		$content .= mwAdminComponents::title([
			'text' => __('Pořadí', 'mwshop') . mwAdminComponents::tooltip(['text' => __('V případě že máte v nastavení vzhledu a obsahu nastaveno že se má ve výpisech zboží řadit podle vlastního řazení, tak zde můžete nastavit pořadí daného produktu.', 'mwshop'), 'tooltip_align' => 'top']),
		]);

		$content .= '<div class="mw_onedit_action" data-type="text">';
		$content .= mwAdminComponents::input([
			'name' => 'menu_order',
		], $item ? $item->getOrder() : '');
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	public function printTaxList($terms, $itemInTerms, $tax, $class = '')
	{
		$content = '';

		$content .= '<div class="mws_product_tags_field mw_onedit_action" data-type="product_tags">';

		$content .= '<div class="mws_product_tags_list">';
		$list = '';
		foreach ($terms as $term) {
			$tag = MwsTag::printAdminLabel($term);

			$hideInList = false;
			if (in_array($term->getId(), $itemInTerms)) {
				$content .= $tag;
				$hideInList = true;
			}
			$list .= '<li class="mw_input_whisperer_item mw_input_whisperer_item_' . $term->getId() . ' ' . ($hideInList ? 'whisperer_item_used' : '') . '">';
			$list .= '<a href="#" data-text="' . $term->getName() . '"><span style="background-color:' . $term->getColor() . '"></span>' . $term->getName() . '</a>';
			$list .= mwAdminComponents::textarea([
				'name' => '',
			], $tag, 'mws_product_tag_html cms_nodisp');
			$list .= '</li>';
		}
		$content .= '</div>';

		$content .= '<div class="set_form_subrow mw_input_whisperer">';
		$content .= mwAdminComponents::input([
			'name' => '',
			'placeholder' => __('Přidat štítek', 'mwshop'),
		], '', 'mw_input_whisperer_input');
		$content .= '<div class="mw_input_whisperer_list mw_scroll">';
		$content .= '<ul>';
		$content .= $list;
		$content .= '<li class="mw_input_whisperer_add_item">' . mwAdminComponents::iconLink([
				'icon' => 'plus',
				'text' => __('Přidat štítek', 'mwshop'),
				'attrs' => 'data-object="' . $tax->getId() . '" data-title="' . $tax->getLabel('add_item') . '"',
		], 'mw_setting_action_link mw_input_whisperer_add') . '</li>';
		$content .= '</ul>';
		$content .= '</div>';
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	public function checkDataSet($setData, $set, $setId = '', $itemId = 0, bool $add = false): bool
	{
		if ($setId == 'product') {
			if (isset($setData['type']) && $setData['type'] === MwsProductType::Membership && !$setData['membership_setting']) {
				mwMessages()->error(__('Pro tento typ produktu musí být vybráno v jaké členské sekci se má vytvářet členství.', 'mwshop'));

				return false;
			}
			if (isset($setData['product_structure']) && $setData['product_structure'] == MwsProductStructureType::Variants) {
				if (!isset($setData['variant_list']['variants'])) {
					mwMessages()->error(__('Musí být zadaná alespoň jedna varianta produktu.', 'mwshop'));

					return false;
				}
			}
		}

		return true;
	}

	public function beforeSaveMeta($itemId, $tosave): array
	{
		if (isset($tosave['product'][MWS_OPTION_STOCKCOUNT])) {
			$product = MwsProduct::getOneById($itemId, true);

			if (!$product) {
				return $tosave;
			}
			if ($tosave['product']['stock_enabled'] ?? false) {
				$product->updateStockCount((int) $tosave['product'][MWS_OPTION_STOCKCOUNT], MwsStockUpdate::Set, true);
				unset($tosave['product'][MWS_OPTION_STOCKCOUNT]);
			}
		}

		return $tosave;
	}

	public function afterSaveActions($itemId, $tosave)
	{
		$product = MwsProduct::getOneById($itemId, true);

		mwshoplog(sprintf(__('Uložen produkt "%s" [%d].', 'mwshop'), $product->getName(), $product->getId()), MWLL_INFO);
		// Update variants if needed
		$this->updateVariantsOf($product);
	}

	private function updateVariantsOf(MwsProductRoot $product): void
	{
		// Update variants if needed

		if ($product->getStructure() === MwsProductStructureType::Variants) {
			$variantList = $product->getVariantDefinition();
			$preservedVariants = [];

			// Process the table of variants - create and update
			if (isset($variantList['variants']) && is_array($variantList['variants'])
				&& isset($variantList['parametres']) && is_array($variantList['parametres'])
			) {
				$stockEnabled = $product->isStockEnabled();
				$parameters = $variantList['parametres'];
				$order = 0;

				foreach ($variantList['variants'] as $varArrKey => $varDef) {
					$variantId = isset($varDef['variant_id']) ? (int) $varDef['variant_id'] : 0;
					$properties = $varDef['property'] ?? [];
					$price = isset($varDef['price']) ? str_replace(',', '.', $varDef['price']) : 0;
					$priceSale = isset($varDef['price_sale']) ? str_replace(',', '.', $varDef['price_sale']) : null;
					$weight = isset($varDef['weight_variant']) ? str_replace(',', '.', $varDef['weight_variant']) : null;
					$stockCount = $stockEnabled
					? isset($varDef['stock_count']) ? (int) $varDef['stock_count'] : 0
					: false;

					$thumbId = isset($varDef['imageid']) && !empty($varDef['imageid']) ? (int) $varDef['imageid'] : false;

					// Make sure that all requested parameters are set within $properties. This will force to their validation.
					foreach ($parameters as $parameter) {
						$parameter = (int) $parameter;
						if (!isset($properties[$parameter])) {
							$properties[$parameter] = '';
						}
					}

					$codes = $varDef['codes'] ?? [];

					$variant = null;
					try {
						if ($variantId && $variant = MwsProductVariant::getOneById($variantId)) {
							// Existing variant -> update it
							$preservedVariants[] = $variantId;
							try {
								$variant->updateVariant($properties, $price, $priceSale, $stockCount, $codes, $order, $weight);
								mwshoplog(sprintf(__('Varianta produktu "%s" [%d] aktualizována.', 'mwshop'), $variant->getName(), $variant->getId()), MWLL_INFO, 'variant');
							} catch (MwsException $e) {
								mwshoplog(
									sprintf(__('Variantu produktu "%s" se nepodařilo aktualizovat. %s', 'mwshop'), $product->getName(), $e->getMessage()),
									MWLL_ERROR,
									'variant'
								);
							}
						} else {
							// New variant
							try {
								// @TODO create post out?
								$variant = MwsProductVariant::createVariant($product, $properties, $price, $priceSale, $stockCount, $codes, $order, $weight);
								if ($variant) {
									mwshoplog(sprintf(__('Varianta produktu "%s" [%d] vytvořena.', 'mwshop'), $variant->getName(), $variant->getId()), MWLL_INFO, 'variant');
									// Propagate value of created variant back into variant definition list. It wil be saved withing the product fields.
									$variantList['variants'][$varArrKey]['variant_id'] = $variantId = $variant->getId();
									$preservedVariants[] = $variantId;
								} else {
									mwshoplog(sprintf(__('Variantu produktu "%s" se nepodařilo vytvmořit.', 'mwshop'), $product->getName()), MWLL_ERROR, 'variant');
								}
							} catch (MwsException $e) {
								mwshoplog(
									sprintf(__('Variantu produktu "%s" se nepodařilo vytvořit. %s', 'mwshop'), $product->getName(), $e->getMessage()),
									MWLL_ERROR,
									'variant'
								);
							}
						}
					} catch (Exception $e) {
						mwshoplog(
							sprintf(__('Došlo k chybě při zpracování definice varianty "%d" produktu [%d].', 'mwshop'), $varArrKey, $variantId)
							. "\n" . $e->getMessage(),
							MWLL_ERROR,
							'variant'
						);
					}

					// Update thumbnail
					if ($variant) {
						$post = get_post($variant->getId());
						$oldThumbId = get_post_thumbnail_id($post);
						if ($oldThumbId != $thumbId) {
							if ($thumbId) {
								$res = set_post_thumbnail($post, $thumbId);
								if (!$res) {
									mwshoplog(
										sprintf(__('Nepodařilo se aktualizovat náhled varianty "%s".', 'mwshop'), $variant->getName(), $variantId),
										MWLL_WARNING,
										'variant'
									);
								}
							} else {
								delete_post_thumbnail($post);
							}
						}
					}

					$order++;
				}
				//$this->unsetCopyObject();
			}

			// Remove unused variants
			/** @var MwsProductRoot $rootProduct */
			$rootProduct = $product;
			$variants = $rootProduct->getVariants([]); // get all variants including concepts etc.
			/** @var MwsProductVariant $variant */
			foreach ($variants as $variant) {
				$variantId = $variant->getId();
				$preserve = in_array($variantId, $preservedVariants);
				if (!$preserve) {
					//TODO Statistic of updated+added+deleted --> synchronization + errors to UI
					wp_delete_post($variantId);
					mwshoplog(sprintf(__('Varianta produktu "%s" [%d] odebrána.', 'mwshop'), $variant->getName(), $variantId), MWLL_INFO, 'variant');
				}
			}

			// Update field
			mwshoplog('Saving variant list definition for [' . $product->getId() . ']', MWLL_DEBUG, 'save');
			$product->setVariantDefinition($variantList);

			$id = $product->getId();
			$product = MwsProduct::getOneById($id, true);
		}
	}

	public function delete($id, $force_delete = false)
	{
		$product = MwsProduct::getOneById($id, true);

		//if($product && $product->isTrashed())
		//{
			mwshoplog(sprintf(__('Smazán produkt "%s" [%d].', 'mwshop'), $product->getName(), $product->getId()), MWLL_INFO);
			// Delete variants if needed
			if ($product->getStructure() === MwsProductStructureType::Variants) {
			$variants = $product->getVariants([]); // get all variants including concepts etc.
			/** @var MwsProductVariant $variant */
			foreach ($variants as $variant) {
				$variantId = $variant->getId();
				wp_delete_post($variantId, true);
				mwshoplog(sprintf(__('Varianta produktu "%s" [%d] odebrána.', 'mwshop'), $variant->getName(), $variantId), MWLL_INFO, 'variant');
			}
			}
		//}

				// Remove from similar products
		$allProducts = MwsProduct::getAll();
		foreach ($allProducts as $product) {
			$similarProducts = $newSimilarProducts = $product->getSimilarProducts();

			foreach ($similarProducts as $key => $similarProduct) {
				if (isset($similarProduct['product_id']) && (int) $similarProduct['product_id'] === (int) $id) {
					unset($newSimilarProducts[$key]);
				}
			}

			if (count($similarProducts) !== count($newSimilarProducts)) {
				$product->setSimilarProducts($newSimilarProducts);
			}
		}

		wp_delete_post($id, true);
	}

}

class mwSettingObjectService_ProductTag extends mwSettingObjectService_Taxonomy
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => $this->object()->getLabel('singular'),
				],
				[
					'content' => __('Produktů', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Viditelnost', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();
		$terms = MwsTag::getAll($this->object()->getId(), [
			'name__like' => $filter['s'] ?? '',
		]);

		foreach ($terms as $term) {
			$args['rows'][] = [
				'bulk_id' => $term->getId(),
				'cols' => [
					[
						'content' => mwAdminComponents::link([
							'text' => mwAdminComponents::textLabel([
								'text' => $term->getName(),
								'color' => $term->getColor(),
							]),
							'link' => $this->object()->getEditUrl($term->getId()),
						], ''),
					],
					[
						'content' => $term->getCount(),
						'align' => 'center',
					],
					[
						'content' => mwAdminComponents::checker($term->isVisible(), [
							'attrs' => 'data-id="' . $term->getId() . '" data-objectid="' . $this->object()->getId() . '"',
						], 'mw_checker_visibility'),
						'align' => 'center',
					],
					[
						'content' => mwSetting()->printSettingActions(['edit', 'delete'], $term->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}
}

class mwSettingObjectService_ProductProperty extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Vlastnost', 'cms'),
				],
				[
					'content' => __('Jednotka', 'cms'),
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$query = MwsProperty::getAll([
			'post_status' => 'any',
			'posts_per_page' => $perPage,
			'paged' => $page,
		], true);

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		foreach ($query['items'] as $property) {
			$args['rows'][] = [
				'bulk_id' => $property->getId(),
				'cols' => [
					[
						'content' => mwAdminComponents::link([
							'text' => $property->getName(),
							'link' => $this->object()->getEditUrl($property->getId()),
						]),
					],
					[
						'content' => $property->getUnit(),
					],
					[
						'content' => mwSetting::printSettingActions(['edit', 'delete'], $property->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}
	public function checkDataSet($setData, $set, $setId = '', $itemId = 0, bool $add = false): bool
	{
		if ($setId == MWS_PROPERTY_META_KEY && isset($setData['type']) && $setData['type'] == 'enum') {
			if (isset($setData['values']) && count($setData['values'])) {
				foreach ($setData['values'] as $val) {
					if ($val['name'] === '') {
						mwMessages()->error(__('Hodnota nemůže být prázdná. Prosím nastavte ji nebo smažte.', 'mwshop'));

						return false;
					}
				}
			} else {
				mwMessages()->error(__('Musí být zadaná alespoň jedna hodnota.', 'mwshop'));

				return false;
			}
		}

		return true;
	}
}

class mwSettingObjectService_Shipping extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Název', 'cms'),
				],
				[
					'content' => __('Země', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Viditelnost v eshopu', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Cena', 'cms'),
					'align' => 'right',
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();

		$query = MwsShipping::getAll([
			'post_status' => 'any',
		], true);

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		foreach ($query['items'] as $shipping) {
			$price = $shipping->isPriceByWeight() ? __('od', 'mwshop') . ' ' . $shipping->getPriceByWeight(0)->htmlPriceVatIncluded() : $shipping->getPrice()->htmlPriceVatIncluded();

			$args['rows'][] = [
				'cols' => [
					[
						'content' => '<a class="mw_link" href="' . $this->object()->getEditUrl($shipping->getId()) . '">' . $shipping->getName() . '</a>',
					],
					[
						'content' => $shipping->getCountry() ?: '-',
						'align' => 'center',
					],
					[
						'content' => mwAdminComponents::checker($shipping->isVisible(), [
							'attrs' => 'data-id="' . $shipping->getId() . '" data-objectid="' . $this->object()->getId() . '"',
						], 'mw_checker_visibility'),
						'align' => 'center',
					],
					[
						'content' => $price,
						'align' => 'right',
					],
					[
						'content' => mwSetting::printSettingActions(['edit', 'delete'], $shipping->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}
}

class mwSettingObjectService_Payment extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Název', 'cms'),
				],
				[
					'content' => __('Viditelnost v eshopu', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();

		$query = MwsPaymentMethod::getAll([
			'post_status' => 'any',
			'posts_per_page' => $perPage,
			'paged' => $page,
		], true);

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		foreach ($query['items'] as $item) {
			$args['rows'][] = [
				'cols' => [
					[
						'content' => '<a class="mw_link" href="' . $this->object()->getEditUrl($item->getId()) . '">' . $item->getName() . '</a>',
					],
					[
						'content' => mwAdminComponents::checker($item->isVisible(), [
							'attrs' => 'data-id="' . $item->getId() . '" data-objectid="' . $this->object()->getId() . '"',
						], 'mw_checker_visibility'),
						'align' => 'center',
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
}

class mwSettingObjectService_DiscountCode extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Slevový kód', 'cms'),
				],
				[
					'content' => __('Uplatnění', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Aktivní', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Výše slevy', 'cms'),
					'align' => 'right',
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$query = MwsDiscountCode::getAllForSetting($perPage, $page);

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		foreach ($query['items'] as $item) {
			$args['rows'][] = [
				'bulk_id' => $item->getId(),
				'cols' => [
					[
						'content' => '<a class="mw_link" href="' . $this->object()->getEditUrl($item->getId()) . '">' . $item->getCode() . '</a>',
					],
					[
						'content' => $item->getUsedCount() . ($item->getMaxCount() ? '/' . $item->getMaxCount() : ''),
						'align' => 'center',
					],
					[
						'content' => mwAdminComponents::checker($item->isVisible(), [
							'attrs' => 'data-id="' . $item->getId() . '" data-objectid="' . $this->object()->getId() . '"',
						], 'mw_checker_visibility'),
						'align' => 'center',
					],
					[
						'content' => $item->printValue(),
						'align' => 'right',
					],
					[
						'content' => mwSetting::printSettingActions(['edit', 'duplicate', 'delete'], $item->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function checkDataSet($setData, $set, $setId = '', $itemId = 0, bool $add = false): bool
	{
		if ($setId == 'discount_code') {
			// check code
			if (preg_match('/[^A-Za-z0-9-]/', $setData['code'])) {
				mwMessages()->error(__('Slevový kód může obsahovat pouze číslice, pomlčku a znaky bez diakritiky. Nesmí obsahovat mezery ani žádné speciální znaky.', 'mwshop'));

				return false;
			}
			if (strlen($setData['code']) > 30) {
				mwMessages()->error(__('Slevový kód může obsahovat maximálně 30 znaků.', 'mwshop'));

				return false;
			}

			// is unique
			if (($discountCode = MwsDiscountCode::getOneByCode($setData['code'])) && (!$itemId || $discountCode->getId() != $itemId)) {
				mwMessages()->error(__('Slevový kód s tímto kódem již existuje, zadejte prosím jiný.', 'mwshop'));

				return false;
			}

			// value
			if ($setData['type'] == MwsDiscountCodeType::Fixed && $setData['value'] == '') {
				mwMessages()->error(__('Zadejte výši slevy.', 'mwshop'));

				return false;
			}
			if ($setData['type'] == MwsDiscountCodeType::Fixed && (float) $setData['value'] <= 0) {
				mwMessages()->error(__('Sleva musí být větší než 0.', 'mwshop') . ' ' . MWS()->getDefaultCurrency());

				return false;
			}
			// max percent
			if ($setData['type'] == MwsDiscountCodeType::Percent && ((float) $setData['value'] > 99 || (float) $setData['value'] < 1)) {
				mwMessages()->error(__('Procentuální sleva musí být v rozmezí 1 - 99%.', 'mwshop'));

				return false;
			}

			// order min price is lower then value

			if ($setData['type'] == MwsDiscountCodeType::Fixed && $setData['min_price'] == '') {
				mwMessages()->error(__('Je nutné zadat od jaké minimální částky objednávky se má sleva uplatnit.', 'cms'));

				return false;
			}
			if ($setData['type'] == MwsDiscountCodeType::Fixed && (float) $setData['min_price'] < (float) $setData['value']) {
				mwMessages()->error(__('Minimální výše objednávky pro uplatnění slevového kódu nemůže být nižší než je výše slevy.', 'mwshop'));

				return false;
			}

			// expiration / from is bigger then to
			if ($setData['expiration_type'] == MwsDiscountCodeExpirationType::DateRange) {
				if ($setData['expiration_from'] == '' || $setData['expiration_to'] == '') {
					mwMessages()->error(__('Musíte zadat začátek i konec platnosti slevového kódu.', 'mwshop'));

					return false;
				}
				if (strtotime($setData['expiration_from']) > strtotime($setData['expiration_to'])) {
					mwMessages()->error(__('Začátek platnosti musí být dřívější datum než konec platnosti.', 'mwshop'));

					return false;
				}
			}

			// count is bigger then 1
			if ($setData['expiration_type'] == MwsDiscountCodeExpirationType::Count && intval($setData['max_count']) < 1) {
				mwMessages()->error(__('Při omezení počtem použití nemůže být počet použití menší než 1.', 'mwshop'));

				return false;
			}
		}

		return true;
	}

	public function delete($id, $force_delete = false)
	{
		wp_delete_post($id, true);
	}
}
