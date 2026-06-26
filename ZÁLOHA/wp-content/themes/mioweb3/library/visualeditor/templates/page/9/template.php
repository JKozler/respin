<?php
/**
 * Template Title: Stránka s ceníkem
 * Template Description: Stránka s ceníkem a často kladenými dotazy.
 */
__('Stránka s ceníkem', 'cms_ve');
__('Stránka s ceníkem a často kladenými dotazy.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
