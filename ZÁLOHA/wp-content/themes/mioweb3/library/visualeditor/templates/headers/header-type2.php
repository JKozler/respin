<?php
global $vePage, $mwContainer;
$head_set = $mwContainer->list['headers'][$vePage->display->header_setting['appearance']];
if ($head_set['type'] == '2') {
	echo '<div id="header_in" class="fix_width">';
	$vePage->display->printLogo();
	do_action('mw_header_icon');
	echo '</div>';
	$vePage->display->header_menu();
} else {
	echo '<div class="header_nav_fullwidth_container">';
	echo '<div id="header_in" class="fix_width">';

	$vePage->display->header_menu();
	if ($head_set['type'] == '3') {
		do_action('mw_header_icon');
	}

	echo '</div>';
	echo '</div>';
}
