<?php

$object = mwSetting()->object();
if ($object) {
	echo $object->addGetToFilter(); // add $_GET parametres from url to filter

	echo $object->service()->printTitle();
	echo $object->service()->printListContent();
} else {
	echo mwSetting::message404(__('Tento objekt neexistuje. Stránku nelze načíst.', 'cms'));
}
