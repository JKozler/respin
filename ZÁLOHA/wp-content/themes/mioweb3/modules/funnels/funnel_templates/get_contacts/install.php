<?php

return [
	'title' => __('Získávání kontaktů', 'mw_funnels'),
	'desc' => __('Získejte nové prodejní příležitosti díky rozšiřování své e-mailové databáze.', 'mw_funnels'),
	'icon' => 'mail',
	'items' => [
		[
			'title' => __('Vstupní stránka', 'mw_funnels'),
			'type' => 'squeeze',
			'page' => 'squeeze',
		],
		[
			'title' => __('Děkovací stránka', 'mw_funnels'),
			'type' => 'thanks',
			'page' => 'thanks',
			'nav_hide' => 1,
		],
	],
	'setting' => [
		'show_sell' => 0,
	],
];
