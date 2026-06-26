<?php
use Mioweb\VisualEditor\Lib\Link;
use Mioweb\VisualEditor\Lib\Icon;
use Mioweb\VisualEditor\Lib\Button;
use Mioweb\Shop\Upsell;

function ve_element_pay_button($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$vePage->display->add_enqueue_script('shop_front_script');
	$vePage->display->add_enqueue_script('ve_lightbox_script');
	$vePage->display->add_enqueue_style('mwsShop');
	$vePage->display->add_enqueue_style('ve_lightbox_style');
	if (MWS()->packeta->isConnected()) {
		$vePage->display->add_enqueue_script('mw_packeta_library_script');
		$vePage->display->add_enqueue_script('mw_packeta_script');
	}

	$content = '';

	if (isset($element['style']['product_id']) && $element['style']['product_id'] && get_post($element['style']['product_id']) && $product = MwsProduct::getOneById($element['style']['product_id'])) {
		$allowDiscount = $element['style']['allow_discount_codes'] ?? false;
		$isVariantRoot = $product->getStructure() === MwsProductStructureType::Variants;
		$count = ($element['style']['count_default'] ?? 1);
		$status = $product->getAvailabilityStatus($count);
		$but_set = [
			'style' => $element['style']['button'],
			'link' => '#',
			'loading' => true,
			'icon' => new Icon([
				'icon' => 'shopping-cart',
				'icon_set' => 'feather',
			]),
			'text' => (isset($element['style']['content']) && !empty($element['style']['content']) ? $element['style']['content'] : esc_html($product->getBuyButtonText())),
			'align' => $element['style']['align'],
		];
		$kind = $element['style']['kind'] ?? null;
		$isQuick = $kind === 'quick' || $kind === 'quick_with_cart';
		$allowSimplified = $isQuick && (bool) ($element['style']['allow_simply_form'] ?? false);
		$thanksPage = $isQuick && isset($element['style']['thx_page']) ? Link::create_link(['page' => $element['style']['thx_page']]) : '';

		if ($product && $product->canBuyCount($count)) {
			// buying possible

			$variantList = '';
			if ($isVariantRoot) {
				$varProduct = MwsProductRoot::getOneById($product->getId());
				$variantList .= '<div class="mws_variant_list_container">';
				$variantList .= '	<div class="mws_variant_list_content"'
				. ' data-all-availability-css="' . esc_attr(implode(' ', MwsProductAvailabilityStatus::getAllCSSArray())) . '"'
				. '>';

				$variantList .= '<div class="mws_add_to_cart_header mws_variant_list_header">' . __('Vybrat variantu pro', 'mwshop') . ' <strong>' . $product->getName() . '</strong>
    								<a href="#" class="mws_close_cart_box">' . MWS()->getTemplateIcon('close') . '</a>
    						</div>';

				/*
				foreach ($varProduct->getVariants() as $variant) {
				$count = 1;
				$availability = $variant->getAvailabilityStatus($count);
				$css = $variant->getAvailabilityCSS($availability);
				$variantList .= '   <a href="#" class="shop-variant-select '.$css.'"'
				. ($variant->canBuy() ? ' data-product="'.$variant->getId().'"' : '')
				. ' data-msg-buy-button="'.esc_attr(esc_html($variant->getBuyButtonText($availability))).'"'
				. ' data-msg-availability="'.esc_attr($variant->htmlAvailabilityMessage($availability)).'"'
				. ' data-availability-css="'.esc_attr($css).'"'
				. ' data-msg-price="'.esc_attr($variant->htmlPriceSaleFull()).'"'
				. ' data-msg-sale="'.esc_attr($variant->htmlPriceSaleFull(null,$count,array('vatExcluded','vatIncluded','salePrice'))).'"'
				. '>'
				. ' <span class="mws_product_title_variant">'.esc_html($variant->getName()).'</span>'
				. ($variantPricesAreEqual
				? ''
				: ' <div class="mws_product_price">'.$variant->htmlPriceSaleFull(null,1,array('vatExcluded')).'</div>'
				)
				. ' ' . $variant->htmlAvailabilityMessage($availability)
				. ' '
				. ' </a>';
				}   */
				foreach ($varProduct->getVariants() as $variant) {
					$count = 1;
					$availability = $variant->getAvailabilityStatus($count);
					$css = $variant->getAvailabilityCSS($availability);
					$variantList .= '<a href="#" class="shop-variant-select shop-action ' . $css . '"'
					. ($variant->canBuy() ? ' data-product="' . $variant->getId() . '"' : '')
					. ' data-operation="mws_cart_add"'
					. ' data-count="1"'
					. ($isQuick ? ' data-isQuick="1"' : '')
					. ' data-allow-discount="' . ((int) $allowDiscount) . '"'
					. ' data-allow-simplified="' . ((int) $allowSimplified) . '"'
					. ' data-backurl="' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '"'
					. ' data-thanks-page="' . $thanksPage . '"'
					/*
					. ' data-msg-buy-button="'.esc_attr(esc_html($variant->getBuyButtonText($availability))).'"'
					. ' data-msg-availability="'.esc_attr($variant->htmlAvailabilityMessage($availability)).'"'
					. ' data-availability-css="'.esc_attr($css).'"'
					. ' data-msg-price="'.esc_attr($variant->htmlPriceSaleFull()).'"'
					. ' data-msg-sale="'.esc_attr($variant->htmlPriceSaleFull(null,$count,array('vatExcluded','vatIncluded','salePrice'))).'"'
					*/
					. '>';

					$variantList .= '<table class="mws_variant_list_item">'
					. '<tr>'
					. '<td class="mws_variant_list_item_thumb responsive_image">'
					. '<div class="mw_image_ratio mw_image_ratio_' . MWS()->thumb_name . '">' . $variant->getThumbnail()->getImg('medium', ['loading' => false]) . '</div>'
					. '</td>'
					. '<td class="mws_variant_list_info">';
					/** @var MwsPropertyValue $variant_value */
					foreach ($variant->getVariantValues() as $variant_value) {
						$variantList .= '<div class="mw_variant_info">';
						$variantList .= '<span class="mw_variant_info_name">' . $variant_value->getProperty()->getName() . '</span>';
						$unit = $variant_value->getProperty()->getUnit();
						$variantList .= '<span class="mw_variant_info_value">' . $variant_value->getName() . ($unit ? ' ' . $unit : '') . '</span>';
						$variantList .= '</div>';
					}
					$variantList .= '</td>'
					. '<td class="mws_variant_list_price">'
					. '<div class="mws_product_price">' . $variant->htmlPriceSaleFull(null, 1, ['vatExcluded', 'saleDuration']) . '</div>'
					. $variant->htmlAvailabilityMessage($availability)
					. '</td>'
					. '</tr>'
					. '</table>'
					. '<span class="ve_but_icon"></span>';

					$variantList .= '</a>';
				}
				$variantList .= '	</div>';
				$variantList .= '	</div>';
			}

			$content .= ($isVariantRoot ? '<div class="mws_add_to_cart_part">' : '');

			$but_set['attrs'] = ''
				. 'data-operation="mws_cart_add"'
				. ' ' . ($isVariantRoot ? 'data-variant-product' : 'data-product') . '="' . $product->getId() . '"'
				. ' data-count="' . $count . '"'
				. ($isQuick ? ' data-isQuick="1"' : '')
				. ' data-allow-discount="' . ((int) $allowDiscount) . '"'
				. ' data-allow-simplified="' . ((int) $allowSimplified) . '"'
				. ' data-backurl="' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '"'
				. ' data-thanks-page="' . $thanksPage . '"';

			$content .= Button::createButton(
				$but_set,
				$vePage->display->element_css,
				'shop-action ' . $product->getAvailabilityCSS($status),
				$css_id . ' .ve_content_button',
				$added,
				$edit_mode
			);

			$content .= $variantList;
			$content .= ($isVariantRoot ? '</div>' : '');
		} else {
			// no buy
			$but_set['text'] = $product->getBuyButtonText($status);

			$but_set['attrs'] = ''
				. 'data-operation="mws_cart_add"'
				. ' ' . ($isVariantRoot ? 'data-variant-product' : 'data-product') . '="' . $product->getId() . '"'
				. ' data-product="' . $element['style']['product_id'] . '"'
				. ' data-count="' . $count . '"'
				. ($isQuick ? ' data-isQuick="1"' : '')
				. ' data-allow-discount="' . ((int) $allowDiscount) . '"'
				. ' data-backurl="' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '"'
				. ' data-thanks-page="' . $thanksPage . '"';

			$content .= Button::createButton(
				$but_set,
				$vePage->display->element_css,
				'shop-action ' . $product->getAvailabilityCSS($status),
				$css_id . ' .ve_content_button',
				$added,
				$edit_mode
			);
		}
		$content .= '<div class="cms_clear"></div>';
	} else {
		$vePage->display->add_element_info(__('Není vybrán žádný produkt k prodeji.', 'mwshop'), 'info');
	}

	return $content;
}

function ve_element_product_list($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$vePage->display->add_enqueue_script('shop_front_script');
	$vePage->display->add_enqueue_script('ve_lightbox_script');
	$vePage->display->add_enqueue_style('mwsShop');
	$vePage->display->add_enqueue_style('ve_lightbox_style');

	$content = '';

	$orderby = $element['style']['order'] ?? 'date';

	$args = ['posts_per_page' => -1, 'orderby' => $orderby];

	if ($orderby == 'bestseller') {
		$args['orderby'] = 'meta_value_num';
		$args['meta_key'] = 'ordered_count';
		$args['order'] = 'DESC';
	} elseif ($orderby == 'menu_order') {
		$args['order'] = 'ASC';
	} elseif ($orderby == 'title') {
		$args['order'] = 'ASC';
	}

	// remove invisile
	$invisibleIds = MwsProduct::getInvisibleProducts(true);
	if (!empty($invisibleIds)) {
		$args['post__not_in'] = $invisibleIds;
	}

	if ($element['style']['show'] == 'custom') {
		$query = [];
		if (!empty($element['style']['custom_products'])) {
			foreach ($element['style']['custom_products'] as $product) {
				if ($product['product_id'] && get_post($product['product_id'])) {
					$query[] = MwsProduct::createNew(get_post($product['product_id']));
				}
			}
		}
	} else {
		if ($element['style']['show'] == 'category') {
			$args['tax_query'] = [
				[
					'taxonomy' => MWS_PRODUCT_CAT_SLUG,
					'field' => 'term_id',
					'terms' => $element['style']['category'],
				],
			];
		} elseif ($element['style']['show'] == 'bestsellers') {
			$args['orderby'] = 'meta_value_num';
			$args['meta_key'] = 'ordered_count';
			$args['order'] = 'DESC';
			$args['own_per_page'] = 1;
			$args['posts_per_page'] = $element['style']['bestsellers_count'] ?: '3';
		}

		$query = MwsProduct::getAll($args, false);
	}

	if (count($query)) {
		if (isset($element['style']['font_price'])) {
			$vePage->display->element_css->addStyles(
				['font' => $element['style']['font_price']],
				$css_id . ' .mws_price_vatincluded'
			);
		}

		$element['style']['cols'] = isset($element['style']['cols']) && $element['style']['cols'] ? $element['style']['cols'] : 3;
		$cols = $element['style']['cols'];

		$content = '<div class="in_element_content mws_product_list">';
		$content .= MWS()->writeProducts($query, $cols, $element['style']['product_style'], $element['style'], $col_type, $css_id, $added);
		$content .= '</div>';
	} elseif ($vePage->edit_mode) {
		if ($element['style']['show'] == 'bestsellers') {
			$vePage->display->add_element_info(__('Zatím nebyl prodán žádný produkt, proto nelze vypsat nejprodávanější produkty.', 'mwshop'));
		} else {
			$vePage->display->add_element_info(__('Výpis produktů je prázdný.', 'mwshop'), 'info');
		}
	}

	return $content;
}

function ve_element_eshop_category_list($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$vePage->display->add_enqueue_script('shop_front_script');
	$vePage->display->add_enqueue_style('mwsShop');

	$content = '';

	$args = ['hide_empty' => false, 'parent' => 0, 'published' => 1];

	if ($element['style']['show'] == 'sub') {
		if ($element['style']['category_parent']) {
			$args['parent'] = $element['style']['category_parent'];
		}
	}

	$categories = mwTerm::getAll(MWS_PRODUCT_CAT_SLUG, $args);

	if ($element['style']['style'] == 'v1') {
		$vePage->display->element_css->addStyles(
			['font' => $element['style']['vmenu_font']],
			$css_id . ' li'
		);

		$content .= '<div class="in_element_content mw_vertical_menu mw_vertical_menu_center">' . MWS()->getShopCategories($vePage->display->get_font_class($element['style']['vmenu_font'], 'text'), 0, $categories) . '</div>';
	} else {
		if (count($categories)) {
			$element['style']['cols'] = isset($element['style']['cols']) && $element['style']['cols'] ? $element['style']['cols'] : 0;
			$cols = $vePage->display->getAutoCols($element['style']['cols'], count($categories), 3, false, $element['style']['style']);

			$hover_style = $element['style']['hover'] ?? '';
			$image_ratio = $element['style']['image_ratio'] ?? '32';
			$text_align = $element['style']['text_align'] ?? 'left';
			$img_col_size = $element['style']['image_size'] ?? 2;

			$items_set = [];

			foreach ($categories as $item) {
				$args = [
					'style' => $element['style']['style'],
					'cols' => $cols,
					'link' => $item->getUrl(),
					'image' => $item->getThumbnail(),
					'title' => $item->getName(),
					'edit_button' => $item->getEditButton(),
				];

				if ($element['style']['style'] == '1') {
					$args['image_hover_link'] = $args['link'] ? true : false;
					$args['image_hover_content'] = '<h3 class="' . $vePage->display->get_font_class($element['style']['font']) . '">' . $args['title'] . '</h3>';
				}

				$items_set[] = $args;
			}

			$items_args = [
				'style' => $element['style']['style'],
				'cols' => $cols,
				'inside_col_type' => $col_type,
				'autocols' => !$element['style']['cols'] ? true : false,
				'cols_type' => $element['style']['cols_type'] ?? 'cols',
				'hover_style' => $hover_style,
				'image_ratio' => $image_ratio,
				'align' => $text_align,
				'img_col_size' => $img_col_size,
				'styles' => [
					'hover_color' => isset($element['style']['hover_color']) ? $element['style']['hover_color']['rgba'] : 'rgba(0,0,0,0,5)',
					'font_title' => $element['style']['font'],
				],
				'cssid' => $css_id,
				'added' => $added,
			];

			if (isset($element['style']['background_set'])) {
				$items_args['background_set'] = $element['style']['background_set'];
			}

			if ($element['style']['style'] == '1') {
				$items_args['image_hover'] = true;
			}

			$content = '<div class="in_element_content in_shop_category_list_element">';
			$content .= $vePage->display->generate_element_items($items_args, $items_set, $added, $row_set);
			$content .= '</div>';
		} else {
			$vePage->display->add_element_info(__('Výběru neodpovídá žádná kategorie.', 'mwshop'));
		}
	}

	return $content;
}

function ve_element_product_detail($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage, $product;

	$vePage->display->add_enqueue_script('shop_front_script');
	$vePage->display->add_enqueue_script('ve_lightbox_script');
	$vePage->display->add_enqueue_style('mwsShop');
	$vePage->display->add_enqueue_style('ve_lightbox_style');

	$content = '';
	if ($element['style']['product_id'] && get_post($element['style']['product_id'])) {
		$productId = (int) ($element['style']['product_id']);
		$post = get_post($productId);
		$product = MwsProduct::createNew($post);

		MWS()->current()->setProduct($product);

		$content .= mwsRenderParts('product', 'detail', true);

		$gallery = get_post_meta($productId, 'product_gallery', true);

		if ($added && $gallery && isset($gallery['gallery'])) {
			$content .= '<script>
            jQuery(function() {
                mwGetIframeContent().set_miocarousel("' . $css_id . ' .miocarousel");
            });
            </script>';
		}
		if ($added && isset(MWS()->visual_setting['show_product_count'])) {
			$content .= '<script>' .
			'jQuery(function(){' .
			'mwGetIframeContent().mw_init_product_count("' . $css_id . ' .mws_product_count_field");' .
			'});' .
			'</script>';
		}
	} else {
		$vePage->display->add_element_info(__('Pro výpis detailu musíte vybrat produkt.', 'mwshop'), 'info');
	}

	return $content;
}

function ve_element_product_price($element, $css_id)
{
	global $vePage;
	$vePage->display->add_enqueue_script('shop_front_script');
	$vePage->display->add_enqueue_style('mwsShop');

	$content = '';

	if (isset($element['style']['product_id']) && $element['style']['product_id'] && get_post($element['style']['product_id'])) {
		$vePage->display->element_css->addStyles(
			['font' => $element['style']['font']],
			$css_id . ' .mws_price_vatincluded'
		);

		$hide = ['salePercentage'];
		if (isset($element['style']['hide']['salePrice'])) {
			$hide[] = 'salePrice';
		}
		if (isset($element['style']['hide']['vatExcluded'])) {
			$hide[] = 'vatExcluded';
		}

		$product = MwsProduct::getOneById($element['style']['product_id'] ?? 0);
		if ($product) {
			MWS()->current()->setProduct($product);

			//'.$vePage->display->get_font_class($element['style']['font'],'text').'

			$content .= '<div class="mws_product_price in_element_content in_element_product_price ve_' . $element['style']['align'] . '">' . $product->htmlPriceSaleFull(null, 1, $hide, $vePage->display->get_font_class($element['style']['font'], 'text')) . '</div>';
		} else {
			$content .= '<div class="mws_product_price in_element_content in_element_product_price ve_' . $element['style']['align'] . '">' . __('(zvolte produkt)', 'mwshop') . '</div>';
		}
	} else {
		$vePage->display->add_element_info(__('Není vybrán žádný produkt pro vypsání ceny.', 'mwshop'), 'info');
	}

	return $content;
}

function ve_element_upsell_price(array $element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;
	$vePage->display->add_enqueue_script('shop_front_script');
	$vePage->display->add_enqueue_style('mwsShop');
	$content = '';
	$upsellId = $post_id;
	if ($upsellId !== null) {
		$upsell = Upsell::getOneById($upsellId);
		\assert($upsell instanceof Upsell || $upsell === null);

		if ($upsell !== null && $upsell->isValid()) {
			$vePage->display->element_css->addStyles(
				['font' => $element['style']['price_font']],
				$css_id . ' .mws_upsell_price_container'
			);
			$vePage->display->element_css->addStyles(
				['font' => $element['style']['sale_font']],
				$css_id . ' .mws_original_upsell_price_container'
			);
			$vePage->display->element_css->addStyles(
				['color' => $element['style']['price_color']],
				$css_id . ' .mws_upsell_price'
			);

			$product = $upsell->getProduct();
			$unit = MWS()->getDefaultCurrency();
			$currency = MWS()->getDefaultCurrency('key');

			$priceDiffText = '';
			$securityCode = $_GET[Upsell::SECURITY_CODE_QUERY_PARAMETER] ?? null;
			if ($securityCode && $product->getWeight()) {
				$form = MwsForm::getOneById($upsell->getFormId());
				\assert($form instanceof MwsForm);

				$cart = MWS()->getFormCart($form);
				$currency = $cart->getCurrency();
				$unit = MwsCurrencyEnum::getSymbol($currency);
				$shipping = $cart->getShipping();

				if ($shipping->isPriceByWeight()) {
					$currentPrice = $shipping->getTotalPrice($cart->getPaymentMethod(), $cart->getStoredTotalPrice(), $cart->getItems()->getTotalWeight())->asCurrency($cart->getCurrency());
					$newPrice = $shipping->getTotalPrice($cart->getPaymentMethod(), $cart->getStoredTotalPrice(), $cart->getItems()->getTotalWeight() + $product->getWeight())->asCurrency($cart->getCurrency());
					$priceDiff = $newPrice->sub($currentPrice);
					if ($priceDiff->getPriceVatIncluded() > 0) {
						$priceDiffText = '<div class="mws_upsell_shipping_price">';
						$priceDiffText .= '+ ';
						$priceDiffText .= htmlPriceSimple(
							$priceDiff->getPriceVatIncluded(),
							$unit,
						);
						$priceDiffText .= ' ' . __('navýšení ceny dopravy.', 'mwshop');
						$priceDiffText .= '</div>';
					}
				}
			}

			$content .= '<div class="in_element_content in_element_upsell_price ve_' . $element['style']['align'] . '">';
			if ($product->isDiscountedNow()) {
				$content .= '<div class="mws_original_upsell_price_container">';
				$text = $element['style']['sale_before'] ?? '';
				$content .= $vePage->display->printContentContainer($text, 'mws_original_upsell_price_before', 'span') . ' ';
				$content .= htmlPriceSimple(
					$product->getPriceFull()->asCurrency($currency)->getPriceVatIncluded(),
					$unit,
					false,
					'mws_original_upsell_price'
				);
				$text = $element['style']['sale_after'] ?? '';
				$content .= ' ' . $vePage->display->printContentContainer($text, 'mws_original_upsell_price_after', 'span');
				$content .= '</div>';
			}
			$content .= '<div class="mws_upsell_price_container">';
			$text = $element['style']['price_before'] ?? '';
			$content .= $vePage->display->printContentContainer($text, 'mws_upsell_price_before', 'span') . ' ';
			$content .= htmlPriceSimple(
				$product->getPrice()->asCurrency($currency)->getPriceVatIncluded(),
				$unit,
				false,
				'mws_upsell_price'
			);
			$text = $element['style']['price_after'] ?? '';
			$content .= ' ' . $vePage->display->printContentContainer($text, 'mws_upsell_price_after', 'span');
			$content .= '</div>';
			//$content .= $upsell->getProduct()->htmlPriceSaleFull(null, 1, $hide, $vePage->display->get_font_class($element['style']['font'], 'text'));
			$content .= $priceDiffText;
			$content .= '</div>';

			return $content;
		}
	}

	return '<div class="mw_error_box">' . __('Obsah nelze vykreslit.', 'mwshop') . '</div>';
}

function ve_element_upsell(array $element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;
	$vePage->display->add_enqueue_script('shop_front_script');
	$vePage->display->add_enqueue_style('mwsShop');

	$content = '';

	$upsellId = $post_id;
	$securityCode = $_GET[Upsell::SECURITY_CODE_QUERY_PARAMETER] ?? null;
	if ($upsellId !== null) {
		$upsell = Upsell::getOneById($upsellId);
		\assert($upsell instanceof Upsell || $upsell === null);

		if ($upsell !== null && $upsell->isValid()) {
			$product = $upsell->getProduct();
			\assert($product instanceof MwsProduct);

			$content .= '<div class="in_element_mws_upsell_buttons mws_upsell_buttons_element in_element_content in_element_upsell_buttons_app_' . ($element['style']['appearance'] ?? 'next') . '">';
			$content .= '<div class="mws_flash_messages"></div>';
			$content .= '<div class="mws_upsell_buttons_container in_element_content_button in_element_content_button_' . ($element['style']['align'] ?? 'center') . '">';
			$but_set1 = [
				'style' => $element['style']['button_yes'],
				'link' => '#',
				'text' => $element['style']['yes_text'] ?? __('Ano, koupit', 'mwshop'),
				'loading' => true,
				'subtext' => $element['style']['yes_subtext'] ?? '',
				'attrs' => 'data-upsell-id="' . $upsellId . '" data-security-code="' . $securityCode . '"',
			];
			$content .= Button::createButton(
				$but_set1,
				$vePage->display->element_css,
				'mws_upsell_button ve_content_button_icon mws_upsell_button_yes' . ($edit_mode ? ' js-disabled' : ''),
				$css_id . ' .mws_upsell_button',
				$added,
				$edit_mode,
			);

			$but_set2 = [
				'style' => $element['style']['button_yes'],
				'link' => '#',
				'text' => $element['style']['no_text'] ?? __('Ne, děkuji', 'mwshop'),
				'loading' => true,
				'attrs' => 'data-upsell-id="' . $upsellId . '" data-security-code="' . $securityCode . '"',
			];
			$content .= Button::createButton(
				$but_set2,
				$vePage->display->element_css,
				'mws_upsell_button ve_content_button_icon mws_upsell_button_no' . ($edit_mode ? ' js-disabled' : ''),
				$css_id . ' .mws_upsell_button',
				$added,
				$edit_mode,
			);
			$content .= '</div>';
			$content .= '</div>';

			return $content;
		}
	}

	return '<div class="mw_error_box">' . __('Obsah nelze vykreslit.', 'mwshop') . '</div>';
}

function ve_element_upsell_image(array $element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;
	$vePage->display->add_enqueue_script('shop_front_script');
	$vePage->display->add_enqueue_style('mwsShop');

	$content = '';

	$upsellId = $post_id;
	$securityCode = $_GET[Upsell::SECURITY_CODE_QUERY_PARAMETER] ?? null;
	if ($upsellId !== null) {
		$upsell = Upsell::getOneById($upsellId);
		\assert($upsell instanceof Upsell || $upsell === null);

		if ($upsell !== null && $upsell->isValid()) {
			$product = $upsell->getProduct();
			\assert($product instanceof MwsProduct);

			$element['style']['click_action'] = 'none';

			if (isset($element['style']['upsell-max-width']) && $element['style']['upsell-max-width'] || $edit_mode) {
				$vePage->display->element_css->addVariableStyles(
					[
						$css_id . ' .mw_upsell_image_container' => ['max-width'],
					],
					'--upsell-image-width-' . $css_id,
					isset($element['style']['upsell-max-width']) && $element['style']['upsell-max-width'] ? $element['style']['upsell-max-width'] . 'px' : '100%'
				);
			}

			if (!isset($element['style']['image']['image']) || !$element['style']['image']['image']) {
				$element['style']['image']['image'] = $upsell->getProduct()->getThumbnail()->getImage();
			}

			$image_align = $element['style']['upsell_image_align'] ?? 'left';
			$image = ve_element_image($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type);
			if ($image) {
				$content .= '<div class="in_element_upsell_image ve_' . $image_align . '">';
				$content .= '<div class="mw_upsell_image_container">';
				$content .= $image;
				if ($product->getDiscountPercentage() > 0 && !isset($element['style']['hide_sale'])) {
					$content .= ' <span class="mws_price_sale_percentage">-' . $product->getDiscountPercentage() . ' %</span>';
				}
				$content .= '</div>';
				$content .= '</div>';
			}

			return $content;
		}
	}

	return '<div class="mw_error_box">' . __('Obsah nelze vykreslit.', 'mwshop') . '</div>';
}
