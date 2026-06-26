<?php
/**
 * Template part used in cart loop to print header for cart items.
 */

$cart = MWS()->getCart();

$discountCodes = MwsDiscountCode::getAll();
$price = $cart->getStoredTotalPrice();
$unit = $price !== null ? MwsCurrencyEnum::getSymbol($price->getCurrency()) : null;

if (count($discountCodes) > 0) {
	?>
	<tr class="mws_discount_code_row ve_nodisp">
		<td colspan="2"><?php echo __('Slevový kód', 'mwshop'); ?></td>
		<td colspan="3" class="mws_cart_item_price">
			<div class="mws_discount_code_form">
				<input type="text" name="discount_code" class="mws_cart_discount_code"
					   value="<?php echo $cart->getDiscountCode() !== null ? $cart->getDiscountCode()->getCode() : null; ?>"
					   autocomplete="off"
					   placeholder="<?php echo __('Zde vložte váš kód', 'mwshop'); ?>"/>
				<a class="mws_discount_code_reload eshop_color_background"
				   href="#"><span><?php echo __('Uplatnit slevový kód', 'mwshop'); ?></span></a>
			</div>
		</td>
	</tr>
	<?php
	if ($cart->getDiscountCode() !== null) {
		?>
		<tr class="mws_discount_code_row_added">
			<td colspan="3"><?php echo sprintf(__('Slevový kód %s byl uplatněn', 'mwshop'), '<strong>' . $cart->getDiscountCode()->getCode() . '</strong>'); ?></td>
			<td class="mws_cart_item_price">
				<div class="mws_discount_code_form">
					<span>-<?php echo $cart->getDiscountCode()->printValue($price->getCurrency()); ?></span>
				</div>
			</td>
			<td class="mws_cart_item_remove">
				<a href="#" class="mws_cart_remove shop-discount-code-remove"
				   title="<?php echo __('Odebrat slevový kód', 'mwshop'); ?>">
		<?php echo MWS()->getTemplateIcon('close'); ?>
				</a>
			</td>
		</tr>
		<?php
	}
}
?>


<tr class="mws_cart_items_footer">

	<td colspan="2"><?php echo __('Celkem', 'mwshop'); ?></td>
	<td colspan="3" class="mws_cart_item_price">
		<?php
		if (count($discountCodes) > 0) {
			?>
			<a class="mws_add_discount_code <?php if ($cart->getDiscountCode() !== null) { echo 've_nodisp'; } ?>"
			   href=""><?php echo __('Chci uplatnit slevový kód', 'mwshop'); ?></a>
			<?php
		}

		$price = $cart->getStoredTotalPrice();
		$unit = $price !== null ? MwsCurrencyEnum::getSymbol($price->getCurrency()) : null;
		if ($price !== null) {
			echo htmlPriceSimpleIncluded($price->getPriceVatIncluded(), $unit);
			echo htmlPriceSimpleExcluded($price->getPriceVatExcluded(), $unit);
		} else {
			echo __('(nutno přepočítat)', 'mwshop');
		}
		?>
	</td>
</tr>
