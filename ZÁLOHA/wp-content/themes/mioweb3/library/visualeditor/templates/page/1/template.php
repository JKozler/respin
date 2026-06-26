<?php
/**
 * Template Title:Prázdná stránka
 * Template Description: Prázdná stránka bez okrajů, vhodná pro výstavbu vlastních stránek.
 */
__('Prázdná stránka', 'cms_ve');
__('Prázdná stránka bez okrajů, vhodná pro výstavbu vlastních stránek.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
