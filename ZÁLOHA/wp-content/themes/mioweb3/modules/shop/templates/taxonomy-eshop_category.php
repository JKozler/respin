<?php
/**
 * Template for product catalog = archive of shop's product custom type.
 */

get_header('mwshop');
get_blog_sidebar('mwshop');

global $vePage;

echo $vePage->display->create_content();

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
