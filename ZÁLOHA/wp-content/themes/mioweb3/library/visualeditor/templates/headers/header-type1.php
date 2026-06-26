<?php
global $vePage;

echo '<div id="header_in" class="fix_width">';

$vePage->display->printLogo();
$vePage->display->header_menu();

do_action('mw_header_icon');

echo '</div>';
