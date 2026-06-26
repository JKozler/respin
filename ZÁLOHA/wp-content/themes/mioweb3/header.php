<?php
global $vePage; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo('charset'); ?>"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo $vePage->display->getTitle(); ?></title>

		<?php wp_head(); ?>

	</head>
<body <?php body_class(); ?>>
<?php MwCodes()->printBodyCodes(); ?>
<div id="wrapper">
<?php
$vePage->display->facebook_script();

echo '<header>';
echo $vePage->display->header_content;
echo '</header>';
