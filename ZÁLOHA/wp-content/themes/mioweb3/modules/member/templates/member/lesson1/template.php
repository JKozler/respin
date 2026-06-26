<?php
/**
 * Template Title:Stránka lekce
 * Template Description:Stránka lekce s výpisem dalších lekcí na levé straně.
 */
__('Stránka lekce', 'cms_member');
__('Stránka lekce s výpisem dalších lekcí na levé straně.', 'cms_member');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
