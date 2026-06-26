<?php

$object = mwSetting()->object();
if ($object) {
	echo $object->service()->printTitle();
	echo $object->service()->printListContent();
} else {
	echo mwSetting::message404(__('Tento objekt neexistuje. Stránku nelze načíst.', 'cms'));
}
