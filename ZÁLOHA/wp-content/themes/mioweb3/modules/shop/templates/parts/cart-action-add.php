<?php
/**
 * Template part used to generate "ADD TO CART" form with the count edit field.
 */

$product = MWS()->current()->getProduct();
if (!$product) {
	// No product specified.
	return;
}
$url = MWS()->getUrl_CartAdd($product->getId());
if (empty($url)) { ?>
	<span class="mws_config_error"
		  xmlns="http://www.w3.org/1999/html"><?php echo __('Košík není nakonfigurován.', 'mwshop'); ?></span>
	<?php

	return;
}

$status = $product->getAvailabilityStatus(1);
$canBuy = $product->canBuy($status);
$isVariantRoot = $product->getStructure() === MwsProductStructureType::Variants;

if ($isVariantRoot) {
	wp_enqueue_script('ve_lightbox_script');
	wp_enqueue_style('ve_lightbox_style');
}

if ($canBuy) {
	echo '<div class="mws_add_to_cart_part">';
	if (isset(MWS()->visual_setting['show_product_count']) && !MwsProductType::isElectronic($product->getType())) {
		echo mwFrontComponents::countField([], 'mws_buy_count');
	} else {
		echo '<input type="hidden" name="count" value="1"/>';
	}
	?>
	<a href="#" class="add_tocart_button ve_content_button ve_content_button_icon ve_content_button_1 shop-action"
	   title="<?php echo __('Vložit zboží do košíku', 'mwshop'); ?>"
	   data-operation="mws_cart_add"
	<?php echo ($isVariantRoot ? 'data-variant-product' : 'data-product') . '="' . $product->getId() . '"'; ?>
	   data-count="input"
	   data-backurl="<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>"
	>
	<?php
	?>
		<span class="ve_but_icon"><?php echo mw_content_icon_set('shopping-cart'); ?></span>
		<span class="ve_but_loading_icon"><svg role="img"><use
					xlink:href="<?php echo MW_UI_ICONS_URL; ?>loading.svg#icon-loading-w"></use></svg></span>
		<span class="ve_but_text"><?php echo esc_html($product->getBuyButtonText($status)); ?></span>
	</a>
	<?php
	if ($isVariantRoot) {
		echo '<div class="mws_variant_list_container">';
		/*
		echo '  <a href="#" class="mws_dropdown_button ve_content_button ve_content_button_1 add_tocart_button" title="'
		. __('Zvolte variantu', 'mwshop').'">'
		.'v' //TODO Use dropdown image?
		.'</a>';   */
		$varProduct = MwsProductRoot::getOneById($product->getId());
		echo '<div class="mws_variant_list_content"'
		. ' data-all-availability-css="' . esc_attr(implode(' ', MwsProductAvailabilityStatus::getAllCSSArray())) . '"'
		. '>';

		echo '<div class="mws_add_to_cart_header mws_variant_list_header">' . __('Vybrat variantu pro', 'mwshop') . ' <strong>' . $product->getName() . '</strong>
				<a href="#" class="mws_close_cart_box">' . MWS()->getTemplateIcon('close') . '</a>
			</div>';
		foreach ($varProduct->getVariants() as $variant) {
			$count = 1;
			$availability = $variant->getAvailabilityStatus($count);
			if (!$variant->isListVisible($availability)) {
				continue;
			}
			$css = $variant->getAvailabilityCSS($availability);
			echo '<a href="#" class="shop-variant-select shop-action ' . $css . '"'
			. ($variant->canBuy() ? ' data-product="' . $variant->getId() . '"' : '')
			. ' data-operation="mws_cart_add"'
			. ' data-count="1"'
			. ' data-backurl="' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '"'
			/*
			. ' data-msg-buy-button="'.esc_attr(esc_html($variant->getBuyButtonText($availability))).'"'
			. ' data-msg-availability="'.esc_attr($variant->htmlAvailabilityMessage($availability)).'"'
			. ' data-availability-css="'.esc_attr($css).'"'
			. ' data-msg-price="'.esc_attr($variant->htmlPriceSaleFull()).'"'
			. ' data-msg-sale="'.esc_attr($variant->htmlPriceSaleFull(null,$count,array('vatExcluded','vatIncluded','salePrice'))).'"'
			*/
			. '>';
			?>
			<table class="mws_variant_list_item">
				<tr>
					<td class="responsive_image mws_variant_list_item_thumb">
						<div class="mw_image_ratio mw_image_ratio_<?php echo MWS()->thumb_name; ?>"><?php echo $variant->getThumbnail()->getImg('medium', ['loading' => false]); ?></div>
					</td>
					<td class="mws_variant_list_info">
					<?php
					foreach ($variant->getVariantValues() as $variant_value) {
						echo '<div class="mw_variant_info">';
						echo '<span class="mw_variant_info_name">' . $variant_value->getProperty()->getName() . '</span>';
						$unit = $variant_value->getProperty()->getUnit();
						echo '<span class="mw_variant_info_value">' . $variant_value->getName() . ($unit ? ' ' . $unit : '') . '</span>';
						echo '</div>';
					}
					?>
					</td>
					<td class="mws_variant_list_price">
					<?php
					echo '<div class="mws_product_price">' . $variant->htmlPriceSaleFull(null, 1, ['vatExcluded', 'saleDuration']) . '</div>';
					echo $variant->htmlAvailabilityMessage($availability);
					?>
					</td>
				</tr>
			</table>

			<span class="ve_but_icon"></span>
			<?php
			echo '</a>';
		}
		echo '</div>';
		echo '</div>';
	}
	echo '</div>'; // div.mws_add_to_cart_part
} else {
	// Can not be bought
	?>
	<div class="mws_product_sold_out_info">
		<span class = "mws_product_sold_out_text">
			<?php echo esc_html($product->getSoldOutText());?>
		</span>
	</div>
	<?php
}
