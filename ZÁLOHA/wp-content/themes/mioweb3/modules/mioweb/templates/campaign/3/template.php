<?php
/**
 * Template Title: Video stránka s menu nahoře
 * Template Description: Video stránka s dvousloupcovým obsahem a s menu vedle videa.
 */
__('Video stránka s menu nahoře', 'cms_mioweb');
__('Video stránka s dvousloupcovým obsahem a s menu vedle videa.', 'cms_mioweb');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
