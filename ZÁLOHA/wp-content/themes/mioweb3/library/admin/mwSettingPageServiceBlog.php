<?php
class mwSettingPageService_blog extends mwSettingPageService
{

	public function saveSetting($tosave)
	{
		foreach ($this->settingPage()->getSetting() as $setField) {
			$tosave = $this->settingPage()->checkSaveHooks($tosave, $setField);
		}

		MWDB()->setOption($this->settingPage()->getId(), $tosave);

		if ($tosave['blog_page']['show_on_front'] == 'posts') {
			update_option('show_on_front', 'posts');
			update_option('page_on_front', '0');
			update_option('page_for_posts', '0');
		} else {
			update_option('show_on_front', 'page');
			update_option('page_for_posts', $tosave['blog_page']['page_for_posts']);
			$page_on_front = get_option('page_on_front');
			if (!$page_on_front || $page_on_front == $tosave['blog_page']['page_for_posts']) {
				$pages = new WP_Query([
					'post_type' => 'page',
					'posts_per_page' => -1,
					'post_status' => 'publish',
					'fields' => 'ids',
				]);
				if (is_array($pages->posts) && count($pages->posts) > 1) {
					foreach ($pages->posts as $page_id) {
						if ($page_id != $tosave['blog_page']['page_for_posts']) {
							update_option('page_on_front', $page_id);

							break;
						}
					}
				}
			}
		}

		mwSetting::saveUsed($tosave);
	}

}
