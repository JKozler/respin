<?php
/**
 * Template part used to generate "REMOVE FROM CART" button.
 */

$product = MWS()->current()->getProduct();
if (!$product) {
	// No product specified.
	return;
}
$url = MWS()->getUrl_CartRemove($product->getId());
if (empty($url)) { ?>
	<span class="mws_config_error"><?php echo __('Košík není nakonfigurován.', 'mwshop'); ?></span>
	<?php

	return;
}
?>

<a href="#" class="mws_cart_remove shop-action-remove" title="<?php echo __('Odebrat zboží z košíku', 'mwshop'); ?>"
   data-operation="mws_cart_remove" data-product="<?php echo $product->getId(); ?>" data-count="1"
   data-backurl="<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>"
>
	<?php echo MWS()->getTemplateIcon('close'); ?>
</a>
