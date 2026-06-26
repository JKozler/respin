<?php
/**
 * Template part used in cart loop. It shows items within cart.
 */

$cart = MWS()->getCart();


echo '<div class="mws_cart_empty_info">' . __('Košík je prázdný', 'mwshop') . '</div>';
if (!$cart->getItems()->isEmpty()) {
	if (!$cart->isRecounted()) {
		echo '<div class="mws_error">';
		echo empty($cart->getRecountError())
		? __('Omlouváme se, nepodařilo se spočítat cenu košíku. Proveďte přepočítání.', 'mwshop')
		: $cart->getRecountError();
		if ($cart->getAvailabilityErrorsCount()) {
			echo ' ' . __('Upravte obsah košíku.', 'mwshop');
		}
		if (MWS()->edit_mode && !empty($cart->getRecountAdminError())) {
			echo '<small>' . __('Technický popis chyby:', 'mwshop') . ' <span>' . $cart->getRecountAdminError() . '</span></small>';
		}
		echo '</div>';
	}
	echo '<table class="mws_cart mws_cart_filled">';
	/** @var MwsCartItem $cartItem */
	foreach ($cart->getItems()->getAll() as $cartItem) {
		MWS()->current()->setCartItem($cartItem);
		mwsRenderParts('cart', 'items-line');
	}
	mwsRenderParts('cart', 'items-footer');
	echo '</table>';
}
