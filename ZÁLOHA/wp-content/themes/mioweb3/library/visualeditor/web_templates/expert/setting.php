<?php
$web_setting = [
	've_header' => [
		'show' => 'page',
		'appearance' => 'type1',
		'logo' => get_template_url_image() . '/library/visualeditor/images/default/logo2.png',
		'before_header' => '',
		'background_color' => [
			'color1' => '',
			'color2' => '',
			'transparency' => '100',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
		],
		'menu' => $installed_menus['main_header'],
		'menu_font' => [
			'font-size' => '15',
			'font-family' => '',
			'weight' => '',
			'color' => $color_set['bg_text'],
		],
		'menu_active_color' => '#ffffff',
		'menu_submenu_text_color' => '#ffffff',
		'menu_bg' => [
			'color1' => '#121212',
			'color2' => '',
		],
		'header_width' => [
			'size' => '',
			'unit' => 'px',
		],
	],

	've_footer' => [
		'show' => 'page',
		'custom_footer' => $installed_contents['footer'],
		'appearance' => 'type2',
		'text' => '',
		'menu' => '',
		'background_color' => [
			'color1' => '#181a1a',
			'color2' => '',
			'transparency' => '100',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
		],
		'font' => [
			'font-size' => '14',
			'font-family' => '',
			'weight' => '',
			'color' => '#a8a8a8',
		],
		'footer_width' => [
			'size' => '',
			'unit' => 'px',
		],
	],

	've_appearance' => [
		'page_width' => [
			'size' => '',
			'unit' => 'px',
		],
		'background_color' => $color_set['background'],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'cover' => 'cover',
			'image' => '',
			'pattern' => '0',
		],
		'background_video_mp4' => '',
		'background_video_webm' => '',
		'background_video_ogg' => '',
		'font' => [
			'font-size' => '16',
			'font-family' => 'Open Sans',
			'weight' => '400',
			'line-height' => '1.5',
			'color' => '',
		],
		'link_color' => '',
		'title_font' => [
			'font-family' => 'Open Sans',
			'weight' => '400',
			'color' => '',
		],
		'h1_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h2_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h3_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h4_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h5_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h6_font' => [
			'font-size' => '',
			'color' => '',
		],
		'li' => '1',
	],

	'blog_comments' => [
		'comments' => [
			'wordpress' => 'wordpress',
			'facebook' => 'facebook',
		],
		'comments_order' => 'facebook',
		'show_share' => [
			'facebook' => 'facebook',
			'facebook_share' => 'facebook_share',
			'twitter' => 'twitter',
			'google' => 'google',
		],
		'show_share_list' => [
			'facebook' => 'facebook',
			'facebook_share' => 'facebook_share',
		],
	],

	'blog_sidebars' => [
		'sidebar_blog' => $installed_sidebars['main'],
		'sidebar_category' => $installed_sidebars['main'],
		'sidebar_post' => $installed_sidebars['main'],
		'sidebar_author' => $installed_sidebars['main'],
		'sidebar_tag' => $installed_sidebars['main'],
		'sidebar_search' => $installed_sidebars['main'],
	],

	'blog_appearance' => [
		'appearance' => 'style1',
		'structure' => 'right',
		'post_look' => '1',
		'background_color' => $color_set['background'],
		'background_image' => [
			'position' => 'center bottom',
			'repeat' => 'no-repeat',
			'image' => '',
			'pattern' => '0',
		],
		'font' => [
			'font-size' => '14',
			'font-family' => 'Open Sans',
			'weight' => '400',
			'line-height' => '',
			'color' => '#111111',
		],
		'link_color' => $color_set['background'],
		'title_font' => [
			'font-family' => 'Open Sans',
			'weight' => '600',
			'color' => '',
		],
		'tb_background' => [
			'color1' => $color_set['background'],
			'color2' => '',
		],
		'tb_font' => [
			'font-size' => '35',
			'font-family' => '',
			'weight' => '',
			'line-height' => '',
			'color' => '#ffffff',
		],
		'article_font' => [
			'font-size' => '35',
			'font-family' => '',
			'weight' => '',
			'line-height' => '',
			'color' => '',
		],
		'article_font_text' => [
			'font-size' => '',
			'font-family' => '',
			'weight' => '',
			'line-height' => '',
			'color' => '',
		],
		'sidebar_font' => [
			'font-size' => '20',
			'font-family' => '',
			'weight' => '',
			'line-height' => '',
			'color' => '',
		],
		'h1_font' => [
			'font-size' => '30',
			'color' => '',
		],
		'h2_font' => [
			'font-size' => '23',
			'color' => '',
		],
		'h3_font' => [
			'font-size' => '18',
			'color' => '',
		],
		'h4_font' => [
			'font-size' => '14',
			'color' => '',
		],
		'h5_font' => [
			'font-size' => '14',
			'color' => '',
		],
		'h6_font' => [
			'font-size' => '14',
			'color' => '',
		],
		'li' => '1',
	],

	'blog_footer' => [
		'show' => 'blog',
		'custom_footer' => '',
		'appearance' => 'type2',
		'text' => '',
		'menu' => '',
		'background_color' => [
			'color1' => '',
			'color2' => '',
			'transparency' => '100',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
		],
		'font' => [
			'font-size' => '13',
			'font-family' => '',
			'weight' => '',
			'color' => $color_set['bg_text'],
		],
		'footer_width' => [
			'size' => '',
			'unit' => 'px',
		],
	],
	'blog_header' => [
		'show' => 'global',
	],
];
