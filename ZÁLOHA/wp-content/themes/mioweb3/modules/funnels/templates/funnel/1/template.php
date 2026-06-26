<?php
/**
 * Template Title: Dvousloupcová video stránka
 * Template Description: Video stránka s dvousloupcovým obsahem a s menu vedle videa.
 */
__('Dvousloupcová video stránka', 'mw_funnels');
__('Video stránka s dvousloupcovým obsahem a s menu vedle videa.', 'mw_funnels');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
