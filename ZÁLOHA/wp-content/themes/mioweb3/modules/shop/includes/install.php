<?php
/**
 * Routines for package installation, uninstallation, upgrage.
 *
 * Date: 04.02.16
 * Time: 13:29
 *
 * @since 1.0.0
 */

use Mioweb\Shop\Document\Document;
use Mioweb\Shop\Exceptions\MissingInvoiceContactException;
use Mioweb\Shop\Gates\ShopGateRepository;
use Mioweb\Shop\Order\Exceptions\OrderHasNoHashException;
use Mioweb\Shop\Order\History\OrderHistory;
use Mioweb\Shop\Order\History\OrderHistoryRepository;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Order\OrderItem;
use Mioweb\Shop\Order\OrderItemRepository;
use Mioweb\Shop\Order\OrderRepository;
use Mioweb\Shop\PacketSize;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Http\Url;
use Nette\Utils\Validators;
use Mioweb\Lib\LockFactory;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockInterface;

/**
 * Handles installation and uninstallation procedures.
 *
 * @class MwsInstall
 */
class MwsInstall
{

	const OPTION_INSTALLER_STEPS = 'mw_shop_installer_steps';

	private static ?int $startedTimestamp = null;

	public static function install(): void
	{
		// init all setting even if eshop is not installed yet
		MwsInitSetting::init();

		// Install tables
		self::tableEngines();
		self::installTables();

		// save setting
		$setting = mwSetting()->getPage(MWS_OPTION_SHOP_SETTING)->getDefaultSetting();
		update_option(MWS_OPTION_SHOP_SETTING, $setting);

		// eshop_emails setting
		$emails = mwSetting()->getPage('eshop_emails')->getDefaultSetting();
		update_option('eshop_emails', $emails);

		// currencies
		update_option(MWS_OPTION_CURRENCIES, [
			1 => [
				'currency' => 'czk',
				'round_orders' => 1,
				'round_function' => 'ceil',
				'round_precision' => 0,
			],
			2 => [
				'currency' => 'eur',
				'round_orders' => 1,
				'round_function' => 'ceil',
				'round_precision' => 1,
			],
		]);
		update_option(MWS_OPTION_DEFAULT_CURRENCY, 'czk');

		// shipping countries
		update_option(MWS_OPTION_SHIPPING_COUNTRIES, [
			1 => ['country' => 'CZ'],
			2 => ['country' => 'SK'],
		]);
		update_option(MWS_OPTION_DEFAULT_SHIPPING_COUNTRY, 'CZ');

		// save shipping
		$post = [
			'post_title' => __('Osobní odběr', 'mwshop'),
			'post_name' => __('osobni-odber', 'mwshop'),
			'post_status' => 'publish',
			'post_excerpt' => __('Zboží si můžete vyzvednout na naší prodejně.', 'mwshop'),
			'post_type' => MWS_SHIPPING_SLUG,
		];
		$shipping_id = wp_insert_post($post);
		update_post_meta($shipping_id, 'shipping', [
			'price' => 0,
			'vat_id' => 0,
			'country' => '',
			'type' => 'personal',
			'cod_enabled' => 1,
		]);

		$post = [
			'post_title' => __('Poštou', 'mwshop'),
			'post_name' => __('postou', 'mwshop'),
			'post_status' => 'publish',
			'post_excerpt' => __('Zboží Vám bude doručeno jako balík Českou poštou.', 'mwshop'),
			'post_type' => MWS_SHIPPING_SLUG,
		];
		$shipping_id = wp_insert_post($post);
		update_post_meta($shipping_id, 'shipping', [
			'price' => 99,
			'vat_id' => 0,
			'country' => '',
			'type' => 'custom',
		]);

		// save payments
		$post = [
			'post_title' => __('Při převzetí', 'mwshop'),
			'post_status' => 'publish',
			'post_excerpt' => MwsPayType::getDescription(MwsPayType::Cod),
			'post_type' => MWS_PAYMENT_METHOD_SLUG,
		];
		$pay_id = wp_insert_post($post);
		update_post_meta($pay_id, 'payment_method', [
			'type' => MwsPayType::Cod,
		]);
		$post = [
			'post_title' => __('Bankovní převod (1-2 dny)', 'mwshop'),
			'post_status' => 'publish',
			'post_excerpt' => MwsPayType::getDescription(MwsPayType::Wire),
			'post_type' => MWS_PAYMENT_METHOD_SLUG,
		];
		$pay_id = wp_insert_post($post);
		update_post_meta($pay_id, 'payment_method', [
			'type' => MwsPayType::Wire,
		]);
	}

	public static function createEshop(): void
	{
		if (!MWS()->isCreated()) {
			mwshoplog(__METHOD__, MWLL_DEBUG, 'eshop install');

			global $vePage;
			// create eshop page
			$post = [
				'post_title' => __('Eshop', 'mwshop'),
				'post_name' => __('eshop', 'mwshop'),
				'post_status' => 'publish',
				'comment_status' => 'open',
				'post_type' => 'page',
			];

			$temp_layer = [
				'0' => [
					'class' => '',
					'style' => [
						'background_color' => [
							'color1' => '#303030',
							'transparency1' => '1.00',
							'rgba1' => 'rgba(48, 48, 48, 1)',
							'color2' => '',
							'transparency2' => '1',
							'rgba2' => '',
						],
						'background_setting' => 'image',
						'background_image' => [
							'position' => '',
							'image' => MW_IMAGE_LIBRARY . 'bg/default_shop.jpg',
							'imageid' => '',
							'pattern' => '',
							'tablet' => [
								'position' => '',
								'image' => '',
								'imageid' => '',
							],
							'mobile' => [
								'position' => '',
								'image' => '',
								'imageid' => '',
							],
							'cover' => '1',
							'color_filter' => '1',
							'overlay_color' => [
								'color' => '#000000',
								'transparency' => '0.50',
								'rgba' => 'rgba(0, 0, 0, 0.5)',
							],
							'efect' => '',
							'repeat' => 'no-repeat',
						],
						'slider_overlay_color' => [
							'color' => '',
							'transparency' => '0.7',
							'rgba' => '',
						],
						'video_type' => 'iframe',
						'video_url' => '',
						'background_video_mp4' => '',
						'background_video_webm' => '',
						'background_video_ogg' => '',
						'video_image' => [
							'position' => '50% 50%',
							'image' => '',
							'imageid' => '',
							'cover' => '1',
						],
						'video_overlay_color' => [
							'color' => '',
							'transparency' => '0.7',
							'rgba' => '',
						],
						'row_height' => 'full',
						'min-height' => '100',
						'arrow_color' => '#fff',
						'content_align' => 'center',
						'text' => 'auto',
						'font' => [
							'font-family' => '',
							'weight' => '',
							'font-size' => '',
							'color' => '',
						],
						'link_color' => '',
						'type' => 'basic',
						'row_padding' => 'big',
						'padding_top' => '50',
						'tablet' => [
							'padding_top' => '',
							'padding_bottom' => '',
							'padding_left' => [
								'size' => '',
								'unit' => 'px',
							],
							'padding_right' => [
								'size' => '',
								'unit' => 'px',
							],
						],
						'mobile' => [
							'padding_top' => '',
							'padding_bottom' => '',
							'padding_left' => [
								'size' => '',
								'unit' => 'px',
							],
							'padding_right' => [
								'size' => '',
								'unit' => 'px',
							],
						],
						'padding_bottom' => '50',
						'padding_left' => [
							'size' => '',
							'unit' => 'px',
						],
						'padding_right' => [
							'size' => '',
							'unit' => 'px',
						],
						'border-top' => [
							'size' => '',
							'style' => 'solid',
							'color' => '',
						],
						'border-bottom' => [
							'size' => '',
							'style' => 'solid',
							'color' => '',
						],
						'shape_top' => [
							'shape' => 'tilt',
							'code' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none">
					<path d="M0,6V0h1000v100L0,6z"></path>
				</svg>
				',
							'size' => '100',
							'tablet' => [
								'size' => '',
							],
							'mobile' => [
								'size' => '',
							],
							'color' => '',
						],
						'shape_bottom' => [
							'shape' => 'tilt',
							'code' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none">
					<path d="M0,6V0h1000v100L0,6z"></path>
				</svg>
				',
							'size' => '100',
							'tablet' => [
								'size' => '',
							],
							'mobile' => [
								'size' => '',
							],
							'color' => '',
						],
						'margin_top' => '',
						'margin_bottom' => '',
						'css_class' => '',
						'row_anchor' => '',
						'delay' => '',
					],
					'content' => [
						'0' => [
							'type' => 'col-one',
							'class' => '',
							'content' => [
								'0' => [
									'style' => [
										'font' => [
											'font-size' => '50',
											'tablet' => [
												'font-size' => '',
											],
											'mobile' => [
												'font-size' => '',
											],
											'color' => '',
											'font-family' => '',
											'weight' => '',
											'line-height' => '1.2',
											'letter-spacing' => '0',
											'text-shadow' => 'none',
										],
										'style' => '1',
										'border' => [
											'size' => '1',
											'style' => 'solid',
											'color' => '#d5d5d5',
										],
										'background-color' => [
											'color1' => '#e8e8e8',
											'transparency1' => '1',
											'rgba1' => 'rgba(232,232,232,1)',
										],
										'decoration-color' => '#158ebf',
										'align' => 'center',
										'content' => '<p style="text-align: center;">' . __('ÚVODNÍ STRÁNKA VAŠEHO NOVÉHO E-SHOPU', 'mwshop') . '</p>',
										'mw30' => '1',
									],
									'type' => 'title',
									'config' => [
										'margin_top' => '',
										'tablet' => [
											'margin_top' => '',
											'margin_bottom' => '',
											'max_width' => '',
										],
										'mobile' => [
											'margin_top' => '',
											'margin_bottom' => '',
											'max_width' => '',
										],
										'margin_bottom' => '',
										'max_width' => '700',
										'element_align' => 'center',
										'animate' => '',
										'id' => '',
										'class' => '',
										'delay' => '',
									],
								],
								'1' => [
									'style' => [
										'font' => [
											'font-family' => '',
											'weight' => '',
											'font-size' => '',
											'color' => '',
											'line-height' => '',
										],
										'style' => '1',
										'p-background-color' => [
											'color1' => '#e8e8e8',
											'transparency1' => '1',
											'rgba1' => 'rgba(232,232,232,1)',
										],
										'content' => '<p style="text-align: center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque euismod ex quis risus ornare dapibus. Cras id felis purus. Ut eros risus, pellentesque eget congue et, tempus non nisl.</p>',
										'li' => '',
										'mw30' => '1',
									],
									'type' => 'text',
									'config' => [
										'margin_top' => '',
										'tablet' => [
											'margin_top' => '',
											'margin_bottom' => '',
											'max_width' => '',
										],
										'mobile' => [
											'margin_top' => '',
											'margin_bottom' => '',
											'max_width' => '',
										],
										'margin_bottom' => '',
										'max_width' => '600',
										'element_align' => 'center',
										'animate' => '',
										'id' => '',
										'class' => '',
										'delay' => '',
									],
								],
							],
						],
					],
				],
			];

			$home_id = $vePage->builder->save_new_page($post, 'page/1/', visualEditor::code($temp_layer));

			// create order page
			$post = [
				'post_title' => __('Košík', 'mwshop'),
				'post_name' => __('kosik', 'mwshop'),
				'post_status' => 'publish',
				'comment_status' => 'open',
				'post_type' => 'page',
			];

			$order_id = $vePage->builder->save_new_page($post, 'page/1/', visualEditor::code([]));

			// save setting
			$setting = get_option(MWS_OPTION_SHOP_SETTING, []);
			$setting['home_page'] = $home_id;
			$setting['order_page'] = $order_id;

			update_option(MWS_OPTION_SHOP_SETTING, $setting);

			// save visual setting
			MwsInitSetting::init();
			$visual_setting = mwSetting()->getPage(MWS_OPTION_SHOP_APPEARANCE)->getDefaultSetting();
			update_option(MWS_OPTION_SHOP_APPEARANCE, $visual_setting);

			// mark eshop created
			update_option('mw_eshop_created', '1');

			MwsTypesRegistration::registerForEshop();

			// activate rewrite rules for eshop products and categories
			flush_rewrite_rules();

			echo mwSetting()->getPage(MWS_OPTION_SHOP_SETTING)->getUrl();
			//wp_redirect(get_permalink($home_id));
			die();
		}
	}

	/**
	 * Perform installation or upgrade of MioShop.
	 * After successful installation global options are set, necessary tables are created.
	 */
	public static function autoInstall(): void
	{
		self::$startedTimestamp = time();

		mwshoplog(__METHOD__, MWLL_DEBUG, 'install');
		//Make sure custom types are registered.
		MwsTypesRegistration::registerAll();

		$versionInstalled = get_option('mwshop_version', null);
		$versionFiles = MioShop::version;

		if ($versionInstalled === null) {
			// Not installed.
			self::install();
			mwshoplog('MioShop not installed. Installing...', MWLL_INFO, 'install');
			update_option('mwshop_version', MioShop::version);
			update_option('mwshop_first_install', time());
			mwshoplog('MioShop installed as version ' . MioShop::version, MWLL_INFO, 'install');
		} elseif (version_compare($versionInstalled, $versionFiles, '<')) {
			// Upgrade needed
			mwshoplog('File version of MioShop is ' . MioShop::version . ', older version ' . $versionInstalled
				. ' of configuration found. Upgrade is needed.', MWLL_INFO, 'install');

			/**
			 * Array of upgrade functions. Functions are executed sequentially from the $versionInstalled up to the current version.
			 * As value use anonymous functions with code to be executed when upgradin from $key version.
			 */
			$upgradeFunc = [
				'1.0.2' =>
					function () {
						// Permalink structure changed - hooked bellow eshop home.
						mwshoplog('Flushing permalinks', MWLL_INFO, 'install');
						flush_rewrite_rules(true);
					},
				'1.0.3' =>
					function () {
						// Permalink structure modified - separate URIs.
						mwshoplog('Flushing permalinks', MWLL_INFO, 'install');
						flush_rewrite_rules(true);
					},
				'1.0.6' =>
					function () {
						// Permalink structure settings changed. Moved to another option.
						mwshoplog('Flushing permalinks', MWLL_INFO, 'install');
						flush_rewrite_rules(true);
					},
				'1.0.9' =>
					function () {
						// Content on end of order added to visual editor.
						$eshop_set = get_option(MWS_OPTION_SHOP_SETTING);

						if ($eshop_set['order_text']) {
							global $vePage;

							$new_post = [
								'post_title' => __('Text na konci objednávky', 'mw_shop'),
								'post_status' => 'publish',
								'post_type' => 'weditor',
								'post_author' => 1,
							];

							$content = [
								0 => [
									'class' => '',
									'style' => [
										'background_color' => ['color1' => '#fff', 'color2' => '', 'transparency' => '100'],
										'font' => [],
									],
									'content' => [
										0 => [
											'type' => 'col-one',
											'class' => '',
											'content' => [
												0 => [
													'type' => 'text',
													'content' => $eshop_set['order_text'],
													'style' => [
														'font' => [
															'font-size' => '',
															'font-family' => '',
															'weight' => '',
															'line-height' => '',
															'color' => '',
														],
														'li' => '',
													],
													'config' => ['margin_top' => 0, 'margin_bottom' => 20],
												],
											],
										],
									],
								],
							];

							$post_id = $vePage->save_new_window_post($new_post, '', visualEditor::code($content), 'weditor');

							$eshop_set['thanks_content'] = $post_id;
							update_option(MWS_OPTION_SHOP_SETTING, $eshop_set);
						}
					},
				'1.0.10' =>
					function () {
						// Set all product types to SINGLE.
						mwshoplog('Setting all old products as SINGLE types', MWLL_INFO, 'install');
						$qry = new WP_Query([
							'post_type' => MWS_PRODUCT_SLUG,
							'posts_per_page' => -1,
						]);
						if ($qry->have_posts()) {
							$updatedCnt = 0;
							foreach ($qry->posts as $post) {
								$res = add_post_meta($post->ID, MWS_PRODUCT_META_KEY_STRUCTURE, MwsProductStructureType::Single);
								if ($res) {
									$updatedCnt++;
								}
							}
							mwshoplog(
								$updatedCnt . ' product(s) defined in eshop version prior 1.0.8 set as SINGLE product type.',
								MWLL_INFO,
								'install'
							);
						}
					},
				'1.0.11' =>
					function () {
						// Permalink structure settings changed. Moved to another option.
						mwshoplog('Flushing permalinks', MWLL_INFO, 'install');
						flush_rewrite_rules(true);
					},
				'1.0.12' =>
					function () {
						// Change in FAPI API global settings. New field "pohoda_stock", removed fields "pohoda_store" and "pohoda_stock_item".
						// Code made backwards compatible, no data update is needed.
					},
				'1.0.13' =>
					function () {
						// shipping type back compatibility
						$args = [
							'post_type' => MWS_SHIPPING_SLUG,
							'posts_per_page' => -1,
						];
						$query = new WP_Query($args);
						if ($query->have_posts()) {
							foreach ($query->posts as $post) {
								$meta = get_post_meta($post->ID, 'shipping', true);
								if (!is_array($meta)) {
									$meta = [];
								}
								$meta['type'] = isset($meta['personal_pickup']) ? MwsShippingType::Personal : MwsShippingType::Custom;
								update_post_meta($post->ID, 'shipping', $meta);
							}
						}
					},
				'1.0.14' =>
					function () {
						// cleaning product variants without parents
						$args = [
							'post_type' => MWS_VARIANT_SLUG,
							'post_status' => 'publish',
							'posts_per_page' => -1,
						];
						$query = new WP_Query($args);
						if ($query->have_posts()) {
							foreach ($query->posts as $post) {
								$delete = false;

								if ($post->post_parent === null || $post->post_parent === 0) {
									$delete = true;
								} else {
									$product = MwsProduct::getOneById($post->post_parent);
									if ($product === null) {
										$delete = true;
									}
								}

								if ($delete) {
									mwshoplog(sprintf('Product variant "%s" [%d] was deleted.', $post->post_name, $post->ID), MWLL_INFO, 'install');
									wp_delete_post($post->ID);
								}
							}
						}
					},
				'3.1.0' => function () {
					$eshop_set = get_option(MWS_OPTION_SHOP_SETTING);
					$eshop_visual_set = get_option(MWS_OPTION_SHOP_APPEARANCE);

					if ($eshop_set) {
						if (isset($eshop_set['product_order'])) {
							$eshop_visual_set['product_order'] = $eshop_set['product_order'];
						}

						if (isset($eshop_set['eshop_display_product'])) {
							$eshop_visual_set['eshop_display_product'] = $eshop_set['eshop_display_product'];
						}

						if (isset($eshop_set['cart_content'])) {
							$eshop_visual_set['cart_content'] = $eshop_set['cart_content'];
						}

						if (isset($eshop_set['thanks_content'])) {
							$eshop_visual_set['thanks_content'] = $eshop_set['thanks_content'];
						}

						if (isset($eshop_set['eshop_hide'])) {
							if (isset($eshop_set['eshop_hide']['product_count'])) {
								$eshop_visual_set['show_product_count'] = $eshop_set['eshop_hide']['product_count'];
							}

							if (isset($eshop_set['eshop_hide']['comments'])) {
								$eshop_visual_set['hide_comments'] = $eshop_set['eshop_hide']['comments'];
							}

							if (isset($eshop_set['eshop_hide']['similar_products'])) {
								$eshop_visual_set['hide_similar_products'] = $eshop_set['eshop_hide']['similar_products'];
							}

							if (isset($eshop_set['eshop_hide']['social'])) {
								$eshop_visual_set['hide_social'] = $eshop_set['eshop_hide']['social'];
							}

							if (isset($eshop_set['eshop_hide']['availability'])) {
								$eshop_visual_set['hide_availability'] = $eshop_set['eshop_hide']['availability'];
							}

							if (isset($eshop_set['eshop_hide']['home_product_list'])) {
								$eshop_visual_set['hide_home_product_list'] = $eshop_set['eshop_hide']['home_product_list'];
							}

							if (isset($eshop_set['eshop_hide']['categories'])) {
								$eshop_visual_set['hide_categories'] = $eshop_set['eshop_hide']['categories'];
							}

							if (isset($eshop_set['eshop_hide']['search'])) {
								$eshop_visual_set['hide_search'] = $eshop_set['eshop_hide']['search'];
							}
						}

						if (isset($eshop_set['home_page']) && $eshop_set['home_page'] && isset($eshop_set['order_page']) && $eshop_set['order_page']) {
							update_option('mw_eshop_created', '1');
						}
					} else {
						self::install();
					}

					update_option(MWS_OPTION_SHOP_APPEARANCE, $eshop_visual_set);

					// eshop category images
					$categories = get_terms([
						'taxonomy' => MWS_PRODUCT_CAT_SLUG,
						'hide_empty' => false,
					]);
					if (!is_wp_error($categories)) {
						foreach ($categories as $cat) {
							$cat_meta = get_option('mws_eshop_category_fields_' . $cat->term_id);
							if (isset($cat_meta['category_image']) && $cat_meta['category_image']) {
								update_term_meta($cat->term_id, 'mw_thumbnail', $cat_meta['category_image']);
							}
						}
					}

					// order status and paid
					global $wpdb;
					$orders = $wpdb->get_results("SELECT {$wpdb->prefix}posts.ID, data.meta_value as data"
						. " FROM {$wpdb->prefix}posts"
						. " LEFT JOIN {$wpdb->prefix}postmeta AS data ON ( {$wpdb->prefix}posts.ID = data.post_id AND data.meta_key = 'mwshop_order' )"
						. " WHERE {$wpdb->prefix}posts.post_type = 'mworder'");

					foreach ($orders as $order) {
						$data = unserialize($order->data);
						$status = $data['status'] ?? MwsOrderStatus::Ordered;
						$paid = $order->data['status'] ?? false;
						if ($status == 3) {
							$status = MwsOrderStatus::Closed;
						}

						update_post_meta($order->ID, MWS_OPTION . 'order_status', $status);
					}

					// get setting from fapi
					$eshopSetting = get_option(MWS_OPTION_SHOP_SETTING);
					$fapiSetting = get_option(MWS_OPTION . '_gate_fapi');
					if ($fapiSetting) {
						$fapiGateway = MWS()->gateways()->getById('fapi');
						// set paygate to fapi for old versions
						$eshopSetting['paygate'] = 'fapi';
						$currentCurrency = $eshopSetting['currency'] ?? MwsCurrencyEnum::czk;
						// available currencies
						$currencies = [];
						$i = 1;
						foreach ([MwsCurrencyEnum::czk, MwsCurrencyEnum::eur] as $currency) {
							$currencies[$i] = [
								'currency' => $currency,
							];
							if ($currency === $currentCurrency) {
								update_option(MWS_OPTION_DEFAULT_CURRENCY, $currency);
							}
							$i++;
						}
						update_option(MWS_OPTION_CURRENCIES, $currencies);

						// shipping countries
						$countries = [];
						$i = 1;
						foreach ($fapiGateway->getSupportedCountries() as $country) {
							$countries[$i] = [
								'country' => $country,
							];
							$i++;
						}
						if (isset($countries[1])) {
							update_option(MWS_OPTION_DEFAULT_SHIPPING_COUNTRY, $countries[1]['country']);
						}

						update_option(MWS_OPTION_SHIPPING_COUNTRIES, $countries);

						// payment method
						// FAPI has separate payment method for each bank. We need only one universal`wireOnline` method
						$wireOnlineCreated = false;
						foreach ($fapiGateway->getEnabledPayments(true) as $payment) {
							if (!$wireOnlineCreated || $payment['payment_type'] !== MwsPayType::WireOnline) {
								wp_insert_post([
									'post_title' => MwsPayType::getCaption($payment['payment_type']),
									'post_status' => 'publish',
									'post_type' => MWS_PAYMENT_METHOD_SLUG,
									'comment_status' => 'closed',
									'ping_status' => 'closed',
									'post_name' => '',
									'meta_input' => [
										'payment_method' => [
											'type' => $payment['payment_type'],
										],
									],
								]);

								if ($payment['payment_type'] === MwsPayType::WireOnline) {
									$wireOnlineCreated = true;
								}
							}
						}

						$fapiInstance = $fapiGateway->sharedInstance();
						// Supplier info
						if ($fapiInstance instanceof MwsGatewayImpl_Fapi) {
							$fapiClient = $fapiInstance->getApi();

							try {
								$fapiUser = $fapiClient->getCurrentUser();
								$eshopSetting['sender_mail'] = $fapiUser['sender_reply_to'] ?? '';
								$eshopSetting['sender_name'] = $fapiUser['sender_name'] ?? '';
								$eshopSetting['company_name'] = $fapiUser['name'] ?? '';
								$eshopSetting['company_id'] = $fapiUser['ic'] ?? '';
								$eshopSetting['company_tax_id'] = $fapiUser['dic'] ?? '';
								$eshopSetting['company_vat_id'] = $fapiUser['ic_dph'] ?? '';
								$eshopSetting['street'] = $fapiUser['address']['street'] ?? '';
								$eshopSetting['city'] = $fapiUser['address']['city'] ?? '';
								$eshopSetting['country'] = $fapiUser['address']['country'] ?? '';
								$eshopSetting['zip'] = $fapiUser['address']['zip'] ?? '';
							} catch (\MwShop\FapiClient\Rest\InvalidStatusCodeException $e) {
								// FAPI might not be connected - ignore
							}
						}

						// GDPR
						foreach ($fapiSetting['form']['purposes'] ?? [] as $purpose) {
							if ($purpose['is_primary'] ?? false) {
								$eshopSetting['gdpr_text'] = $purpose['checkbox_label'] ?? '';
								$eshopSetting['gdpr_url_text'] = $purpose['link_label'] ?? '';
								$urlHref = $purpose['link_href'] ?? '';

								if ((bool) $urlHref && Validators::isUrl($urlHref)) {
									try {
										$url = new Url($urlHref);
										$page = mw_get_page_by_url($url);
									} catch (\Nette\InvalidArgumentException $e) {
										// Invalid URL - ignore;
										$page = null;
									}

									$eshopSetting['gdpr_url'] = $page !== null ? $page->ID : null;
								}

								break;
							}
						}

						// update form
						$fapiGateway->sharedInstance()->syncSettings();

						flush_rewrite_rules();
					}
					update_option(MWS_OPTION_SHOP_SETTING, $eshopSetting);
				},
				'3.1.1' => function () {
					$eshopSetting = get_option(MWS_OPTION_SHOP_SETTING);
					$fapiSetting = get_option(MWS_OPTION . '_gate_fapi');

					// Migrate VAT electronic invoicing setting (OSS vs Inland)
					if ($fapiSetting) {
						$fapiGateway = MWS()->gateways()->getById('fapi');

						if ($fapiGateway !== null) {
							$fapiInstance = $fapiGateway->sharedInstance();

							if ($fapiInstance instanceof MwsGatewayImpl_Fapi) {
								$fapiClient = $fapiInstance->getApi();

								try {
									$fapiValue = $fapiClient->getSetting('use_moss_for_non_vat_payer_eu_companies');
									if (is_array($fapiValue)) {
										$fapiValue = array_shift($fapiValue);
									}
									$mwValue = MwsVatElectronicInvoicing::getByFapiValue((int) $fapiValue);
									$eshopSetting['vat_electronic_products_invoicing'] = $mwValue;
									update_option(MWS_OPTION_SHOP_SETTING, $eshopSetting);
								} catch (\MwShop\FapiClient\Rest\InvalidStatusCodeException $e) {
									// FAPI might not be connected - ignore
								}
							}
						}
					}
				},
				'3.1.2' => function () {
					// Save default eshop_emails setting if no is already present
					$eshopEmails = get_option('eshop_emails');
					if ($eshopEmails === false) {
						if (!MwsInitSetting::$isInitialized) {
							MwsInitSetting::init(MWS()->isCreated());
						}

						$emails = mwSetting()->getPage('eshop_emails')->getDefaultSetting();
						update_option('eshop_emails', $emails);
					}
				},
				'3.1.3' => function () {
					$oldCodes = get_option('eshop_codes');
					$newCodes = MwCodes::convertCodesFromOldData($oldCodes, 'head_scripts', null, 'footer_scripts', 'css_scripts', 'eshop_conversion');
					update_option('mw_eshop_codes', $newCodes);
				},
				'3.1.4' => function () {
					// remove phone from fapi invoice note
					$fapiSetting = get_option(MWS_OPTION . '_gate_fapi');

					if ($fapiSetting) {
						$fapiGateway = MWS()->gateways()->getById('fapi');

						if ($fapiGateway !== null) {
							$fapiGateway->sharedInstance()->syncSettings();
						}
					}
				},
				'3.1.5' => function () {
					if (!MwsInitSetting::$isInitialized) {
						MwsInitSetting::init(MWS()->isCreated());
					}

					// Set default values for new setting "simplified-invoice_nums"
					$eshopSetting = get_option(MWS_OPTION_SHOP_SETTING);

					$optionName = MwsDocumentType::SimplifiedInvoice . '_nums';
					if (!isset($eshopSetting[$optionName])) {
						$defaultSettings = mwSetting()->getPage(MWS_OPTION_SHOP_SETTING)->getDefaultSetting();

						$eshopSetting[$optionName] = $defaultSettings[$optionName];

						update_option(MWS_OPTION_SHOP_SETTING, $eshopSetting);
					}
				},
				'3.1.6' => function () {
					$currencies = get_option(MWS_OPTION_CURRENCIES, []);

					$fapiSetting = get_option(MWS_OPTION . '_gate_fapi');

					// Migrate VAT electronic invoicing setting (OSS vs Inland)
					if ($fapiSetting && MWS()->gateways()->getDefault()->getId() === 'fapi') {
						$fapiGateway = MWS()->gateways()->getById('fapi');

						if ($fapiGateway !== null) {
							$fapiInstance = $fapiGateway->sharedInstance();

							if ($fapiInstance instanceof MwsGatewayImpl_Fapi) {
								$fapiClient = $fapiInstance->getApi();

								try {
									$fapiSet = $fapiClient->getSettings();
									foreach ($currencies as $key => $currency) {
										if (isset($fapiSet['round_' . $currency['currency'] . '_precision'])) {
											$currencies[$key]['round_orders'] = 1;
											$currencies[$key]['round_function'] = $fapiSet['round_' . $currency['currency'] . '_function'] ?? 'round';
											$currencies[$key]['round_precision'] = $fapiSet['round_' . $currency['currency'] . '_precision'];
										}
									}
								} catch (\MwShop\FapiClient\Rest\InvalidStatusCodeException $e) {
									// FAPI might not be connected - ignore
								}
							}
						}
					} else {
						foreach ($currencies as $key => $currency) {
							$currencies[$key]['round_orders'] = 1;
							$currencies[$key]['round_function'] = 'ceil';
							$currencies[$key]['round_precision'] = 0;

							$currencies[$key]['round_precision'] = $currency['currency'] === 'czk' ? 0 : 1;
						}
					}
					// currencies
					update_option(MWS_OPTION_CURRENCIES, $currencies);
				},
				'3.1.7' => function () {
					$eshopEmails = get_option('eshop_emails');
					if (!MwsInitSetting::$isInitialized) {
						MwsInitSetting::init(MWS()->isCreated());
					}

					$emailType = MwsEmailType::OrderPaymentFailed;

					if (!isset($eshopEmails[$emailType])) {
						$defaults = mwSetting()->getPage('eshop_emails')->getDefaultSetting();
						$eshopEmails[$emailType] = $defaults[$emailType];
						update_option('eshop_emails', $eshopEmails);
					}
				},
				'3.1.8' => function () {
					mwshoplog('Flushing permalinks', MWLL_INFO, 'install');
					flush_rewrite_rules(); // Upsell pages
				},
				'3.2.0' => function () {
					$factory = new LockFactory();
					$lock = $factory->createLock('shop-migration-3.2');

					try {
						if (!$lock->acquire()) {
							die();
						}
					} catch (LockConflictedException | LockAcquiringException) {
						die();
					}

					$steps = self::getInstallationSteps();
					$step = end($steps) ?: null;
					$migrationTimeout = 20 * 60;
					$currentTimestamp = time();

					if ($step !== null && ($step['status'] === 'running')) {
						$startedTimestamp = $step['time'];
						$lock->release();

						if ($currentTimestamp - $startedTimestamp > $migrationTimeout) {
							echo __('Při aktualizaci na novou verzi Miowebu pravděpodobně došlo k chybě.', 'mwshop');
							mwlog(MWLS_SHOP, 'Shop migration - migration timeout while processing 3.2.0.');
							die();
						}

						echo __('Web je dočasně nedostupný z důvodu probíhající aktualizace.', 'mwshop');
						die();
					}

					ini_set('max_execution_time', $migrationTimeout);
					set_time_limit($migrationTimeout);

					if (!array_key_exists('tables', $steps)) {
						self::addInstallationStep('table-engines', null, $lock);
						self::tableEngines($lock);
						$steps = self::markInstallationStepsAsDone();
					}

					if (!array_key_exists('tables-installed', $steps)) {
						self::addInstallationStep('tables', null, $lock);
						self::installTables($lock);
						self::addInstallationStep('tables-installed', null, $lock);
						$steps = self::markInstallationStepsAsDone();
					}

					if (!array_key_exists('done', $steps)) {
						$gates = [];
						foreach (ShopGateRepository::findAll() as $gate) {
							$gates[$gate->getIdentifier()] = $gate;
						}

						$step = $steps['orders'] ?? [];

						$limit = 50;
						$offset = (int) ($step['value'] ?? 0);

//						if ($step !== []) {
							// if it is not first iteration
//							$offset += $limit;
//						}

						do {
							self::addInstallationStep('orders', $offset, $lock);

							$page = ($offset / $limit) + 1;
							$oldOrders = MwsOrder::getAllOrders($limit, $page);

							foreach ($oldOrders as $oldOrder) {
								// TODO #3642 Vadí, že se změní IDčka?
								$newOrder = new Order(null, $oldOrder->getNumber());
//								$newOrder->setId($oldOrder->getId());
								try {
									$oldHash = $oldOrder->getHash();
									$newOrder->setHash($oldHash);
								} catch (OrderHasNoHashException $e) {
									$newOrder->regenerateHash();
								}
								$newOrder->setGate($gates[$oldOrder->getGateIdentifier() ?: 'fapi']);
								$newOrder->setStatus($oldOrder->getStatus());
								$newOrder->setPaid($oldOrder->isPaid(), false);
								$oldPaidAt = $oldOrder->getPaidOn();
								$newOrder->setPaidAt($oldPaidAt !== null ? (new \DateTimeImmutable())->setTimestamp($oldPaidAt) : null);
								$oldArchivedAt = $oldOrder->getArchivedDate();

								if ((bool) $oldArchivedAt) {
									try {
										$newOrder->setArchivedAt(new \DateTimeImmutable($oldArchivedAt));
									} catch (\Exception) {
										// Try to create datetime from timestamp
										$newOrder->setArchivedAt((new \DateTimeImmutable())->setTimestamp($oldArchivedAt));
									}
								}
								$newOrder->setAsOpened($oldOrder->isOpened());
								$newOrder->setIsTest($oldOrder->isTest());
								$newOrder->setNumber($oldOrder->getNumber());
								$customerId = $oldOrder->getCustomerId();
								if ($customerId !== null) {
									$userExists = get_user_by('id', $customerId) !== false;

									if ($userExists) {
										$newOrder->setCustomerId($customerId);
									}
								}
								$newOrder->setCurrency($oldOrder->getCurrency());
								$newOrder->setNativeCurrency($oldOrder->getNativeCurrency() ?: null);
								$newOrder->setExchangeRate($oldOrder->getCurrencyExchangeRate());
								$newOrder->setNote($oldOrder->getNote());
								$newOrder->setTrackingNumber($oldOrder->getTrackingNumber());
								$newOrder->setShipping($oldOrder->getShipping());
								$newOrder->setPayment($oldOrder->getPayment() ?? []);
								try {
									$newOrder->setInvoiceContact($oldOrder->getInvoiceContact());
								} catch (MissingInvoiceContactException $e) {
									// ignore
								}
								$newOrder->setShippingContact($oldOrder->getShippingContact());
								$newOrder->setTotal($oldOrder->getTotal());
								$newOrder->setDiscountCode($oldOrder->getDiscountCode() ?: null);
								$oldPacketSize = $oldOrder->getPacketSize();
								$newOrder->setPacketSize($oldPacketSize !== null ? PacketSize::fromArray($oldPacketSize) : null);
								$oldPacketData = self::getPacketData($oldOrder) ?: null;
								$newOrder->setPacketData($oldPacketData);
								$newOrder->setReverseCharge($oldOrder->isReverseChargeApplied());
								$newOrder->setSendPaymentFailedNotification($oldOrder->isPaymentFailedNotificationSent());
								$newOrder->setSimplifiedInvoice($oldOrder->useSimplifiedInvoice());
								$newOrder->setVatAccounting($oldOrder->getVatAccounting());
								$newOrder->setShowVat($oldOrder->showVat());
								$newOrder->setCustomerNote($oldOrder->getCustomerNote());
								$newOrder->setHeurekaDisagree($oldOrder->getHeurekaDisagree());
								$newOrder->setSource($oldOrder->getSource());
								$newOrder->setDirectPaymentUrl($oldOrder->getUrlDirectPay() ?: null);
								$newOrder->setShopVersion($oldOrder->getShopVersion());
								$newOrder->setGateOrderData($oldOrder->getGateOrderData() ?? null);
								$newOrder->setCreatedAt($oldOrder->getCreatedAt());

								// Items
								foreach ($oldOrder->getItems()->getAll() as $oldItem) {
									$oldProduct = $oldItem->getProduct();

									$newItem = new OrderItem(
										$oldItem->getName(),
										$oldItem->getType(),
										$oldItem->getPrices(),
										$oldItem->getCount(),
										$oldItem->getCodes(),
										$oldProduct !== null ? $oldProduct->getId() : null,
										$oldItem->isOssApplied(),
										$oldItem->isMiniupsell(),
										$oldItem->getWeight(),
									);

									$newOrder->getItems()->add($newItem);
								}

								// History
								foreach ($oldOrder->getHistory() as $timestamp => $oldHistoryItem) {
									$newHistoryItem = new OrderHistory(
										$oldHistoryItem['text'],
										$oldHistoryItem['user_id'] ?? null,
										(new \DateTimeImmutable())->setTimestamp($timestamp),
										$oldHistoryItem['event'] ?? null,
									);

									$newOrder->addHistoryItem($newHistoryItem);
								}

								try {
									OrderRepository::save($newOrder);
								} catch (UniqueConstraintViolationException $e) {
									mwshoplog(sprintf(
										'Order ID %d - migration failed - unique constraint violation - hash: %s.',
										$oldOrder->getId(),
										$newOrder->getHash(),
									), MWLL_ERROR, 'install');
								}

								$oldDocuments = Document::getAllByOldOrderId($oldOrder->getId());
								foreach ($oldDocuments as $oldDocument) {
									$oldDocument->setOrder($newOrder);
									$oldDocument->save();
								}
							}

							$offset += $limit;
						} while ($oldOrders);
					}

					self::addInstallationStep('permalinks', null, $lock);
					flush_rewrite_rules();
					self::markInstallationStepsAsDone();

					self::addInstallationStep('done', null, $lock);
					self::markInstallationStepsAsDone();

					$lock->release();
				},
			];
			$saveUpgraded = function ($from, $to) {
				update_option('mwshop_version', $to);
				update_option('mwshop_updated', time());
				mwshoplog('MioShop was upgraded from version ' . $from . ' to version ' . $to, MWLL_INFO, 'install');
			};
			$execute = false;
			$idx = 0;
			$versions = array_keys($upgradeFunc);
			$errorOccurred = false;
			foreach ($upgradeFunc as $key => $val) {
				$idx++;
				$nextVersion = $idx >= count($upgradeFunc)
					? $nextVersion = MioShop::version
					: $nextVersion = $versions[$idx];
				$execute = $execute || version_compare($key, $versionInstalled, '>');
				if ($execute) {
					try {
						if (is_callable($val)) {
							mwshoplog("Running inline upgrade script from version $key up", MWLL_INFO, 'install');
							$val();
						} elseif (is_string($val) && function_exists($val)) {
							mwshoplog("Running external upgrade script from version $key up", MWLL_INFO, 'install');
							call_user_func($val);
						} else {
							mwshoplog("No upgrade actions for version $key", MWLL_INFO, 'install');
						}

						$saveUpgraded($key, $nextVersion);
					} catch (Exception $e) {
						$errorOccurred = true;
						mwshoplog("Upgrade of version \"$versionInstalled\" failed. " . $e->getMessage(), MWLL_ERROR, 'install');

						break;
					}
				}
			}
			//All passed without errors? Works for case when no update scripts were necessary.
			if (!$errorOccurred) {
				$versionInstalled = get_option('mwshop_version', null);
				if (version_compare($versionInstalled, $versionFiles, '<')) {
					$saveUpgraded($versionInstalled, $versionFiles);
				}
			}
		}
	}

	private static function getInstallationSteps(): array
	{
		return get_option(self::OPTION_INSTALLER_STEPS, []) ?: [];
	}

	/** @todo remove in future */
	public static function getPacketData(MwsOrder $order)
	{
		$result = get_post_meta($order->getId(), 'mwshop_order_packeta', true);

		if ((bool) $result) {
			$result = (array) $result;
		}

		return $result;
	}

	private static function installTables(?LockInterface $lock = null): void
	{
		MWDB()->sql("SET @@sql_mode := REPLACE(REPLACE(@@sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '');");

		MWDB()->createTable(ShopGateRepository::getTableName(), '
			`id` int unsigned NOT NULL AUTO_INCREMENT,
  			`identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  			PRIMARY KEY (`id`)', false);

		MWDB()->insert(ShopGateRepository::getTableName(), [
			'id' => 1,
			'identifier' => 'mioweb',
		], false);
		MWDB()->insert(ShopGateRepository::getTableName(), [
			'id' => 2,
			'identifier' => 'fapi',
		], false);

		global $wpdb;

		MWDB()->createTable(OrderRepository::getTableName(), '
			`id` int unsigned NOT NULL AUTO_INCREMENT,
			`hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
			`gate_id` int unsigned NOT NULL,
			`status` smallint NOT NULL,
			`is_paid` tinyint(1) NOT NULL DEFAULT "0",
			`is_archived` tinyint(1) NOT NULL DEFAULT "0",
			`paid_at` datetime DEFAULT NULL,
			`archived_at` datetime DEFAULT NULL,
			`is_opened` tinyint(1) DEFAULT "0",
			`is_test` tinyint(1) NOT NULL DEFAULT "0",
			`variable_symbol` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
			`customer_id` bigint unsigned DEFAULT NULL,
			`currency` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
			`native_currency` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
			`exchange_rate` double DEFAULT NULL,
			`note` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
			`tracking_number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
			`shipping` json NOT NULL,
			`payment` json NOT NULL,
			`invoice_contact` json NULL,
			`shipping_contact` json DEFAULT NULL,
			`total` json NOT NULL,
			`discount_code` json DEFAULT NULL,
			`total_weight` double NULL,
			`packet_size` json DEFAULT NULL,
			`packet_data` json DEFAULT NULL,
			`reverse_charge_applied` tinyint(1) NOT NULL DEFAULT "0",
			`is_payment_failed_notification_sent` tinyint(1) NOT NULL DEFAULT "0",
			`use_simplified_invoice` tinyint(1) NOT NULL DEFAULT "0",
			`vat_accounting` enum("noVat","noVatIdentified","withVat") CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
			`show_vat` tinyint(1) DEFAULT NULL,
			`customer_note` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
			`heureka_disagree` tinyint(1) NOT NULL DEFAULT "0",
			`source_type` enum("eshop","form","quick-buy") CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
			`source_form_id` int unsigned DEFAULT NULL,
			`source_page_id` int unsigned DEFAULT NULL,
			`source_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
			`direct_payment_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
			`shop_version` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
			`gate_data` json DEFAULT NULL,
			`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
  			KEY `gate_id` (`gate_id`),
  			KEY `customer_id` (`customer_id`),
			CONSTRAINT `mw_orders_unique_hash` UNIQUE (`hash`),
			CONSTRAINT `mw_orders_ibfk_1` FOREIGN KEY (`gate_id`) REFERENCES `' . ShopGateRepository::getTableName() . '` (`id`) ON DELETE RESTRICT,
 			CONSTRAINT `mw_orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `' . $wpdb->prefix . 'users` (`ID`) ON DELETE SET NULL', false);

		MWDB()->createTable(OrderHistoryRepository::getTableName(), '
			`id` int unsigned NOT NULL AUTO_INCREMENT,
  			`order_id` int unsigned NOT NULL,
  			`text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  			`event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  			`user_id` int unsigned NULL,
  			`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  			PRIMARY KEY (`id`),
  			KEY `order_id` (`order_id`),
  			CONSTRAINT `mw_order_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `' . OrderRepository::getTableName() . '` (`id`)', false);

		MWDB()->createTable(OrderItemRepository::getTableName(), '
  			`id` int unsigned NOT NULL AUTO_INCREMENT,
  			`order_id` int unsigned NOT NULL,
  			`name` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  			`count` smallint NOT NULL,
  			`type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  			`product_id` int unsigned DEFAULT NULL,
  			`prices` json NOT NULL,
  			`codes` json NULL,
  			`product_codes` json DEFAULT NULL,
  			`oss_applied` tinyint(1) NOT NULL DEFAULT "0",
  			`is_miniupsell` tinyint(1) NOT NULL DEFAULT "0",
  			`weight` double DEFAULT NULL,
  			PRIMARY KEY (`id`),
  			KEY `order_id` (`order_id`),
  			CONSTRAINT `mw_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `' . OrderRepository::getTableName() . '` (`id`)', false);
	}
	private static function tableEngines(?LockInterface $lock = null): void
	{
		global $wpdb;

		$engine = self::getTableEngine($wpdb->prefix . 'users');
		if ($engine === null || strtolower($engine) !== 'innodb') {
			// TODO optimise - it kills webs with large `wp_users` table
			MWDB()->sql("ALTER TABLE {$wpdb->prefix}users ENGINE=InnoDB;");
		}
	}

	private static function getTableEngine(string $tableName): ?string
	{
		$status = (array) MWDB()->getRow("SHOW TABLE STATUS WHERE Name = '$tableName'");

		return $status['Engine'] ?? null;
	}

	private static function doABreakIfNeeded(?LockInterface $lock = null): void
	{
		$currentTimestamp = time();
		$maximumExecutionTimeout = 20;

		if (self::$startedTimestamp !== null && ($currentTimestamp - self::$startedTimestamp > $maximumExecutionTimeout)) {
			mwlog(MWLS_SHOP, 'Shop installation - reloading browser.');
			// Automaticaly reload the page
			echo '<script type="application/javascript">window.location.reload()</script>';

			if ($lock !== null) {
				$lock->release();
			}

			// TODO maybe show some maintenance page?
			die();
		}
	}

	private static function addInstallationStep(string $step, $value = null, ?LockInterface $lock = null): void
	{
		$steps = self::markInstallationStepsAsDone();

		if ($value === null) {
			self::doABreakIfNeeded($lock);
		}

		$timestamp = time();
		$pid = getmypid();

		$steps[$step] = [
			'step' => $step,
			'time' => $timestamp,
			'pid' => $pid,
			'value' => $value,
			'status' => 'running',
		];
		$scalarValue = is_scalar($value) || $value === null ? (string) $value : 'not-scalar';
		mwlog(MWLS_SHOP, sprintf('Updating installation step to "%s", time: %d, PID: %d, value: %s', $step, $timestamp, $pid, $scalarValue));
		update_option(self::OPTION_INSTALLER_STEPS, $steps);

		if ($value !== null) {
			self::markInstallationStepsAsDone();
			self::doABreakIfNeeded();
		}
	}

	private static function markInstallationStepsAsDone(?array $steps = null): array
	{
		$steps ??= self::getInstallationSteps();

		foreach ($steps as $key => $stepTmp) {
			$steps[$key]['status'] = 'done';
		}

		update_option(self::OPTION_INSTALLER_STEPS, $steps);

		return $steps;
	}

}
