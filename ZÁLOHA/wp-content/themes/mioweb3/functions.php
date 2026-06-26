<?php
if (defined('MW_LOAD') && !MW_LOAD) {
	return;
}

require_once(TEMPLATEPATH . '/library/init.php');
require_once(TEMPLATEPATH . '/library/visualeditor/init.php');
MW()->add_module('funnels');
MW()->add_module('mioweb');
MW()->add_module('shop');
mwApiConnect()->getApi('google_analytics')->client(); // Register GA event subscriber (must be after "shop" init)
MW()->add_module('member');
MW()->add_module('blog');

MW()->create_init();
