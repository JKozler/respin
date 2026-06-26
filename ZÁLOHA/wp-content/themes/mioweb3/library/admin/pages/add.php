<?php
$object = mwSetting()->object();

echo $object->service()->printTitle();

echo '<form action="" class="mw_setting_form">';

$copyItemId = $_GET['copy'] ?? '';
$item = $object->service()->getItem($copyItemId);

$object->service()->printForm($item, true);

$object->service()->printAddBar();

echo '</form>';
