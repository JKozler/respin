<?php

use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\Link;
use Mioweb\VisualEditor\Lib\GDPR;
use Mioweb\VisualEditor\Lib\Button;
use Mioweb\VisualEditor\Lib\Image;
use Mioweb\Member\MemberPage;
use Mioweb\Member\MemberLevel;
use Mioweb\Member\MemberChecklist;
use Mioweb\Member\MonthMembership;

function ve_element_member_login($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage, $post;

	$vePage->display->add_enqueue_style('member_content_css');

	if ($added) {
		mwMemberModule()->builderMemberInit($post_id);
	}

	$content = '';
	$access = false;

	$vePage->display->element_css->addStyles(
		[
			'font' => $element['style']['form-font'],
			'background-color' => $element['style']['background'],
		],
		$css_id . ' .ve_form_field'
	);

	$class = 've_form_field';
	$class .= Colors::isLightColor($element['style']['background']) ? ' light_color' : ' dark_color';

	$redirect_url = '';
	$logto = null;
	if (isset($_GET['redirect_to']) && $_GET['redirect_to']) {
		$redirect_url = urldecode($_GET['redirect_to']);
	} else {
		if (mwMemberModule()->isDefaultLogin()) {
			$redirect_url = mwMemberModule()->memberPage()->getUrl();
		} else {
			$logto = mwMemberModule()->getMemberSectionForLoginPage($post_id);
			if (isset($element['style']['loginto']) && $element['style']['loginto'] !== '' && mwMemberModule()->memberSectionIdExist($element['style']['loginto'])) {
				$logtoId = $element['style']['loginto'];
				$logto = mwMemberModule()->getMemberSection($logtoId);
			}

			if ($logto && $logto->getDashboardId()) {
				$redirect_url = $logto->getUrl();

				if (mwMemberModule()->currentMember()->getMembership($logto->getId())) {
					$access = true;
				}
			}
		}
	}

	if ($logto === null) {
		$vePage->display->add_element_info(__('Přihlašovací formulář není propojen s žádnou členskou sekcí. Vyberte v nastavení formuláře členskou sekci, do které se má po vyplnění formuláře uživatel přihlásit, nebo zařaďte tuto stránku do členské sekce v Nastavení stránky->Členská stránka.', 'cms_member'));
	} elseif ($logto->getDashboardId() === null) {
		$vePage->display->add_element_info(__('Vybraná členská sekce nemá nastavenou žádnou stránku jako nástěnku. Přihlašovací formulář proto nebude fungovat správně. Nastavte členské sekci nástěnku v <strong>nastavení členských sekcí</strong>.', 'cms_member'));
	}

	$but_set = [
		'style' => $element['style']['button'] ?? [],
		'text' => __('Přihlásit se', 'cms_member'),
		'tag' => 'button',
	];

	// if user is logged in
	if ($access && !mwMemberModule()->isEditMode()) {
		$already = true;
		//if limited member
		$membership = mwMemberModule()->currentMember()->getMembership($logto->getId());

		if ($membership->isExpired()) {
			$already = false;
			$content = '<div class="member_login_form_logged_box">
					<p style="color: red;">' . __('Vaše členství v této členské sekci bylo časově omezeno a již vypršelo, proto se nelze přihlásit.', 'cms_member') . '</p>
				</div>';
		}

		if ($already) {
			$but_set['text'] = __('Vstoupit do členské sekce', 'cms_member');
			$but_set['tag'] = 'a';
			$but_set['link'] = $redirect_url;

			$content = '<div class="member_login_form_logged_box">';
			$content .= '<p>' . __('Už jste přihlášen(a).', 'cms_member') . '</p>';
			$content .= '<div class="member_login_form_button_row">';
			$content .= Button::createButton(
				$but_set,
				$vePage->display->element_css,
				'',
				$css_id . ' .ve_content_button',
				$added,
				$edit_mode
			);
			$content .= '</div>';
			$content .= '</div>';
		}
	} else {
		// if user is not logged in
		$content .= '<form class="in_element_content member_login_form ve_content_form ve_form_style_1 ve_form_input_style_' . $element['style']['input-style'] . ' ' . (isset($element['style']['corners']) ? 've_form_corners_' . $element['style']['corners'] : '') . '" action="' . wp_login_url() . '" method="post">';

		$content .= '<div class="ve_form_row">';
		$content .= '<input class="ve_form_text ' . $class . '" type="text" name="log" id="log" value="" placeholder="' . __('Přihlašovací jméno', 'cms_member') . '" />';
		$content .= '</div>';

		$content .= '<div class="ve_form_row">';
		$content .= '<input class="ve_form_text ' . $class . '" type="password" name="pwd" id="pwd" placeholder="' . __('Heslo', 'cms_member') . '" />';
		$content .= '</div>';

		$content .= '<div class="member_login_form_button_row">';
		$but_set['attrs'] = 'type="submit" name="submit" value="1"';
		$content .= Button::createButton(
			$but_set,
			$vePage->display->element_css,
			'',
			$css_id . ' .ve_content_button',
			$added,
			$edit_mode
		);
		$content .= '<input type="hidden" name="redirect_to" value="' . $redirect_url . '" />';
		$content .= '<input type="hidden" name="cms_abort_redirect" value="1" />';
		$content .= '</div>';

		$content .= '<div class="member_login_form_forgot"><a href="' . wp_lostpassword_url() . '">' . __('Zapomněli jste heslo?', 'cms_member') . '</a></div>';

		$content .= '</form>';

		/*
		<div class="member_login_form_row">
		<label><input name="rememberme" id="rememberme" value="forever" type="checkbox"> Pamatovat si mě</label>
		</div>

		$args = array(
		'redirect' => admin_url(),
		'form_id' => 'loginform-custom',
		'label_username' => __( 'Username custom text' ),
		'label_password' => __( 'Password custom text' ),
		'label_remember' => __( 'Remember Me custom text' ),
		'label_log_in' => __( 'Log In custom text' ),
		'remember' => true,
		'echo' => false
		);
		$content.=wp_login_form( $args );*/
	}

	return $content;
}

function ve_element_member_regform($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;

	$vePage->display->add_enqueue_style('member_content_css');
	$vePage->display->add_enqueue_script('member_front_script');

	$content = '';

	if (isset($element['style']['reginto']) && isset($element['style']['reginto']['section']) && $element['style']['reginto']['section'] !== '') {
		$memberSection = mwMemberModule()->getMemberSection($element['style']['reginto']['section']);

		if ($memberSection === null) {
			$vePage->display->add_element_info(__('Vybraná členská sekce již neexistuje. Pravděpodobně byla smazána.', 'cms_member'));
		} else {
			$vePage->display->element_css->addStyles(
				[
					'font' => $element['style']['form-font'],
					'background-color' => $element['style']['background'],
				],
				$css_id . ' .ve_form_field'
			);

			$class = 've_form_field';
			$class .= Colors::isLightColor($element['style']['background']) ? ' light_color' : ' dark_color';

			$redirect = '';
			if (isset($element['style']['redirect'])) {
				$redirect = Link::create_link($element['style']['redirect'], false);
			}

			$info = [];

			$info['name'] = $memberSection->getName();
			$info['id'] = $memberSection->getId();
			if (isset($element['style']['reginto']['levels'][$element['style']['reginto']['section']])) {
				$info['level'] = $element['style']['reginto']['levels'][$element['style']['reginto']['section']];
			}

			if (isset($element['style']['update'])) {
				$info['update'] = 1;
			}
			if ($element['style']['sendtomail']) {
				$info['email'] = $element['style']['sendtomail'];
			}
			if (isset($element['style']['days']) && $element['style']['days']) {
				$info['days'] = $element['style']['days'];
			}
			if (isset($element['style']['generate_password'])) {
				$info['generate_password'] = 1;
			}
			if (isset($element['style']['no_email'])) {
				$info['no_email'] = 1;
			}

			// back compatibility (temporary)
			$element['style']['sendtose'] = mwEmailingApi()->repair_content_val($element['style']['sendtose']);
			// back compatibility end
			if (isset($element['style']['sendtose']['id']) && $element['style']['sendtose']['id']) {
				$info['se'] = $element['style']['sendtose'];
			}

			// form
			$content .= '<form action="" class="in_element_content mw_member_register_form ve_content_form ve_form_style_1 ve_form_input_style_' . $element['style']['input-style'] . ' ' . (isset($element['style']['corners']) ? 've_form_corners_' . $element['style']['corners'] : '') . '" method="post">';

			$content .= '<div class="ve_form_message">
            <svg class="error_icon" role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-x-circle"></use></svg>
            <svg class="ok_icon" role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-check-circle"></use></svg>
            <span></span>
        	</div>';

			$content .= '<div class="ve_form_row">
                <input class="ve_form_text ve_form_required ve_form_email ' . $class . '" value="' . (isset($_GET['email']) ? esc_attr(urldecode($_GET['email'])) : '') . '" type="text" name="user_email" placeholder="' . __('E-mail', 'cms_member') . '*" />
        	</div>';
			if (!isset($element['style']['hide']) || !isset($element['style']['hide']['name'])) {
				$content .= '<div class="ve_form_row">
                <input class="ve_form_text ' . $class . '" type="text" name="first_name" placeholder="' . __('Jméno', 'cms_member') . '" />
        	</div>
        	<div class="ve_form_row">
                <input class="ve_form_text ' . $class . '" type="text" name="last_name" placeholder="' . __('Příjmení', 'cms_member') . '" />
        	</div>';
			}
			if (!isset($element['style']['generate_password'])) {
				$content .= '<div class="ve_form_row">
                <input class="ve_form_text ve_form_required ' . $class . '" type="password" name="user_password" placeholder="' . __('Heslo', 'cms_member') . '*" />
        	</div>
        	<div class="ve_form_row">
                <input class="ve_form_text ve_form_required ' . $class . '" type="password" name="user_password2" placeholder="' . __('Potvrdit heslo', 'cms_member') . '*" />
        	</div>';
			}

			$content .= GDPR::printConsent('regform', $element['style']['gdpr_info'] ?? '', $element['style']['gdpr_link_text'] ?? '', $edit_mode);

			$content .= '<div class="member_login_form_button_row">';

			$but_set = [
				'style' => $element['style']['button'] ?? [],
				'text' => __('Registrovat se', 'cms_member'),
				'loading' => true,
				'icon_align' => 'right',
				'tag' => 'button',
				'attrs' => 'type="submit"',
			];

			$content .= Button::createButton(
				$but_set,
				$vePage->display->element_css,
				'',
				$css_id . ' .ve_content_button',
				$added,
				$edit_mode
			);

			$content .= '<input type="hidden" name="member_free_registration" value="' . base64_encode(serialize($info)) . '" />';
			$content .= wp_nonce_field('member_free_registration_nonce', 'mw_wpnonce_free_reg', false, false);
			$content .= '<input type="hidden" name="member_registration_redirect" value="' . $redirect . '" />';
			if ($vePage->edit_mode) {
				$content .= '<input type="hidden" name="member_registration_redirect_target" value="parent" />';
			}
			$content .= '</div>';
			$content .= '</form>';

			if ($added) {
				$content .= "<script>
		            jQuery(function() {
		              mwGetIframeContent().mw_init_register_form('" . $css_id . " .mw_member_register_form');
		            });
		          </script>";
			}
		}
	} else {
		$vePage->display->add_element_info(__('Registrační formulář není propojen s žádnou členskou sekcí.', 'cms_member'), 'info');
	}

	return $content;
}


function ve_element_member_subpages($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	if ($added) {
		mwMemberModule()->builderMemberInit($post_id);
	}

	global $vePage;

	$vePage->display->add_enqueue_style('member_content_css');
	$vePage->display->add_enqueue_script('member_front_script');

	if (isset($element['style']['page']) && $element['style']['page']) {
		$post_id = $element['style']['page'];
	}

	$items = MemberPage::getMemberSubPages(['parent' => $post_id]);

	// pages on same level
	if (empty($items) && $element['style']['page'] === '') {
		$parent_id = wp_get_post_parent_id($post_id);
		$items = MemberPage::getMemberSubPages(['parent' => $parent_id]);
	}

	$content = '';

	if (!empty($items)) {
		$element['style']['cols'] ??= '';
		$cols = $vePage->display->getAutoCols($element['style']['cols'], count($items), 3, false, $element['style']['item_style']);

		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .mw_member_page_item_progress' => 'background-color',
				$css_id . ' .mw_member_page_item_progress_finished' => 'background-color',
			],
			'--progress-color-' . $css_id,
			$element['style']['progress_color'] ?? '#56b616'
		);

		$hover_style = $element['style']['hover'] ?? '';
		$image_ratio = $element['style']['image_ratio'] ?? '32';
		$text_align = $element['style']['text_align'] ?? 'left';
		$img_col_size = $element['style']['image_size'] ?? 2;

		$items_set = [];

		foreach ($items as $item) {
			$show = true;
			$access = true;

			$show_info = '';
			$hover_icon = 'lock';

			if (!mwMemberModule()->isEditMode() && $item->getMemberPageId()) {
				$membership = mwMemberModule()->currentMember()?->getMembership($item->getMemberSectionId());

				if ($membership === null) {
					$access = false;
					$show_info = '<p>' . __('Do této členské sekce nemáte přístup', 'cms_member') . '</p>';
				}

				if ($access && $membership !== null && !$membership->hasLevelAccess($item->getLevels())) {
					$show = false;

					foreach ($item->getLevels() as $mLevel) {
						$level = MemberLevel::getOneById($mLevel);
						if ($level && $level->isVisible()) {
							$show = true;
							$access = false;
							$show_info = __('K této stránce nemáte přístup', 'cms_member');
							if ($level->getNoAccessId()) {
								$show_info .= ' <a href="' . get_permalink($item->getId()) . '" class="mw_member_noacess_but">' . mw_content_icon_set('info') . __('Více informací', 'cms_member') . '</a>';
							}
						}
					}
				}

				if ($show && $access) {
					// month access
					if ($item->isMonth() && !$membership->hasMonthAccess($item->getMonth()->getMonth())) {
						$show_info = '<p>' . __('K této stránce nemáte přístup', 'cms_member') . '</p>';
						$show_info .= $item->getMonthPageId() ? ' <a class="mw_member_noacess_but" href="' . $item->getMonthPageUrl() . '">' . mw_content_icon_set('unlock') . __('Získat přístup', 'cms_member') . '</a>' : '';
						$access = false;
					}

					// evergreen
					if ($access && $time = $item->isAvailableInFuture($membership->getStart())) {
						$hover_icon = 'unlock';
						$show_info = '<p>' . __('Bude zveřejněno', 'cms_member') . '</p>';
						$show_info .= ' <strong>' . str_replace(' 00:00', '', date('d.m.Y H:i', $time)) . '</strong>';

						// hide evergreen
						if (mwMemberModule()->getMemberSection($item->getMemberSectionId())?->hideEvergreen()) {
							$show = false;
						}

						$access = false;
					}

					// checklist
					if ($access && $item->getAccessType() === 'checklist' && !$item->isPreviousPageCompleted()) {
						$hover_icon = 'unlock';
						$show_info = '<p>' . __('Bude zveřejněno', 'cms_member') . '</p>';
						$show_info .= ' <strong>' . __('po splnění úkolů předešlé lekce', 'cms_member') . '</strong>';

						$access = false;
					}
				}
			}

			if ($show) {
				$subtitle = '';

				// image
				$image = $item->getThumbnail();

				if ($image->isEmpty() && isset($element['style']['default_image'])) {
					$image = Image::createByUrl($element['style']['default_image']);
				}

				// comment counts
				if (!isset($element['style']['hide_comments'])) {
					$subtitle = $item->getCommentsText();
				}

				$class = !$access ? 'mw_member_item_noaccess' : '';
				$progress = '';

				// progress
				if ($access && !isset($element['style']['hide_progress']) && (!isset($element['style']['hide_image']) || ($element['style']['item_style'] !== '3' && $element['style']['item_style'] !== '6'))) {
					$percentage = mwMemberModule()->currentMember()->getProgress($item->getId(), 'parent', true);
					if ($percentage !== null) {
						$progress = '<div class="mw_member_page_item_progress_container">';
						$progress .= '<div class="mw_member_page_item_progress" style="width: ' . $percentage . '%;"></div>';
						if ($percentage == 100) {
							$progress .= '<div class="mw_member_page_item_progress_finished">' . mw_content_icon_set('check') . '</div>';
						}
						$progress .= '</div>';
						$class .= ' mw_member_page_item_with_progress_bar';
					}
				}

				// info if no access
				if (isset($element['style']['hide_image']) && $show_info) {
					$subtitle = $show_info;
				}

				$args = [
					'link' => $access ? $item->getUrl() : '',
					'class' => $class,
					'image' => $image,
					'title' => $item->getName(),
					'description' => $item->getExcerpt((int) ($element['style']['words'] ?? 0)),
					'subtitle' => $subtitle,
				];

				if (isset($element['style']['hide_image'])) {
					$args['custom_header'] = $progress;
				} else {
					$args['after_image'] = $progress;
				}

				$args['image_hover_content'] = '';

				if ($element['style']['item_style'] == '1') {
					$args['image_hover_link'] = $args['link'] ? true : false;
					$args['image_hover_content'] = '<h3 class="' . $vePage->display->get_font_class($element['style']['font']) . '">' . $args['title'] . '</h3>';
					$args['image_hover_content'] .= $vePage->display->printContentContainer($args['subtitle'], 'mw_element_item_subtitle', 'span');
				}

				if ($show_info) {
					$args['image_hover'] = true;
					$args['image_hover_link'] = false;
					$args['image_hover_content'] = '<div class="mw_member_noaccess_info_icon"><i>' . mw_content_icon_set($hover_icon) . '</i></div>' . $args['image_hover_content'];
					$args['image_hover_content'] .= '<div class="mw_member_item_noacess_info">' . $show_info . '</div>';
				}

				$items_set[] = $args;
			}
		}

		$items_args = [
			'style' => $element['style']['item_style'],
			'cols' => $cols,
			'inside_col_type' => $col_type,
			'autocols' => !$element['style']['cols'] ? true : false,
			'cols_type' => $element['style']['cols_type'] ?? 'cols',
			'hover_style' => $hover_style,
			'image_ratio' => $image_ratio,
			'hide_image' => isset($element['style']['hide_image']) ? true : false,
			'align' => $text_align,
			'img_col_size' => $img_col_size,
			'show_description' => isset($element['style']['hide_desc']) ? false : true,
			'styles' => [
				'hover_color' => isset($element['style']['hover_color']) && $element['style']['item_style'] == '1' ? $element['style']['hover_color']['rgba'] : 'rgba(0,0,0,0,5)',
				'font_title' => $element['style']['font'],
				'font_subtitle' => $element['style']['font_subtitle'] ?? '',
				'font_description' => $element['style']['font_description'] ?? [],
			],
			'cssid' => $css_id,
			'added' => $added,
		];

		if (isset($element['style']['background_set'])) {
			$items_args['background_set'] = $element['style']['background_set'];
		}

		if ($element['style']['item_style'] == '1') {
			$items_args['image_hover'] = true;
		}

		$content .= '<div class="mw_member_lessons">';
		$content .= $vePage->display->generate_element_items($items_args, $items_set, $added, $row_set);
		$content .= '</div>';
	} else {
		$vePage->display->add_element_info(__('Tato nebo vybraná stránka nemá žádné podstránky, proto nelze seznam lekcí vypsat. Vyberte stránku, která má podstránky, nebo vytvořte podstránky této stránce.', 'cms_member'), 'info');
	}

	return $content;
}

function ve_element_member_months_pages($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	if ($added) {
		mwMemberModule()->builderMemberInit($post_id);
	}

	global $vePage;

	$content = '';

	if (mwMemberModule()->isMemberPage()) {
		$memberId = mwMemberModule()->memberSection()->getId();

		// archive
		$curYear = null;
		if ($element['style']['show'] === 'archive') {
			$curYear = $_GET['month_year_archive'] ?? date('Y', current_time('timestamp'));

			if (date('m', current_time('timestamp')) === '01' && !isset($_GET['month_year_archive'])) {
				$curYear--;
			}

			$years = MonthMembership::getArchiveYears($memberId);

			$args = [
				'get' => 'archive',
				'year' => $curYear,
			];
		} elseif ($element['style']['show'] === 'current') {
			$args = [
				'get' => 'current',
			];

			$element['style']['cols'] = 1;

			$element['style']['style'] = $element['style']['current_style'];
			$element['style']['background_set'] = $element['style']['current_background_set'];
			$element['style']['image_size'] = $element['style']['current_image_size'];
			$element['style']['hover'] = $element['style']['current_hover'];
			$element['style']['image_ratio'] = $element['style']['current_image_ratio'];
			$element['style']['title_font'] = $element['style']['current_title_font'];
			$element['style']['font_description'] = $element['style']['current_font_description'] ?? [];
			$element['style']['font_subtitle'] = $element['style']['current_font_subtitle'] ?? [];
		} else {
			$args = [
				'get' => 'future',
				'hide_current' => isset($element['style']['hide_current']),
			];
		}

		$pages = MonthMembership::getAllMonthPages($memberId, $args);

		$content .= '<div class="in_element_content mw_member_month_lessons">';

		if ($element['style']['show'] === 'archive' && !empty($years)) {
			$content .= '<div id="mw_member_month_archive" class="mw_element_months_nav">';
			$content .= '<span>' . __('Archív pro rok', 'cms_member') . '</span>';
			if ($vePage->edit_mode) {
				$content .= '<select onchange="window.parent.location = this.options[this.selectedIndex].value;">';
			} else {
				$content .= '<select onchange="window.location = this.options[this.selectedIndex].value;">';
			}
			foreach ($years as $year) {
				$content .= '<option value="' . add_query_arg('month_year_archive', $year, get_permalink($post_id)) . '#mw_member_month_archive" ' . ($year === $curYear ? 'selected="selected"' : '') . '>' . $year . '</option>';
			}
			$content .= '</select>';
			$content .= '</div>';
		}

		if (!empty($pages)) {
			$element['style']['cols'] ??= 0;
			$cols = $vePage->display->getAutoCols($element['style']['cols'], count($pages), 3, false, $element['style']['style']);

			$hover_style = $element['style']['hover'] ?? '';
			$image_ratio = $element['style']['image_ratio'] ?? '32';
			$img_col_size = $element['style']['image_size'] ?? 2;

			$items_set = [];

			foreach ($pages as $item) {
				$access = true;
				$show = true;

				$show_info = '';
				$hover_icon = 'lock';
				$subtitle = '';

				if (!mwMemberModule()->isEditMode() && $item->getMemberPageId()) {
					$membership = mwMemberModule()->currentMembership();

					// level access
					if (!$membership->hasLevelAccess($item->getLevels())) {
						$show = false;

						foreach ($item->getLevels() as $mLevel) {
							$level = MemberLevel::getOneById($mLevel);
							if ($level && $level->isVisible()) {
								$show = true;
								$access = false;
								$show_info = __('K této stránce nemáte přístup', 'cms_member');
								if ($level->getNoAccessId()) {
									$show_info .= ' <a href="' . get_permalink($item->getId()) . '" class="mw_member_noacess_but">' . mw_content_icon_set('info') . __('Více informací', 'cms_member') . '</a>';
								}
							}
						}
					}

					if ($show) {
						if (!$membership->hasMonthAccess($item->getMonth()->getMonth())) {
							$show_info = '<p>' . __('K této stránce nemáte přístup', 'cms_member') . '</p>';
							$show_info .= $item->getMonthPageId() ? ' <a class="mw_member_noacess_but" href="' . $item->getMonthPageUrl() . '">' . mw_content_icon_set('unlock') . __('Získat přístup', 'cms_member') . '</a>' : '';
							$subtitle = __('K této stránce nemáte přístup', 'cms_member');

							$access = false;
						} elseif ($time = $item->isAvailableInFuture($membership->getStart())) {
							// evergreen
							$hover_icon = 'unlock';
							$show_info = '<p>' . __('Bude zveřejněno', 'cms_member') . '</p>';
							$show_info .= ' <strong>' . str_replace(' 00:00', '', date('d.m.Y H:i', $time)) . '</strong>';
							$subtitle = $show_info;

							$access = false;
						}
					}
				}

				if ($show) {
					// image
					$image = $item->getThumbnail();

					if ($image->isEmpty() && isset($element['style']['default_image'])) {
						$image = Image::createByUrl($element['style']['default_image']);
					}

					$class = !$access ? 'mw_member_item_noaccess' : '';
					$progress = '';

					if ($access && !isset($element['style']['hide_progress']) && (!isset($element['style']['hide_image']) || ($element['style']['item_style'] !== '3' && $element['style']['item_style'] !== '6'))) {
						$percentage = mwMemberModule()->currentMember()->getProgress($item->getId(), 'parent', true);
						if ($percentage !== null) {
							$progress = '<div class="mw_member_page_item_progress_container">';
							$progress .= '<div class="mw_member_page_item_progress" style="width: ' . $percentage . '%;"></div>';
							if ($percentage == 100) {
								$progress .= '<div class="mw_member_page_item_progress_finished">' . mw_content_icon_set('check') . '</div>';
							}
							$progress .= '</div>';
							$class .= 'mw_member_page_item_with_progress_bar';
						}
					}

					$args = [
						'link' => $access ? $item->getUrl() : '',
						'class' => $class,
						'image' => $image,
						'title' => $item->getName(),
						'description' => $item->getExcerpt($element['style']['words'] ?? 20),
						'subtitle' => $subtitle,
					];

					if (isset($element['style']['hide_image'])) {
						$args['custom_header'] = $progress;
					} else {
						$args['after_image'] = $progress;
					}

					$args['image_hover_content'] = '';

					if ($element['style']['style'] == '1') {
						$args['image_hover_link'] = $args['link'] ? true : false;
						$args['image_hover_content'] = '<h3 class="' . $vePage->display->get_font_class($element['style']['title_font']) . '">' . $args['title'] . '</h3>';
						$args['image_hover_content'] .= $vePage->display->printContentContainer($args['subtitle'], 'mw_element_item_subtitle', 'span');
					}

					if ($show_info) {
						$args['image_hover'] = true;
						$args['image_hover_link'] = false;
						$args['image_hover_content'] = '<div class="mw_member_noaccess_info_icon"><i>' . mw_content_icon_set($hover_icon) . '</i></div>' . $args['image_hover_content'];
						$args['image_hover_content'] .= '<div class="mw_member_item_noacess_info">' . $show_info . '</div>';
					}

					$items_set[] = $args;
				}
			}

			$items_args = [
				'style' => $element['style']['style'],
				'cols' => $cols,
				'inside_col_type' => $col_type,
				'autocols' => !$element['style']['cols'] ? true : false,
				'cols_type' => $element['style']['cols_type'] ?? 'cols',
				'hover_style' => $hover_style,
				'image_ratio' => $image_ratio,
				'img_col_size' => $img_col_size,
				'show_description' => isset($element['style']['hide_desc']) ? false : true,
				'styles' => [
					'hover_color' => isset($element['style']['hover_color']) && $element['style']['style'] == '1' ? $element['style']['hover_color']['rgba'] : 'rgba(0,0,0,0,5)',
					'font_title' => $element['style']['title_font'],
					'font_subtitle' => $element['style']['font_subtitle'] ?? '',
					'font_description' => $element['style']['font_description'] ?? [],
				],
				'cssid' => $css_id,
				'added' => $added,
			];

			if (isset($element['style']['background_set'])) {
				$items_args['background_set'] = $element['style']['background_set'];
			}

			if ($element['style']['style'] == '1') {
				$items_args['image_hover'] = true;
			}

			$content .= $vePage->display->generate_element_items($items_args, $items_set, $added, $row_set);
		} elseif ($element['style']['show'] == 'archive') {
			$content .= '<div class="mem_member_list_empty">' . __('Archív je prázdný', 'cms_member') . '</div>';
		} else {
			$vePage->display->add_element_info(__('Nejsou definovány žádné lekce měsíčního členství. Vytvořte stránky pro měsíční členství.', 'cms_member'));
		}
		$content .= '</div>';
	} else {
		$vePage->display->add_element_info(__('Tento element musí být umístěn na stránce v členské sekci.', 'cms_member'));
	}

	return $content;
}

function ve_element_member_checklist($element, $css_id, $post_id, $edit_mode, $added)
{
	if ($added) {
		mwMemberModule()->builderMemberInit($post_id);
	}

	global $vePage;
	$content = '';
	if (mwMemberModule()->isMemberPage()) {
		$user = mwMemberModule()->currentMember();

		$type = $element['style']['use'] ?? 'custom';

		if ($type === 'page') {
			$checklist = mwMemberModule()->memberPage()->getChecklistForUser($user->getId());
			$checklistId = mwMemberModule()->memberPage()->getId();
		} else {
			$tasks = $element['style']['content'] ?? [];
			$checklistId = $element['style']['checklist'] . '_' . $post_id;
			$checklist = MemberChecklist::createFromElement($checklistId, $tasks, $user);
		}

		if (!$checklist->isEmpty()) {
			$vePage->display->element_css->addStyles(['font' => $element['style']['font_text']], $css_id . ' li');
			$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .mw_el_mem_checklist_title');
			$vePage->display->element_css->addVariableStyles(
				[
					//$css_id.' li:hover .mem_checklist_checkbox'=>array('border-color'),
					$css_id . ' .mem_checklist_checkbox_checked' => ['background-color'],
				],
				'--checkbox-color-' . $css_id,
				$element['style']['checkbox_color']
			);

			if (!isset($element['style']['title'])) {
				$element['style']['title'] = '';
			}

			$content .= '<div class="in_element_content el_mem_checklist" data-user_id="' . $user->getId() . '"  data-checklist_id="' . $checklistId . '" data-type="' . $type . '">';
			$content .= $vePage->display->printContentContainer($element['style']['title'], 'mw_el_mem_checklist_title ' . $vePage->display->get_font_class($element['style']['font']));
			$content .= '<ul>';
			foreach ($checklist->getTasks() as $task) {
				$content .= '<li>
                    <div class="label">
                        <div class="mem_checklist_checkbox mw_corners_' . $element['style']['corners'] . ' ' . ($task->isCompleted() ? 'mem_checklist_checkbox_checked' : '') . '">
                            <div class="mem_checklist_checkbox_icon">' . mw_content_icon('icon-ok1', 'content-icons.svg') . '</div>
                            <div class="mem_checklist_checkbox_none_icon">' . mw_content_icon_set('x', 'feather') . '</div>
                            <input type="checkbox" ' . ($task->isCompleted() ? 'checked="checked"' : '') . ' name="checklist[' . $task->getId() . ']" value = "' . $task->getId() . '" />
                        </div>
                        <div class="mem_checklist_checkbox_text">' . $task->getTask() . '</div>
                    </div>
                </li>';
			}
			$content .= '</ul></div>';

			if ($added) {
				$content .= "<script>
		            jQuery(function() {
		              mwGetIframeContent().mw_init_member_checklist('" . $css_id . " .el_mem_checklist');
		            });
		          </script>";
			}
		} else {
			$vePage->display->add_element_info(__('Nejsou zadány žádné úkoly.', 'cms_member'), 'info');
		}
	} else {
		$vePage->display->add_element_info(__('Tento element bude fungovat pouze na členské stránce. Tato stránka není zařazena do žádné členské sekce.', 'cms_member'));
	}

	return $content;
}

function ve_element_member_progress($element, $css_id, $post_id, $edit_mode, $added)
{
	if ($added) {
		mwMemberModule()->builderMemberInit($post_id);
	}

	global $vePage;
	$content = '';

	if (mwMemberModule()->isMemberPage()) {
		$progress = 0;

		$progressfor = $element['style']['show'] ?? 'page';

		if ($progressfor == 'page') {
			$child_of = $element['style']['page'] === '' ? $post_id : $element['style']['page'];
			$progress = mwMemberModule()->currentMember()->getProgress($child_of, 'parent');
		} else {
			$member_id = isset($element['style']['member']) && $element['style']['member'] != '' ? $element['style']['member'] : mwMemberModule()->memberPage()->getMemberSectionId();
			$progress = mwMemberModule()->currentMember()->getProgress($member_id, 'member_section');
		}

		$element['style']['percent'] = $progress;

		$content .= ve_element_progressbar($element, $css_id, $post_id, $edit_mode, $added);
	} else {
		$vePage->display->add_element_info(__('Tento element bude fungovat pouze na členské stránce. Tato stránka není zařazena do žádné členské sekce.', 'cms_member'));
	}

	return $content;
}

function ve_element_member_news($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;

	$vePage->display->add_enqueue_style('member_content_css');
	$vePage->display->add_enqueue_script('member_front_script');

	$content = '';

	if ($element['style']['type'] == 'all') {
		$num = $element['style']['per_page'];
		$words = $element['style']['words_all'];
		$current_page = $_GET['mnpage'] ?? 1;
	} else {
		$num = $element['style']['number_news'];
		$words = $element['style']['words_last'];
		$current_page = 1;
	}

	$query = MwMemberNew::getAll([], $num, $current_page, true);
	$news = $query['items'];

	$content .= '<div id="mw_member_news_list" class="in_element_content mw_member_news_list">';

	if (count($news)) {
		$items = [];

		if (!isset($element['style']['style'])) {
			$element['style']['style'] = '3';
		}
		if (!isset($element['style']['cols'])) {
			$element['style']['cols'] = 0;
		}
		$cols = $vePage->display->getAutoCols($element['style']['cols'], count($news), 3, $current_page == 1);

		foreach ($news as $new) {
			if ($words >= str_word_count(wp_strip_all_tags($new->getContent()))) {
				$short = $new->getNewContent();
				$text = '';
			} else {
				$short = $new->getNewContent($words);
				$short .= ' <a class="member_new_show_text" href="#">' . __('Zobrazit celé', 'cms_member') . '</a>';
				$text = $new->getNewContent();
			}

			$new_content = '<div class="member_new_short entry_content">' . wpautop($short) . '</div>';

			if ($text) {
				$new_content .= '<div class="member_new_text entry_content">' . wpautop($text) . ' <a class="member_new_show_text" href="#">' . __('Skrýt', 'cms_member') . '</a></div>';
			}

			$args = [
				'title' => $new->getName(),
				'subtitle' => $new->getDateCreated(),
				'description' => $new_content,
			];

			$items[] = $args;
		}

		$items_args = [
			'style' => $element['style']['style'],
			'cols' => $cols,
			'autocols' => $element['style']['cols'] ? false : true,
			'hide_image' => true,
			'styles' => [
				'font_title' => $element['style']['font_title'],
				'font_description' => $element['style']['font'],
			],
			'cssid' => $css_id,
			'added' => $added,
		];

		if (isset($element['style']['background_set'])) {
			$items_args['background_set'] = $element['style']['background_set'];
		}

		$content .= $vePage->display->generate_element_items($items_args, $items);

		// page navigation
		if ($element['style']['type'] == 'all' && $query['pages'] > 1) {
			$pagination = paginate_links([
				'base' => get_permalink($post_id) . '%_%',
				'format' => '?mnpage=%#%',
				'current' => $current_page,
				'total' => $query['pages'],
				'show_all' => false,
				'type' => 'plain',
				'prev_text' => mw_content_icon_set('chevron-left'),
				'next_text' => mw_content_icon_set('chevron-right'),
				'add_fragment' => '#mw_member_news_list',
			]);

			$content .= '<div class="mw_page_navigation">' . $pagination . '</div>';
		}
	} else {
		$content .= '<div class="mw_element_items_info_box">' . __('Momentálně nejsou k dispozici žádné novinky.', 'cms_ve') . '</div>';
	}
	$content .= '</div>';

	return $content;
}

function ve_element_member_users($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$vePage->display->add_enqueue_style('member_content_css');
	$vePage->display->add_enqueue_script('member_front_script');
	$vePage->display->add_enqueue_script('ve_lightbox_script');
	$vePage->display->add_enqueue_style('ve_lightbox_style');

	$content = '';

	$current_page = $_GET['mcpage'] ?? 1;
	$search = $_GET['search_member'] ?? '';
	$memberSectionId = $element['style']['show'] === '2' ? $element['style']['member_section']['section'] : null;
	$levels = $memberSectionId !== null ? $element['style']['member_section']['levels'][$memberSectionId] ?? null : null;

	$members = mwMember::getAll([
		'paged' => $current_page,
		'number' => $element['style']['per_page'] ?? 15,
		'search' => $search,
		'show_in_memberlist' => true,
		'member_section_id' => $memberSectionId,
		'levels' => $levels,
	], true);

	switch ($element['style']['style']) {
		case '1':
			$style = '6';

			break;
		case '2':
			$style = '3';

			break;
		case '4':
			$style = '4';

			break;
	}

	if ($style == '6') {
		$cols = 1;
		$words = 33;
	} else {
		$cols = 3;
		$words = 10;
	}

	$content = '<div id="mem_member_user_list" class="in_element_content mw_element_item_hover_content mw_member_users_list mw_member_users_list_style_' . $element['style']['style'] . '">';

	$content .= '<div class="mem_member_list_head">';

	if ($element['style']['title']) {
		$content .= '<div class="mem_member_list_title title_element_container">' . $element['style']['title'] . '</div>';
	}

	// search form
	$content .= '<form action="' . get_permalink($post_id) . '#mem_member_user_list" method="GET">';
	$content .= '<input type="text" class="mem_search_member_input" name="search_member" value="' . ($search ? esc_attr($search) : '') . '" placeholder="' . __('Hledat', 'cms_member') . '" />';
	$content .= '<button type="submit" class="mem_search_member_but">' . mw_content_icon_set('search', 'feather') . '</button>';
	if ($search) {
		$content .= '<a class="mem_search_member_cancel" href="' . get_permalink($post_id) . '#mem_member_user_list" title="' . __('Zrušit vyhledávání', 'cms_member') . '">' . mw_content_icon_set('x', 'feather') . '</a>';
	}
	$content .= '</form>';

	$content .= '<div class="cms_clear"></div></div>';

	if (!empty($members['items'])) {
		if (!isset($element['style']['icons_color'])) {
			$element['style']['icons_color'] = '#ababab';
		}

		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .mw_social_icon_bg' => 'color',
			],
			'--social-icon-color-' . $css_id,
			$element['style']['icons_color']
		);

		$hover_style = $element['style']['hover'] ?? '';
		$thumb_name = $vePage->display->is_mobile || $added || (isset($row_set['type']) && $row_set['type'] == 'full') ? 'mio_columns_c1' : 'mio_columns_c' . $cols;
		$image_ratio = '11';

		$text_align = $element['style']['style'] == '1' ? 'left' : 'center';

		$items = [];
		foreach ($members['items'] as $member) {
			$short = $member->getDescription($words);
			$text = $member->getDescription();
			$name = $member->getDisplayName(true);
			$img = $member->getAvatarUrl(['size' => 280]);

			$contactMethods = mwUser::getContactMethods();
			$social_icons = '';
			foreach ($contactMethods as $mKey => $method) {
				if ($member->getContactInfo($mKey)) {
					$social_icons .= '<a class="mw_social_icon_bg" target="_blank" href="' . $member->getContactInfo($mKey) . '" title="' . $method . '">' . mw_content_icon('icon-' . $mKey . '1', 'social-icons.svg') . '</a>';
				}
			}

			if ($social_icons) {
				$soc_class = Colors::isLightColor($element['style']['icons_color']) ? ' light_hover_color' : ' dark_hover_color';
				$social_icons = '<div class="mw_social_icons_container mw_social_icons_container_3 ' . $soc_class . '">' . $social_icons . '</div>';
			}

			$popup_content = '
                <div class="mem_member_list_detail">
                    <div class="mem_mld_head">
                        <img class="mem_mld_image" src="' . $img . '" />
                        <div class="mem_mld_head_info">
                            <div class="mem_mld_name title_element_container">' . $name . '</div>
                            ' . ($member->getDomain() ? '<span class="mem_member_list_domain">' . $member->getDomain() . '</span>' : '');

			$contacts = '';
			if (!$member->hideEmailInMemberList()) {
				$contacts .= '<div>email: <a class="mem_member_list_email" href="mailto:' . $member->getEmail() . '"">' . $member->getEmail() . '</a></div>';
			}
			if ($member->getWebsite()) {
				$contacts .= '<div>web: <a class="mem_member_list_url" href="' . $member->getWebsite() . '" target="_blank">' . $member->getWebsite() . '</a></div>';
			}

			if ($contacts) {
				$popup_content .= '<div class="mem_member_list_contacts">' . $contacts . '</div>';
			}

			$popup_content .= $social_icons;

			$popup_content .= '</div>
                    </div>';
			if ($text) {
				$popup_content .= '<div class="mem_mld_text">' . $text . '</div>';
			}

			// custom fields
			$custom_fields = MwMemberCustomField::getAll();
			if ($custom_fields['count']) {
				foreach ($custom_fields['items'] as $field) {
					if ($member->getCustomField($field->getId())) {
						$popup_content .= '<div class="mem_mld_label">' . $field->getName() . '</div>';
						$popup_content .= '<p>' . $member->getCustomField($field->getId()) . '</p>';
					}
				}
			}

			$popup_content .= '</div>';

			$args = [
				'image' => new Image(['image' => $img]),
				'title' => $name,
				'subtitle' => $member->getDomain(),
				'description' => $short,
				'custom_footer' => $social_icons,
				'open_popup' => true,
				'popup_content' => $popup_content,
				'image_hover_content' => mw_content_icon_set('zoom-in'),
				'image_hover_link' => true,
			];

			$items[] = $args;
		}

		$items_args = [
			'style' => $style,
			'cols' => $cols,
			'cols_type' => $element['style']['cols_type'] ?? 'cols',
			'hover_style' => $hover_style,
			'image_ratio' => '11',
			'thumb' => $thumb_name,
			'align' => $text_align,
			'styles' => [
				'hover_color' => isset($element['style']['hover_color']) ? $element['style']['hover_color']['rgba'] : 'rgba(0,0,0,0,5)',
				'font_title' => $element['style']['font_title'],
				//'font_subtitle'=>$element['style']['font_position'],
				'font_description' => $element['style']['font_description'] ?? [],
			],
			'cssid' => $css_id,
			'image_hover' => true,
			'hover_content' => true,
		];

		if (isset($element['style']['background_set'])) {
			$items_args['background_set'] = $element['style']['background_set'];
		}

		$content .= $vePage->display->generate_element_items($items_args, $items);
	} else {
		$content .= '<div class="mem_member_list_empty">' . __('Nebyli nalezeni žádní členové', 'cms_member') . '</div>';
	}

	if ($added) {
		$content .= "<script>
        jQuery(function() {
          mwGetIframeContent().mw_init_element_popup('" . $css_id . ' .mw_element_item_popup .mw_element_item_title a, ' . $css_id . " .mw_element_item_popup .mw_element_item_image_hover');
        });
      </script>";
	}

	if ($members['pages'] > 1) {
		$pagination = paginate_links([
			'base' => get_permalink($post_id) . '%_%',
			'format' => get_option('permalink_structure') ? '?mcpage=%#%' : '&mcpage=%#%',
			'current' => $current_page,
			'total' => $members['pages'],
			'show_all' => false,
			'type' => 'plain',
			'prev_text' => mw_content_icon_set('chevron-left'),
			'next_text' => mw_content_icon_set('chevron-right'),
			'add_fragment' => '#mem_member_user_list',
		]);

		$content .= '<div class="mw_page_navigation">' . $pagination . '</div>';
	}
	$content .= '</div>';

	return $content;
}


function ve_element_members_list($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	if ($added) {
		mwMemberModule()->builderMemberInit($post_id);
	}

	wp_enqueue_script('member_front_script');
	wp_enqueue_style('member_content_css');

	$content = '';
	$memberSections = mwMemberModule()->getMemberSections();
	if (count($memberSections) && isset($element['style']['members'])) {
		$vePage->display->add_enqueue_script('ve_lightbox_script');
		$vePage->display->add_enqueue_style('ve_lightbox_style');

		$element['style']['cols'] = isset($element['style']['cols']) ? intval($element['style']['cols']) : 0;
		$cols = $vePage->display->getAutoCols($element['style']['cols'], count($element['style']['members']), 3, false);

		$image_ratio = $element['style']['image_ratio'] ?? '32';
		$hover_style = $element['style']['hover'] ?? '';
		$text_align = $element['style']['text_align'] ?? 'left';

		$items_set = [];

		foreach ($element['style']['members'] as $member) {
			if (isset($memberSections[$member['member']])) {
				$memberSection = $memberSections[$member['member']];

				$access = $edit_mode || !mwMemberModule()->currentMember()->isLogged() || mwMemberModule()->currentMember()->hasAccess($memberSection->getId());
				$expired = !$edit_mode && mwMemberModule()->currentMember()->hasExpiredAccess($memberSection->getId());

				$title = $member['title'] ?: $memberSection->getName();

				$progress = '';
				$class = '';

				if ($access && isset($element['style']['show_progress'])) {
					$percentage = mwMemberModule()->currentMember()->getProgress($memberSection->getId(), 'member_section', true);
					if ($percentage !== null) {
						$progress = '<div class="mw_member_page_item_progress_container">';
						$progress .= '<div class="mw_member_page_item_progress" style="width: ' . $percentage . '%;"></div>';
						if ($percentage == 100) {
							$progress .= '<div class="mw_member_page_item_progress_finished">' . mw_content_icon_set('check') . '</div>';
						}
						$progress .= '</div>';
					}
				}
				$btitle = $title;
				if (!$access || $expired) {
					$class = 'mw_member_item_noaccess';
					if ($element['style']['style'] === '5') {
						$btitle = '<div class="mw_member_noaccess_info_icon"><i>' . mw_content_icon_set('lock') . '</i></div>' . $title;
					}
				}

				$args = [
					'link' => $memberSection->getUrl(),
					'image' => new Image($member['image']),
					'title' => $btitle,
					'class' => $class,
					'after_image' => $progress,
				];

				if ($element['style']['style'] === '1') {
					$args['image_hover'] = true;
					$args['image_hover_link'] = $args['link'] ? true : false;
					$args['image_hover_content'] = '<h3 class="' . $vePage->display->get_font_class($element['style']['font']) . '">' . $args['title'] . '</h3>';
				}

				if (!$access || $expired) {
					$args['image_hover'] = true;
					$args['image_hover_link'] = true;

					if ($element['style']['style'] === '1') {
						$args['image_hover_content'] = '<div class="mw_member_noaccess_info_icon">
                                <i>' . mw_content_icon_set('lock') . '</i>
                            </div>
                            ' . $args['image_hover_content'];
					} elseif ($element['style']['style'] !== '5') {
						$args['image_hover_content'] = '<div class="mw_member_noaccess_info_icon"><i>' . mw_content_icon_set('lock') . '</i></div>';
					} else {
						$args['image_hover_content'] = '';
					}

					if ($expired) {
						$args['image_hover_content'] .= '<div class="mw_member_item_noacess_info">';
						$args['image_hover_content'] .= '<p>' . __('Členství vypršelo', 'cms_member') . '</p>';
						if ($memberSection->getExtendUrl()) {
							$args['link'] = $memberSection->getExtendUrl();
							$args['image_hover_content'] .= '<div class="mw_member_noacess_but">' . mw_content_icon_set('unlock') . __('Prodloužit', 'cms_member') . '</div>';
						} else {
							$args['link'] = $memberSection->getExpireUrl();
						}
						$args['image_hover_content'] .= '</div>';
					} else {
						$args['image_hover_content'] .= '<div class="mw_member_item_noacess_info">';
						$args['image_hover_content'] .= '<p>' . __('Do této členské sekce nemáte přístup', 'cms_member') . '</p>';
						$link = new Link($member['link']);
						if ($link->getLink()) {
							$but_set = [
								'style' => $element['style']['button'] ?? [],
								'link' => $member['link'],
								'text' => __('Získat přístup', 'cms_member'),
							];

							$args['open_popup'] = true;
							$args['popup_content'] = '<div class="mw_member_section_list_popup">
                            <small>' . __('Získat přístup do členské sekce', 'cms_member') . '</small>
                            <div class="title_element_container">' . $title . '</div>
								<p>' . stripslashes($member['description']) . '</p>
								' . Button::createButton(
										$but_set,
										$vePage->display->element_css,
										'',
										$css_id . ' .ve_content_button',
										$added,
										$edit_mode
									) . '
							</div>';
							$args['image_hover_content'] .= '<div class="mw_member_noacess_but">' . mw_content_icon_set('unlock') . __('Získat přístup', 'cms_member') . '</div>';
						} else {
							$args['link'] = $memberSection->getNoAccessUrl();
						}
						$args['image_hover_content'] .= '</div>';
					}
				}

				$items_set[] = $args;
			}
		}

		$items_args = [
			'style' => $element['style']['style'],
			'cols' => $cols,
			'inside_col_type' => $col_type,
			'autocols' => !$element['style']['cols'] ? true : false,
			'cols_type' => $element['style']['cols_type'] ?? 'cols',
			'image_ratio' => $image_ratio,
			'hover_style' => $hover_style,
			'align' => $text_align,
			'styles' => [
				'hover_color' => isset($element['style']['hover_color']) && $element['style']['style'] == '1' ? $element['style']['hover_color']['rgba'] : 'rgba(0,0,0,0,5)',
				'font_title' => $element['style']['font'],
			],
			'cssid' => $css_id,
			'added' => $added,
		];

		if (isset($element['style']['background_set'])) {
			$items_args['background_set'] = $element['style']['background_set'];
		}

		$content = '<div class="in_element_content mw_member_section_list">';
		$content .= $vePage->display->generate_element_items($items_args, $items_set, $added, $row_set);
		$content .= '</div>';
		if ($added) {
			$content .= "<script>
            jQuery(function() {
              mwGetIframeContent().mw_init_element_popup('" . $css_id . ' .mw_element_item_popup .mw_element_item_title a, ' . $css_id . " .mw_element_item_popup .mw_element_item_image_hover');
            });
          </script>";
		}
	} else {
		$vePage->display->add_element_info(__('Nejsou vybrány žádné členské sekce.', 'cms_member'), 'info');
	}

	return $content;
}
