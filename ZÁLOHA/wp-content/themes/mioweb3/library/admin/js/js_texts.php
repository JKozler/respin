<?php

return [

	// library/admin/js/admin.js

	'admin' => [
		'image_gallery_delete_image' => __('Odstranit obrázek', 'cms'),
		'image_gallery_edit_image' => __('Upravit obrázek', 'cms'),
		'image_gallery__image_detail__heading' => __('Upravit informace o obrázku', 'cms'),
		'delete_confirm' => __('Opravdu chcete tuto položku smazat?', 'cms'),
		'default_confirm' => __('Opravdu chcete tuto položku nastavit jako defaultní? Změna defaultní měny může mít značný dopad, například na počítání statistik. Přistupujte k tomuto kroku prosím uvážlivě.', 'cms'),
		'paygate_confirm' => __('Opravdu chcete změnit způsob zpracování plateb a fakturace? Tato změna může mít značný dopad na nastavení a fungování eshopu. Přistupujte k tomuto kroku prosím uvážlivě.', 'cms'),
		'siteurl' => site_url(),
	],

	// library/includes/cms_lightbox/lightbox.js - old lightbox
	/*
	'lightbox'=>array(
	   'save'=>__( 'Uložit', 'cms' ),
	   'back'=>__( 'Zpět', 'cms' ),
	   'storno'=>__( 'Storno', 'cms' ),
	   'close_confirm'=>__( 'Opravdu chcete zavřít editor? Vaše poslední úpravy nejsou uloženy a mohou být ztraceny.', 'cms' ),
	   'required'=>__( 'Musíte vyplnit všechny povinné údaje.', 'cms' ),
	),*/

	// library/includes/datepicker/jquery.ui.datepicker-cs.js

	'datepicker' => [
		'close' => __('Zavřít', 'cms'),
		'sooner' => __('Dříve', 'cms'),
		'later' => __('Později', 'cms'),
		'now' => __('Nyní', 'cms'),

		'january' => __('leden', 'cms'),
		'february' => __('únor', 'cms'),
		'march' => __('březen', 'cms'),
		'april' => __('duben', 'cms'),
		'may' => __('květen', 'cms'),
		'june' => __('červen', 'cms'),
		'july' => __('červenec', 'cms'),
		'august' => __('srpen', 'cms'),
		'september' => __('září', 'cms'),
		'october' => __('říjen', 'cms'),
		'november' => __('listopad', 'cms'),
		'december' => __('prosinec', 'cms'),

		'jan' => __('led', 'cms'),
		'feb' => __('úno', 'cms'),
		'mar' => __('bře', 'cms'),
		'apr' => __('dub', 'cms'),
		'ma' => __('kvě', 'cms'),
		'jun' => __('čer', 'cms'),
		'jul' => __('čvc', 'cms'),
		'aug' => __('srp', 'cms'),
		'sep' => __('zář', 'cms'),
		'oct' => __('říj', 'cms'),
		'nov' => __('lis', 'cms'),
		'dec' => __('pro', 'cms'),

		'sunday' => __('neděle', 'cms'),
		'monday' => __('pondělí', 'cms'),
		'tuesday' => __('úterý', 'cms'),
		'wednesday' => __('středa', 'cms'),
		'thursday' => __('čtvrtek', 'cms'),
		'friday' => __('pátek', 'cms'),
		'saturday' => __('sobota', 'cms'),

		'su' => __('ne', 'cms'),
		'mo' => __('po', 'cms'),
		'tu' => __('út', 'cms'),
		'we' => __('st', 'cms'),
		'th' => __('čt', 'cms'),
		'fr' => __('pá', 'cms'),
		'sa' => __('so', 'cms'),

		'week_short' => __('Týd', 'cms'),
		'date_format' => __('dd.mm.yy', 'cms'),
	],

];
