<?php
/**
 * Template Title: Upsell stránka
 * Template Description: Stránka pro nabídku upsellu po vyplnění objednávkového formuláře
 */
__('Upsell stránka', 'cms_ve');
__('Stránka pro nabídku upsellu po vyplnění objednávkového formuláře', 'cms_ve');
if (have_posts()) {
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
}
