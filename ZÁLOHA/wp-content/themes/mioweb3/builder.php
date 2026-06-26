<?php

global $vePage; ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?php echo $vePage->display->getTitle(); ?></title>

	<?php
	$vePage->display->printFavicon();
	wp_head();
	?>

</head>
<body <?php body_class(); ?>>
<?php
if (MW()->getLicense()->isExpired())
{
	MW()->getLicense()->generateExpiredInfo();
}
elseif(!MW()->isWebInstalled())
{
	MwWebInstall()->webInstaller();
	wp_footer();
}
else
{
	?>
	<div class="mw_page_builder mw_page_builder_busy mw_page_builder_loading <?php echo ($vePage->window_editor) ? 'mw_page_builder_window' : ''; ?>" data-editable="<?php echo ($vePage->builder->is_editable($vePage->page_type)) ? '1' : '0'; ?>">
	<?php

	if ($vePage->window_editor) {
		$vePage->builder->editor_top_panel($vePage->page_type, $vePage->modul_type, true);
		$vePage->builder->editor_panel($vePage->object_id, $vePage->page_type, $vePage->modul_type, $vePage->window_editor_setting, true);
	} else {
		$vePage->builder->editor_top_panel($vePage->page_type, $vePage->modul_type);
		$vePage->builder->editor_panel($vePage->object_id, $vePage->page_type, $vePage->modul_type);
		mwPageSelector()->body($vePage->modul_type);
	}

	?>
		<div class="mw_page_builder_container">
			<div class="mw_device_preview_container mw_device_preview-desktop">
				<iframe id="mw_page_builder"
						data-src="<?php echo add_query_arg(['mw_preview' => '1'], "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"); ?>"
						src="" frameborder="0" scrolling="" width="100%" height="100%"></iframe>
				<iframe id="mw_page_builder_revisions" src="" frameborder="0" scrolling="" width="100%"
						height="100%"></iframe>
				<div class="mw_page_builder_loading_container">
					<div class="pb_loading"></div>
				</div>
			</div>
		</div>
	</div>

	<?php

	wp_footer();
}

?>


</body>
</html>
