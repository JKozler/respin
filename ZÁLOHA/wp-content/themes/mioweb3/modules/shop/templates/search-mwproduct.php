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
get_footer('mwshop');
?>
