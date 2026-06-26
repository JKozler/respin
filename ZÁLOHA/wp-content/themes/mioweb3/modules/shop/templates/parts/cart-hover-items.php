<?php
/**
 * Template part used in cart info in header.
 * Content of the cart item is stored in "MWS()->current()->getCartItem()".
 */

$cartItem = MWS()->current()->getCartItem();
if (empty($cartItem)) {
	return;
}

$product = $cartItem->getProduct();
echo '<tr id="mws_product_id-' . $product->getId() . '">'; //mws_product_item_'.$product->getId().'
echo '<td class="mws_product_thumb responsive_image"><a href="' . $product->getDetailUrl() . '"><div class="mw_image_ratio mw_image_ratio_' . MWS()->thumb_name . '">' . $product->getThumbnail()->getImg('large') . '</div></a></td>';
echo '<td class="mws_cart_item_title"><a href="' . $product->getDetailUrl() . '">' . $cartItem->getCount() . ' x ' . $product->getName() . '</a></td>';
echo '<td class="mws_cart_item_price">' . $product->getPrice()->htmlPriceVatIncluded($cartItem->getCount()) . '</td>';
echo '</tr>';
