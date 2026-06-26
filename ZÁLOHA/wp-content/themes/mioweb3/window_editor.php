<?php
global $vePage;

get_header();
?>
	<div id="window_content_container" class="<?php echo $_GET['window_editor']; ?>_content_container">
		<?php
		echo $vePage->display->write_content($vePage->display->layer, $vePage->display->edit_mode)
		?>
	</div>
<?php

get_footer();
