<?php
/**
 * Template for product catalog = archive of shop's product custom type.
 */

get_header('mwshop');

?>

<div class="mws_shop_container">
	<div class="mws_shop_content">


		<?php
		mwsRenderParts('product', 'loop');
		?>

	</div>
</div>

<?php

get_footer('mwshop');

?>
