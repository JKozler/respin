<?php
$currentPage = mwSetting()->page();

$currentPage->printTitle();

echo '<form method="post" action="" class="mw_setting_form">';

$currentPage->printForm();

$currentPage->printSaveBar();

echo '</form>';
