<?php

use Mioweb\VisualEditor\Lib\Button;

global $mw_comment_set, $vePage;

if (post_password_required()) {
	return;
}
?>
<div id="comments" class="comments">

	<?php if (have_comments()) : ?>

		<ol class="comment-list">
		<?php
		$avatar_size = (isset($mw_comment_set['comment_style']) && $mw_comment_set['comment_style'] == '3') ? 75 : 60;

		wp_list_comments([
			'short_ping' => true,
			'reply_text' => __('Odpovědět', 'cms'),
			'avatar_size' => $avatar_size,
		]);
		?>
		</ol>

		<?php

		// pagination
		$args = [
			'show_all' => false,
			'prev_text' => mw_content_icon_set('chevron-left'),
			'next_text' => mw_content_icon_set('chevron-right'),
			'type' => 'plain',
			'add_fragment' => '#comments'
		];

		?>
		<div class="mw_page_navigation">
		<?php paginate_comments_links($args); ?>
		</div>

	<?php endif; // Check for have_comments(). ?>

	<?php
	// If comments are closed and there are comments, let's leave a little note, shall we?
	if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) :
		?>
		<p class="no-comments"><?php _e('Comments are closed.'); ?></p>
	<?php endif;


	//if(isset($mw_comment_set['button_hover']) && $mw_comment_set['button_hover']=='scale') $but_class=' ve_cb_hover_'.$mw_comment_set['button_hover'];
	//else $but_class='';


	// for blog
	if (!isset($mw_comment_set['button_style'])) {
		$mw_comment_set['button_style'] = [
			'style' => 'x',
			'button_size' => 'medium'
		];
		$but_class = 've_content_button ve_content_button_style_x';
	} else {

		$button = new Button($mw_comment_set['button_style'], '', $mw_comment_set['css_id'] . ' .ve_content_button');
		$vePage->display->element_css = $button->addButtonStyles($vePage->display->element_css, null, $vePage->display->edit_mode);
		$but_class = 've_content_button ' . $button->getButtonClasses();
	}

	$form_args = [
		'class_submit' => $but_class,
		'label_submit' => __('Vložit komentář', 'cms'),
		'cancel_reply_link' => __('Zrušit odpověď', 'cms'),
		'title_reply' => __('Přidat komentář', 'cms'),
	];
	comment_form($form_args);
	?>

</div>
