<?php
/**
 * Template Title: Děkovací stránka s fotkou
 * Template Description: Dvousloupcová děkovací stránka s fotkou bez možnosti přidávat další řádky.
 */
__('Děkovací stránka s fotkou', 'cms_ve');
if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
