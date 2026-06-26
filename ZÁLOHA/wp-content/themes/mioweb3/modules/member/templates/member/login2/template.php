<?php
/**
 * Template Title: Přihlašovací stránka 2
 * Template Description: Přihlašovací stránka s formulářem na straně.
 */
__('Přihlašovací stránka 2', 'cms_member');
__('Přihlašovací stránka s formulářem na straně.', 'cms_member');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
