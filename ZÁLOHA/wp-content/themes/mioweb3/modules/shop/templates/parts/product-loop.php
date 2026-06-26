<?php
/**
 * Template part used in product loop. It generates content for one product within product catalog.
 * Posts in global query are product to be shown.
 */

?>
<div
	class="mws_product_list mws_product_list_main mws_product_list_<?php echo MWS()->visual_setting['product_style'] ?>">
	<?php


	if (have_posts()) {
		$cols = MWS()->visual_setting['cols'];

		global $wp_query;
		$products = array_map(function (WP_Post $post) {
			return MwsProduct::createNew($post);
		}, $wp_query->posts);
		echo MWS()->writeProducts($products, $cols, MWS()->visual_setting['product_style']);

		/*
		if(is_tax(MWS_PRODUCT_CAT_SLUG)) {
		$cat = get_queried_object();
		$terms = get_terms(MWS_PRODUCT_CAT_SLUG,array('orderby' => 'count', 'hide_empty' => false, 'order' => 'DESC', 'pad_counts' => true));
		foreach($terms as $term) {
		if($term->term_id==$cat->term_id) $count=$term->count;
		}
		} else {
		$count=$wp_query->found_posts;
		} */

		$count = $wp_query->found_posts;

		$per_page = intval(MWS()->visual_setting['per_page']);
		if (!$per_page) {
			$per_page = 16;
		}

		$total = ceil($count / $per_page);

		if ($total > 1) {
			if (!$current_page = get_query_var('paged')) {
				$current_page = 1;
			}

			$format = get_option('permalink_structure') ? 'page/%#%/' : '?paged=%#%';
			$pagination = paginate_links([
				'format' => $format,
				'current' => $current_page,
				'total' => $total,
				'show_all' => false,
				'type' => 'plain',
				'prev_text' => mw_content_icon_set('chevron-left'),
				'next_text' => mw_content_icon_set('chevron-right'),
			]);

			echo '<div class="mw_page_navigation mw_page_navigation_1">' . $pagination . '</div>';
		}
	} elseif (isset($_GET['search_product'])) { ?>
		<div class="mws_product_list_empty">
		<?php echo __('Hledanému řetězci neodpovídá žádný produkt.', 'mwshop'); ?>
		</div>
	<?php } else { ?>
		<div class="mws_product_list_empty">
			<span><?php echo __('V této kategorii se nenachází žádný produkt.', 'mwshop'); ?></span>
		<?php
		if (MWS()->edit_mode) {
			echo '<a target="_blank" title="' . __('Přidat produkt', 'mwshop') . '" href="' . mwSetting()->getObject('mwproduct')->getAddUrl() . '">' . __('Přidat produkt', 'mwshop') . '</a>';
		}
		?>
		</div>
		<?php
	}
	wp_reset_query();
	wp_reset_postdata();

	?>
</div>
