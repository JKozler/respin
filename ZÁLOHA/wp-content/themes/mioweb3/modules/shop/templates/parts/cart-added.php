<?php declare(strict_types=1);

$count = 1;
$cartItem = MWS()->current()->getCartItem();
if (empty($cartItem)) {
	$product = MWS()->current()->getProduct();
	if (empty($product)) {
		return;
	}
} else {
	$product = $cartItem->getProduct();
	$count = isset($_REQUEST['count']) ? (int) $_REQUEST['count'] : 1;
}

$title = $product->getName();
if ($count > 1) {
	$title = $count . ' x ' . $title;
}
?>

<div class="mws_product_card">
	<div class="mws_product_card_thumb responsive_image">
		<div class="mw_image_ratio mw_image_ratio_<?php echo MWS()->thumb_name; ?>"><?php echo $product->getThumbnail()->getImg('large', ['loading' => false]); ?></div>
	</div>
	<div class="mws_product_card_content">
		<div class="mws_product_card_title_container">
			<div class="mws_product_card_title"><?php echo $title; ?></div>
			<?php if (MWS()->current()->showAvailabilityInAdded()) {
				echo $product->htmlAvailabilityMessage(); // @TODO this show always only current state
			}
			?>
		</div>
		<div class="mws_product_card_price">
			<?php echo $product->getPrice()->htmlPriceVatIncluded($count); ?>
		</div>
	</div>
</div>
