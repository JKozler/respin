<?php
/**
 * Template part used in cart loop to print one item within the cart.
 * Content of the cart item is stored in "MWS()->current()->getCartItem()".
 */

$cartItem = MWS()->current()->getCartItem();
if (!$cartItem) {
	return;
}
$product = $cartItem->getProduct();
$countInCart = $cartItem->getCount();
$status = $cartItem->getAvailabilityStatus();// @TODO $product->getAvailabilityStatus($countInCart); ??
$canBuy = $product->canBuy($status);
$isElectronic = MwsProductType::isElectronic($product->getType())
?>

<tr class="mws_cart_item mws_product_id-<?php echo $product->getId() . ' ' . $product->getAvailabilityCSS($status); ?>">
	<td class="mws_cart_item_thumb"><a class="responsive_image" href="<?php echo $product->getDetailUrl(); ?>">
			<div class="mw_image_ratio mw_image_ratio_<?php echo MWS()->thumb_name; ?>">
				<?php
				echo $product->getThumbnail()->printImg([
					'max_width' => 171,
				]);
				?>
			</div>
		</a>
	</td>
	<td class="mws_cart_item_title"><h2><a
				href="<?php echo $product->getDetailUrl(); ?>"><?php echo $product->getName(); ?></a></h2></td>
	<td class="mws_cart_item_count">
		<div class="mws_count_container">
			<?php if (!$isElectronic) {
			echo '<input type="text" name="count[' . $product->getId() . ']" class="mws_cart_edit_count" value="' . $cartItem->getCount() . '" placeholder="?"/>
				  <a class="mws_count_reload eshop_color_svg_hover" href="#">' . MWS()->getTemplateIcon('reload') . '</a>';
			}
			?>
		</div>
<?php
if (!$canBuy) {
	// Product can not be bought in specified amount.
	$errorMsg = $cartItem->getAvailabilityError();
	echo '<span class="mw_input_error_text">' . $errorMsg . '</span>';
}
?>
	</td>
	<td class="mws_cart_item_price">
<?php
$price = $cartItem->getStoredShopPrice();

if ($price === null) {
	echo __('(neznámo)', 'mwshop');
} else {
	echo $price->htmlPriceFull('mws_product_price', $cartItem->getCount());

	$discountCode = MWS()->getCart()->getDiscountCode();
	if ($discountCode && $discountCode->getType() != MwsDiscountCodeType::Fixed) {
		echo '<div class="mws_discount_code_price_info">';
		if ($product->isDiscountDisabled()) {
			echo __('Na tento produkt nelze uplatnit slevový kód.', 'mwshop');
		} else {
			echo __('Po uplatnění slevového kódu:', 'mwshop') . ' <strong>' . htmlPriceSimple($cartItem->getStoredTotalPrice()->getPriceVatIncluded(), MwsCurrencyEnum::getSymbol($cartItem->getStoredTotalPrice()->getCurrency()), false) . '</strong>';
			echo '<input type="hidden" name="priceVatIncluded[' . $product->getId() . ']" value="' . $price->getPriceVatIncluded() . '" />';
		}
		echo '</div>';
	}
}
?>
	</td>
	<td class="mws_cart_item_remove">
<?php
MWS()->current()->setProduct($product);
mwsRenderParts('cart', 'action-remove');
?>
	</td>
</tr>
