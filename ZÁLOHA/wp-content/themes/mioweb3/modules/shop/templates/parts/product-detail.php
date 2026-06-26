<?php

/**
 * Template part used in product detail page ane product detail element.
 */

use Mioweb\VisualEditor\Lib\Image;

global $vePage;

/** @var MwsProduct $product */
$product = MWS()->current()->getProduct();

//TODO Nonexisting product scenario?
if (!$product) {
	return;
}

wp_enqueue_script('ve_lightbox_script');
wp_enqueue_style('ve_lightbox_style');

$count = 1;
$availability = $product->getAvailabilityStatus($count);
?>
<div class="mws_product mws_single_product mws_product-<?php echo $product->getId()
. ' ' . $product->getAvailabilityCSS($availability); ?>">
	<div class="mws_thumb col col-2">
		<?php
		$mainImage = $product->getThumbnail();
		if (!$mainImage->isEmpty()) {
			echo '<a href="' . $product->getThumbnail()->getUrl('large') . '" class="thumb open_lightbox responsive_image" rel="mws_product_gallery">';
			echo $mainImage->printImg([
					'col_divisor' => 2,
			]);
			echo '</a>';
		} ?>
		<div class="mws_product_sale">
			<?php echo $product->htmlPriceSaleFull(null, $count, ['vatExcluded', 'vatIncluded', 'salePrice', 'saleDuration', 'discountSave']); ?>
		</div>
		<?php
		if (count($product->getTagsSet())) {
			echo '<div class="mw_element_item_labels">';
			foreach ($product->getTagsSet() as $label) {
				echo mwFrontComponents::textLabel($label);
			}
			echo '</div>';
		}

		if ($gallery = $product->getGallery()) {
			$gal_rows = array_chunk($gallery, 3);
			$gallery_slider = false;
			if (count($gal_rows) > 1) {
				$gallery_slider = true;
				wp_enqueue_script('ve_miocarousel_script');
				wp_enqueue_style('ve_miocarousel_style');
			}

			echo '<div class="mws_product_image_gallery">';
			if ($gallery_slider) {
				echo '<div class="miocarousel miocarousel_style_2" data-autoplay="0" data-animation="slide" data-indicators="0">';
				echo '<div class="miocarousel-inner">';
			}
			foreach ($gal_rows as $gal_row) {
				echo '<div class="mws_product_image_gallery_slide ' . ($gallery_slider ? 'slide' : '') . '">';
				foreach ($gal_row as $gal_image_id) {
					$galImage = Image::createById($gal_image_id);
					echo '<a class="open_lightbox col responsive_image" href="' . $galImage->getUrl('large') . '" rel="mws_product_gallery">'
							. $galImage->printImg([
								'col_divisor' => 6,
								'mobile_col_divisor' => 3,
							])
							. '</a>';
				}
				echo '<div class="cms_clear"></div></div>';
			}
			if ($gallery_slider) {
				echo '</div>';
				echo '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
				echo '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
				echo '</div>';
			}
			echo '</div>';
		}
		?>
	</div>
	<div class="col col-2">
		<?php
		$tag = is_singular(MWS_PRODUCT_SLUG) ? 'h1' : 'h2';
		echo '<' . $tag . ' class="mws_product_title title_element_container">' . get_the_title($product->getId()) . '</' . $tag . '>';

		if ($product->getStructure() === MwsProductStructureType::OneVariant) {
			echo '<div class="mw_breadcrumbs">' . __('Varianta produktu', 'mwshop') . ': <a href="' . $product->getDetailUrl() . '">' . $product->getProduct()->getName() . '</a></div>';
			$product_excerpt = $product->getProduct()->getExcerpt();
		} else {
			$hide_cat = isset(MWS()->visual_setting['hide_categories']) ? true : false;
			echo $vePage->display->mw_breadcrumbs($product->getId(), '/', $hide_cat);
			$product_excerpt = $product->getExcerpt();
		}

		if ($product_excerpt) { ?>
			<p class="mws_product_excerpt">
			<?php echo $product_excerpt; ?>
			</p>
		<?php }
		if ($product->showSocial()) { ?>
			<div class="mws_product_socials">
				<div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="button_count" data-action="like"
					 data-show-faces="false" data-share="true"></div>
			</div>
		<?php } ?>

		<div class="mws_product_prices"><?php echo $product->htmlPriceSaleFull(null, $count); ?></div>
		<div class="mws_product_tocart">
			<?php
			mwsRenderParts('cart', 'action-add');
			if ($product->getSoldOutText() === '') {
				echo $product->htmlAvailabilityMessage($product->getAvailabilityStatus($count));
			}
			?>
		</div>

		<?php /*
		$orderedCount = $product->getOrderedCount();
		if($orderedCount) echo '<div class="mws_product_ordered_count">'.sprintf(__('Zakoupeno: %d×'), $orderedCount).'</div>';    */

		?>
	</div>
	<div class="cms_clear"></div>
</div>
