<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?php echo mwSetting()->getPageTitle(); ?></title>

	<?php
	global $vePage;
	$vePage->display->printFavicon();
	wp_head();
	?>

</head>
<body class="mw_setting_body <?php echo mwSetting()->getBodyClass(); ?>" data-modified="0">
	<?php
	if (MW()->getLicense()->isExpired()) {
		MW()->getLicense()->generateExpiredInfo();
	} else {
		mwSetting()->printLeftBar();
		mwSetting()->printSettingMenu();
		mwSetting()->printSettingPage();
		wp_footer();
	}
	?>
</body>
</html>
