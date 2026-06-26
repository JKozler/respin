<?php
/**
 * Template Title: Vysílání webináře 1
 * Template Description: Stránka pro živé vysílání webináře.
 */
__('Vysílání webináře 1', 'cms_ve');
__('Stránka pro živé vysílání webináře.', 'cms_ve');
if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
