<?php
/**
 * Template for single product.
 */

global $post, $vePage;

get_header('mwshop');
get_blog_sidebar('mwshop');

wp_enqueue_script('ve_lightbox_script');
wp_enqueue_style('ve_lightbox_style');

$product = MwsProduct::createNew($post);

if (isset($_GET['variant']) && ($variant = $product->getVariants()[$_GET['variant']] ?? null)) {
	$product = $variant;
}

MWS()->current()->setProduct($product);
?>
<div class="mws_shop_container mws_single_product_container mw_transparent_header_padding">
	<div class="mws_shop_content row_fix_width">
		<?php
		mwsRenderParts('product', 'detail');
		?>
		<div class="mws_product_tabs mw_tabs_element_style_3">
			<?php

			$group = 'mw_product_' . $post->ID;

			$tabs = [];
			if (MWS()->edit_mode || !empty($vePage->display->layer)) {
				$tabs['description'] = __('Popis', 'mwshop');
			}
			if (count($product->getProperties()) && $product->getStructure() !== MwsProductStructureType::OneVariant) {
				$tabs['properties'] = __('Parametry', 'mwshop');
			}
			if (!$product->hideComments()) {
				$tabs['discusion'] = __('Diskuse', 'mwshop');
				if (get_comments_number($post->ID)) {
					$tabs['discusion'] .= ' (' . get_comments_number($post->ID) . ')';
				}
			}

			if (!empty($tabs)) {
				?>
			<ul class="mw_tabs mw_tabs_<?php echo $group; ?>">
				<?php
				$i = 0;
				foreach ($tabs as $tab_id => $tab_name) {
					echo '<li><a href="#mws_product_' . $tab_id . '" data-group="' . $group . '" ' . ($i == 0 ? 'class="active"' : '') . '>' . $tab_name . '</a></li>';
					$i++;
				}
				?>
			</ul>

			<ul class="mw_tabs_container <?php echo $group; ?>_container">
				<?php
				if (isset($tabs['description'])) { ?>
					<li id="mws_product_description">
					<?php
					echo $vePage->display->printContent();
					?>
					</li>

					<?php
				}
				// product properties tab
				if (isset($tabs['properties'])) { ?>
					<li id="mws_product_properties">

						<table class="mws_prodcut_properties_table mw_table mw_table_style_3">
					<?php
					foreach ($product->getProperties() as $propValue) {
						echo '
									<tr>
											<th>
													' . $propValue->getProperty()->getName() . '
													' . ($propValue->getProperty()->getExcerpt() ? '<span class="mws_property_info">(<a href="" title="' . $propValue->getProperty()->getExcerpt() . '" data-property="' . $propValue->getProperty()->getName() . '">?</a>)</span>' : '') . '
											</th>
											<td>' . $propValue->getName() . ' ' . $propValue->getProperty()->getUnit() . '</td>
									</tr>';
					}
					?>
						</table>
					</li>
					<?php
				}
				if (isset($tabs['discusion'])) {
					?>

					<li id="mws_product_discusion">
					<?php
					echo '<div class="element_comment_1 blog_comments">';
					comments_template('/comments.php');
					echo '</div>'; ?>
					</li>

					<?php
				}
			}
			?>
			</ul>
		</div>

		<?php
		if ($product->showSimilar()) {
			$query = [];

			// remove invisile
			$invisibleIds = MwsProduct::getInvisibleProducts(true);
			$invisibleIds[] = $product->getId();
			if ($product->getSimilarProductsShowType() == 'custom') {
				foreach ($product->getSimilarProducts() as $sim_product) {
					if (!in_array($sim_product['product_id'], $invisibleIds)) {
						$query[] = MwsProduct::createNew(get_post($sim_product['product_id']));
					}
				}
			} else {
				$cats = [];

				$categories = get_the_terms($product->getId(), MWS_PRODUCT_CAT_SLUG);
				if (!empty($categories)) {
					foreach ($categories as $c) {
						$cats[] = $c->term_id;
					}
					$args = [
						'tax_query' => [
							[
								'taxonomy' => MWS_PRODUCT_CAT_SLUG,
								'field' => 'term_id',
								'terms' => $cats,
							],
						],
					];

					$args['post__not_in'] = $invisibleIds;

					$query = MwsProduct::getAll($args, false);
				}
			}

			if (count($query)) {
				$cols = MWS()->visual_setting['cols'];
				$style = MWS()->visual_setting['product_style'];
				if ($vePage->display->is_mobile || $style == 'pre2') {
					$cols = 1;
				}

				$setting = [];

				if (count($query) > $cols) {
					wp_enqueue_script('ve_miocarousel_script');
					wp_enqueue_style('ve_miocarousel_style');

					$setting = [
						'use_slider' => true,
						'miocarousel_setting' => [
							'animation' => 'fade',
							'color_scheme' => 'dark',
							'delay' => '2500',
							'speed' => '1000',
						],

					];
				}

				echo '<div class="mws_similar_products_container">';
				echo '<h2 class="mws_product_detail_title">' . __('Podobné zboží', 'mwshop') . '</h2>';
				echo '<div class="mws_product_list">';

				echo MWS()->writeProducts($query, $cols, $style, $setting);

				echo '</div>';
				echo '</div>';
			}
		}
		?>

	</div>
</div>
<?php

get_footer('mwshop');

?>
