<?php global $vePage, $menu; ?>

<div id="footer-in" class="footer-in fix_width <?php if ($menu) {
	echo 'footer_with_menu';
											   } ?>">
	<?php
	//$menu=(isset($vePage->display->footer_setting['menu'])) ? $vePage->display->footer_setting['menu'] : '';
	$vePage->display->footer_menu($menu);
	?>
	<div
		id="site_copyright"><?php echo isset($vePage->display->footer_setting['text']) && $vePage->display->footer_setting['text'] ? str_replace('{current_year}', date('Y'), stripslashes($vePage->display->footer_setting['text'])) : '&copy; ' . date('Y') . ' ' . get_bloginfo('name'); ?></div>

	<?php
	$aff = get_option('web_option_affiliate');
	if (isset($aff['affiliate_link']) && $aff['affiliate_link'] != '') {
		$aff_link = add_query_arg('utm_campaign', 'mioweb_footer', $aff['affiliate_link']);
		?>
		<div id="site_poweredby">
		<?php echo __('Vytvořeno na platformě', 'cms_ve') . ' <a target="_blank" href="' . $aff_link . '">Mioweb</a>'; ?>
		</div>
	<?php } ?>
	<div class="cms_clear"></div>
</div>
