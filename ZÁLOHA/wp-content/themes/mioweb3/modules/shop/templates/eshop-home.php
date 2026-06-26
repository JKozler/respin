<?php
/**
 * Template for product catalog = archive of shop's product custom type.
 */

get_header('mwshop');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}

if (!isset(MWS()->visual_setting['hide_home_product_list'])) {
	// $paged=get_query_var( 'paged', 1 ); dont work on home page
	$paged = $wp_query->query['paged'] ?? 1;

	$args = ['post_type' => MWS_PRODUCT_SLUG, 'paged' => $paged];
	query_posts($args);


	?>

	<div class="mws_shop_container">
		<div class="mws_shop_content row_fix_width">

	<?php

	mwsRenderParts('categories');
	mwsRenderParts('product', 'loop');

	?>

		</div>
	</div>

	<?php
}
get_footer('mwshop');

?>
