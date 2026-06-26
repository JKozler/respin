<?php
class mwSettingPageService_comments extends mwSettingPageService
{

	public function getOption()
	{
		$option = [
			'default_comment_status' => MWDB()->getOption('default_comment_status') == 'open' ? 1 : 0,
			'require_name_email' => MWDB()->getOption('require_name_email'),
			'comment_registration' => MWDB()->getOption('comment_registration'),
			'thread_comments' => MWDB()->getOption('thread_comments'),
			'thread_comments_depth' => MWDB()->getOption('thread_comments_depth'),

			'page_comments' => MWDB()->getOption('page_comments'),
			'comments_per_page' => MWDB()->getOption('comments_per_page'),
			'default_comments_page' => MWDB()->getOption('default_comments_page'),
			'comment_order' => MWDB()->getOption('comment_order'),

			'comments_notify' => MWDB()->getOption('comments_notify'),
			'moderation_notify' => MWDB()->getOption('moderation_notify'),

			'comment_moderation' => MWDB()->getOption('comment_moderation'),
			'comment_previously_approved' => MWDB()->getOption('comment_previously_approved'),

			'comment_max_links' => MWDB()->getOption('comment_max_links'),
			'moderation_keys' => MWDB()->getOption('moderation_keys'),
			'disallowed_keys' => MWDB()->getOption('disallowed_keys'),
		];

		return $option;
	}

	public function saveSetting($tosave)
	{
		$val = isset($tosave['default_comment_status']) ? 'open' : 'closed';
		MWDB()->setOption('default_comment_status', $val);

		$val = isset($tosave['require_name_email']) ? '1' : '';
		MWDB()->setOption('require_name_email', $val);

		$val = isset($tosave['comment_registration']) ? '1' : '';
		MWDB()->setOption('comment_registration', $val);

		$val = isset($tosave['thread_comments']) ? '1' : '';
		MWDB()->setOption('thread_comments', $val);

		MWDB()->setOption('thread_comments_depth', $tosave['thread_comments_depth']);

		$val = isset($tosave['page_comments']) ? '1' : '';
		MWDB()->setOption('page_comments', $val);

		MWDB()->setOption('comments_per_page', $tosave['comments_per_page']);
		MWDB()->setOption('default_comments_page', $tosave['default_comments_page']);
		MWDB()->setOption('comment_order', $tosave['comment_order']);

		$val = isset($tosave['comments_notify']) ? '1' : '';
		MWDB()->setOption('comments_notify', $val);

		$val = isset($tosave['moderation_notify']) ? '1' : '';
		MWDB()->setOption('moderation_notify', $val);

		$val = isset($tosave['comment_moderation']) ? '1' : '';
		MWDB()->setOption('comment_moderation', $val);

		$val = isset($tosave['comment_previously_approved']) ? '1' : '';
		MWDB()->setOption('comment_previously_approved', $val);

		MWDB()->setOption('comment_max_links', $tosave['comment_max_links']);
		MWDB()->setOption('moderation_keys', $tosave['moderation_keys']);
		MWDB()->setOption('disallowed_keys', $tosave['disallowed_keys']);
	}

	public function printForm()
	{
		$option = $this->getOption();

		write_meta($this->settingPage()->getSetting(), $option, 'setting', 'setting');

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');

		echo '<input type="hidden" name="setting_id" value="' . $this->settingPage()->getId() . '"/>';
	}

}
