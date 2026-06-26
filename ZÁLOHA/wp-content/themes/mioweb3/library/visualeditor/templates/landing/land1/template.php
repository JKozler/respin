<?php
/**
 * Template Title: Univerzální domovská stránka 1
 * Template Description: Univerzální domovská stránka se statickým pozadím a průhlednými řádky.
 */
__('Univerzální domovská stránka 1', 'cms_ve');
__('Univerzální domovská stránka se statickým pozadím a průhlednými řádky.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
