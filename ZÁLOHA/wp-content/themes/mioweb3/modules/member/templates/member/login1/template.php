<?php
/**
 * Template Title: Přihlašovací stránka 1
 * Template Description: Jednoduchá přihlašovací stránka s obrázkem na pozadí.
 */
__('Přihlašovací stránka 1', 'cms_member');
__('Jednoduchá přihlašovací stránka s obrázkem na pozadí.', 'cms_member');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
