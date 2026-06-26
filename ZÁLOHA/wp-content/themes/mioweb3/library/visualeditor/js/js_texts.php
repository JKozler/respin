<?php

return [
	// includes/coundown/jquery.countdown.js

	'countdown' => [
		'days' => __('dní', 'cms_ve'),
		'hours' => __('hodin', 'cms_ve'),
		'minutes' => __('minut', 'cms_ve'),
		'seconds' => __('sekund', 'cms_ve'),
	],

	// js/front.js

	'front' => [
		'required' => __('Musíte vyplnit všechny povinné údaje.', 'cms_ve'),
		'wrongemail' => __('Emailová adresa není platná. Zkontrolujte prosím zda je vyplněna správně.', 'cms_ve'),
		'wrongnumber' => __('Zadejte prosím platné číslo.', 'cms_ve'),
		'nosended' => __('Zpráva se nepodařila z neznámého důvodu odeslat.', 'cms_ve'),
		'wrongnumber2' => __('Zadejte prosím platné číslo. Správný formát telefonního čísla je např.: "+420733987123".', 'cms_ve'),
	],

	// js/pgb_editor.js

	'pgb_editor' => [
		'element_copy' => __('Tento element nelze kopírovat.', 'cms_ve'),
		'combine_subelements' => __('Tento element zde nelze umístit.', 'cms_ve'),
		'delete_element' => __('Opravdu chcete element smazat?', 'cms_ve'),
		'delete_row' => __('Opravdu chcete řádek smazat?', 'cms_ve'),
		'before_leave_page' => __('Opravdu chcete opustit tuto stránku? Vaše poslední úpravy nejsou uloženy a mohou být ztraceny.', 'cms_ve'),
		'ab_reset' => __('Opravdu chcete vynulovat výsledky?', 'cms_ve'),
		'ab_reset_but' => __('Vynulovat', 'cms_ve'),
		'ab_nodata' => __('Momentálně nejsou k dispozici žádná data.', 'cms_ve'),
		'menu_conflict' => __('Menu s tímto jménem již existuje. Zadejte jiné jméno.', 'cms_ve'),
		'delete_menu_confirm' => __('Opravdu chcete toto menu smazat?', 'cms_ve'),
		'delete_item_confirm' => __('Opravdu chcete tuto položku smazat?', 'cms_ve'),
		'delete_bulk_confirm' => __('Opravdu chcete vybrané položky smazat?', 'cms_ve'),
		'delete_page_confirm' => __('Opravdu chcete tuto stránku smazat?', 'cms_ve'),
		'delete_button_confirm' => __('Opravdu chcete tlačítko smazat?', 'cms_ve'),
		'storno_save_page_info' => __('Stránku se nepodařilo uložit. Pravděpodobně došlo k chybě v připojení. Zkuste to prosím znovu.', 'cms_ve'),
		'change_theme_confirm' => __('Změna šablony přepíše také obsah. Chcete pokračovat?', 'cms_ve'),
		'create_page_required' => __('Musíte vyplnit všechny povinné údaje.', 'cms_ve'),
		'create_page_url' => __('Stránka s touto URL už existuje. Zkuste zadat jinou URL stránky.', 'cms_ve'),
		'required' => __('Musíte vyplnit všechny povinné údaje.', 'cms_ve'),
		'continue' => __('Pokračovat', 'cms_ve'),
		'delete' => __('Smazat', 'cms_ve'),
		'storno' => __('Zrušit', 'cms_ve'),
		'save' => __('Uložit', 'cms_ve'),
		'create_menu' => __('Vytvořit menu', 'cms_ve'),
		'close' => __('Zavřít', 'cms_ve'),
		'connect' => __('Propojit', 'cms_ve'),
		'close_confirm' => __('Vaše poslední úpravy nejsou uloženy a mohou být ztraceny. Opravdu chcete pokračovat?', 'cms_ve'),
		'confirm_install' => __('Opravdu chcete tento web nainstalovat? Instalace přepíše některé z vašich nastavení!', 'cms_ve'),
		'weburl' => get_bloginfo('template_url'),
		'loading_icons_url' => MW_UI_ICONS_URL,
		'lightbox_help_link_title' => __('Nápověda', 'cms_ve'),
		'url_to_clipboard' => __('URL byla zkopírována do schránky.', 'cms_ve'),

		'empty_trash_confirm' => __('Opravdu chcete vysypat koš?', 'cms_ve'),
		'empty_trash' => __('Vysypat koš', 'cms_ve'),

		'export' => __('Exportovat', 'cms_ve'),

		'tohandle_order' => __('Vyřídit objednávku', 'cms_ve'),
		'tohandle_packeta' => __('Vytvořit zásilku', 'cms_ve'),
		'tohandle_packeta_print' => __('Tisk zásilky', 'cms_ve'),
		'actualize' => __('Aktualizovat', 'cms_ve'),
		'statistics_reset_confirm' => __('Opravdu chcete vynulovat statistiky pro tento funnel?', 'cms_ve'),
		'processing' => __('Zpracování', 'cms_ve'),
		'processed' => __('Zpracováno', 'cms_ve'),
		'tus_error' => __('Došlo k blíže nespecifikované chybě, aktualizujte stránku a zkuste nahrání opakovat.', 'cms_ve'),
		'tus_unauthorized' => __('Došlo k chybě při autorizaci, aktualizujte stránku a zkuste nahrání opakovat.', 'cms_ve'),
		'tus_forbidden_file_type' => __('Nepovolený typ souboru.', 'cms_ve'),

		// comments
		'comment_save_reply' => __('Uložit odpověď', 'cms_ve'),
		'comment_reply_title' => __('Odpovědět na komentář', 'cms_ve'),

		'email_refresh' => __('Obnovit', 'cms_ve'),
		'email_refresh_info' => __('Opravdu chcete nastavit u tohoto emailu původní text? Aktuální data budou ztracena.', 'cms_ve'),
		// archive order
		'archive_order' => __('Opravdu chcete archivovat objednávku?', 'mwshop'),
		'archive_order_remove' => __('Opravdu chcete zrušit archivaci objednávky?', 'mwshop'),
	],


];
