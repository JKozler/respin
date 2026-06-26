<?php
use Mioweb\Member\MonthMembership;

class MwMemberFields extends MwFields
{
	// member section select
	public static function memberSectionSelect($fieldSetting, $val, $fieldName, $fieldId = '', $itemId = 0): string
	{
		$members = mwMemberModule()->getMemberSections();

		if (count($members)) {
			$options = [];
			if (isset($fieldSetting['empty'])) {
				$options[] = [
					'name' => $fieldSetting['empty'],
					'value' => '',
				];
			}
			foreach ($members as $member) {
				$options[] = [
					'name' => $member->getName(),
					'value' => $member->getId(),
				];
			}

			if (isset($fieldSetting['show_levels']) && $fieldSetting['show_levels']) {
				$selectName = $fieldName . '[section]';
				$selectId = $fieldId . '_section';
				$selectVal = $val['section'] ?? '';
			} else {
				$selectName = $fieldName;
				$selectId = $fieldId;
				$selectVal = $val;
			}

			$content = '<div class="member_section_select_container">';

			$content .= mwAdminComponents::select([
				'name' => $selectName,
				'tag_id' => $selectId,
				'options' => $options,
			], $selectVal, 'mw_member_section_select');

			if (isset($fieldSetting['show_levels']) && $fieldSetting['show_levels']) {
				$i = 1;
				foreach ($members as $member) {
					if ($member->hasLevels()) {
						$content .= '<div id="member_levels_container_' . $member->getId() . '" class="member_levels_container ' . ((!isset($val['section']) && $i == 1) || (isset($val['section']) && $val['section'] == $member->getId()) ? 'member_levels_container_v' : '') . '">';
						$content .= '<div class="label">';
						$content .= '<span>' . ($fieldSetting['sublabel'] ?? __('Zařadit do členských úrovní', 'cms_member')) . '</span>';
						if (!isset($fieldSetting['sublabel'])) {
							$content .= mwAdminComponents::tooltip([
								'text' => __('Pokud zaškrtnete některou z členských úrovní, tak na tuto stránku budou mít přístup pouze ti členové, kteří mají přístup alespoň do jedné ze zaškrtnutých členských úrovní. Pokud nezaškrtnete nic, bude stránka přístupná pro všechny členy vybrané členské sekce.', 'cms_member'),
							]);
						}
						$content .= '</div>';
						/* back compatibility @TODO repair data in new migration and remove this code */
						$lVals = $val['levels'][$member->getId()] ?? ($val[$member->getId()]['levels'] ?? ($val[$member->getId()] ?? []));
						/* end back compatibility */

						$content .= '<div class="member_level_items_list">';
						foreach ($member->getLevels() as $level) {
							$content .= '<div class="member_level_item">';
							$content .= mwAdminComponents::checkbox([
								'name' => $fieldName . '[levels][' . $member->getId() . '][' . $level->getId() . ']',
								'label' => $level->getName(),
								'value' => $level->getId(),
							], isset($lVals[$level->getId()]) ? 1 : 0);
							$content .= '</div>';
						}
						$content .= '</div>';
						$content .= '</div>';
					}
					$i++;
				}
			}

			$content .= '</div>';

			return $content;
		} else {
			return mwAdminComponents::messageBox(sprintf(__('Není vytvořena žádná členská sekce. Členskou sekci lze vytvořit v <a href="%s" target="_blank">administraci členských sekcí</a>.', 'cms_member'), mwSetting()->getObject('member_sections')->getUrl()), ['type' => 'error']);
		}
	}

	// member action setting
	public static function membershipCreator($val, $fieldName, $fieldId = '', $itemId = 0): string
	{
		$members = mwMemberModule()->getMemberSections();
		if (count($members)) {
			$content = '<div class="mw_membership_creator_container">';

			// select member
			$content .= MwMemberFields::memberSectionSelect([
				'show_levels' => true,
				'sublabel' => __('Vytvořit přístup do členských úrovní', 'cms_member'),
			], $val, $fieldName, $fieldId);

			// membership type
			$content .= '<div class="set_form_subrow">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Nastavit typ členství na', 'cms_member'),
			]);

			$membershipType = $val['membership_type'] ?? 'nolimit';

			$content .= mwAdminComponents::select([
				'name' => $fieldName . '[membership_type]',
				'id' => $fieldId . '_membership_type',
				'options' => [
					[
						'value' => 'nolimit',
						'name' => __('Neomezené členství', 'cms_member'),
					],
					[
						'value' => 'limit',
						'name' => __('Omezit členství počtem dní', 'cms_member'),
					],
					[
						'value' => 'limit_date',
						'name' => __('Omezit členství do data', 'cms_member'),
					],
					/*
					[
						'value' => 'month',
						'name' => __('Měsíční členství', 'cms_member'),
					],*/
				],
			], $membershipType, 'mw_membership_creator_type');
			$content .= '</div>';

			// limited membership by days
			$content .= '<div class="set_form_subrow mw_membership_creator_days ' . ($membershipType !== 'limit' ? 'cms_nodisp' : '') . '">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Doba platnosti členství', 'cms_member'),
			]);
			$content .= mwAdminComponents::inputNumber([
				'name' => $fieldName . '[membership_days]',
				'id' => $fieldId . '_membership_days',
				'unit' => __('dní', 'cms_member'),
			], $val['membership_days'] ?? 30);
			$content .= '</div>';

			// limited membership by date
			$content .= '<div class="set_form_subrow mw_membership_creator_limit_date ' . ($membershipType !== 'limit_date' ? 'cms_nodisp' : '') . '">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Platnost členství do data', 'cms_member'),
			]);
			$content .= mwAdminComponents::dateInput([
				'name' => $fieldName . '[membership_limit_date]',
			], $val['membership_limit_date'] ?? '');
			$content .= '</div>';

			// advanced
			$content .= '<div class="set_form_subrow">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Pokročilé', 'cms_member'),
			]);
			/*
			$content .= mwAdminComponents::switch([
				'name' => $fieldName.'[remove_levels]',
				'switch_label' => __('Odstranit existujícího člena z původních úrovní', 'cms_member'),
			],isset($val['remove_levels'])? 1 : 0);
			*/
			$content .= '<div>';
			$content .= mwAdminComponents::switch([
				'name' => $fieldName . '[nosend_email]',
				'switch_label' => __('Neposílat informační email', 'cms_member'),
			], isset($val['nosend_email']) ? 1 : 0);
			$content .= '</div>';
			$content .= '<div>';
			$content .= mwAdminComponents::switch([
				'name' => $fieldName . '[set_date]',
				'switch_label' => __('Nastavit pevné datum registrace člena', 'cms_member'),
			], isset($val['set_date']) ? 1 : 0, 'mw_membership_creator_set_date');
			$content .= '</div>';
			$content .= '</div>';

			// date and time of registration
			$content .= '<div class="set_form_subrow mw_membership_creator_date ' . (!isset($val['set_date']) ? 'cms_nodisp' : '') . '">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Nastavit datum registrace na', 'cms_member'),
			]);
			$content .= mwAdminComponents::dateTimeInput([
				'name' => $fieldName . '[date]',
			], $val['date'] ?? []);
			$content .= '</div>';

			$content .= '</div>';
		} else {
			return mwAdminComponents::messageBox(sprintf(__('Není vytvořena žádná členská sekce. Členskou sekci lze vytvořit v <a href="%s" target="_blank">administraci členských sekcí</a>.', 'cms_member'), mwSetting()->getObject('member_sections')->getUrl()), ['type' => 'error']);
		}

		return $content;
	}

	public static function memberFields($userId = 0, $field = [], $name = 'user')
	{
		$userInfo = [];
		$user = null;
		if ($userId) {
			$user = mwMember::getOneById($userId);
		}

		$content = '';

		// catalogue
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel([
			'label' => __('Zobrazení v katalogu členů', 'cms_member'),
		]);
		$content .= '<div class="set_form_subrow">';
		$content .= mwAdminComponents::switch([
			'name' => $name . '[show_member]',
			'switch_label' => __('Zobrazit v katalogu členů', 'cms_member'),
		], $user ? $user->showInMemberList() : 0);
		$content .= '</div>';
		$content .= '<div class="set_form_subrow">';
		$content .= mwAdminComponents::switch([
			'name' => $name . '[member_fields][hide_email]',
			'switch_label' => __('Skrýt email v katalogu členů', 'cms_member'),
		], $user ? $user->hideEmailInMemberList() : 0);
		$content .= '</div>';
		$content .= '</div>';

		// Domain
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel([
			'label' => __('Obor podnikání', 'cms_member'),
			'tooltip' => __('Obor podnikání se zobrazuje v detailu člena v katalogu členů.', 'cms_member'),
		]);
		$content .= mwAdminComponents::input([
			'name' => $name . '[member_fields][domain]',
		], $user ? $user->getDomain() : '');
		$content .= '</div>';

		// custom fields
		$fields = MwMemberCustomField::getAll();

		foreach ($fields['items'] as $field) {
			$content .= '<div class="set_form_row">';
			$content .= mwAdminComponents::inputLabel([
				'label' => $field->getName(),
				'tooltip' => $field->getExcerpt(),
			]);
			if ($field->getType() == 'textarea') {
				$content .= mwAdminComponents::textarea([
					'name' => $name . '[custom_fields][' . $field->getId() . ']',
				], $user ? $user->getCustomField($field->getId()) : '');
			} else {
				$content .= mwAdminComponents::input([
					'name' => $name . '[custom_fields][' . $field->getId() . ']',
				], $user ? $user->getCustomField($field->getId()) : '');
			}

			$content .= '</div>';
		}

		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::messageBox(sprintf(__('Údaje o členovi můžete rozšířit o %s. Zadané údaje se potom budou zobrazovat v katalogu členů.', 'cms_member'), '<a href="' . mwSetting()->getObject(MW_MEMBER_CUSTOM_FIELDS_SLUG)->getUrl() . '" target="_blank">' . __('vlastní pole členů', 'cms_member') . '</a>'), [
			'type' => 'info_gray',
		]);
		$content .= '</div>';

		return $content;
	}

	public static function memberProfileFields(?mwMember $user): void
	{
		if (count(mwMemberModule()->getMemberSections())) {
			foreach (mwMemberModule()->getMemberSections() as $member) {
				$id = $member->getId();

				$checked = 0;
				$start = '';
				$end = '';
				$time = '';
				if ($user !== null && $user->hasAccess($id)) {
					$checked = 1;
					$startTime = $user->getMembership($id)->getStart();
					$start = date('d.m.Y', $startTime);
					$time = date('H:i', $startTime);
					if ($user->getMembership($id)->getEnd()) {
						$end = date('d.m.Y', $user->getMembership($id)->getEnd());
					}
				}

				?>
				<div class="mw_setting_box mw_user_member_item <?php if ($checked) { echo 'checked';} ?>">
					<div class="mw_setting_box_head mw_user_member_item_head">
						<?php
						echo mwAdminComponents::checkbox([
							'name' => 'member[' . $id . '][section]',
							'label' => $member->getName(),
							'style' => 'blue',
						], $checked, 'mw_user_member_item_checkbox');
						?>
						<div class="mw_user_member_item_head_setting">
							<div class="mw_user_member_hs_set">
								<label><?php echo __('Datum registrace', 'cms_member'); ?></label>
								<?php
								echo mwAdminComponents::input([
									'name' => 'member[' . $id . '][start]',
								], $start, 'cms_datepicker');
								?>
							</div>
							<div class="mw_user_member_hs_set">
								<label><?php echo __('Čas registrace', 'cms_member'); ?></label>
								<?php
								echo mwAdminComponents::input([
									'name' => 'member[' . $id . '][time]',
								], $time, 'cms_timepicker');
								?>
							</div>
							<div class="mw_user_member_hs_set">
								<label><?php echo __('Členství do', 'cms_member'); ?></label>
								<?php
								echo mwAdminComponents::input([
									'name' => 'member[' . $id . '][end]',
								], $end, 'cms_datepicker');
								?>
							</div>
						</div>
					</div>

					<?php

					/* add deleted months of member */
					$months = $member->getMonths();
					if ($user && $user->getMembership($id)) {
						$userMonths = $user->getMembership($id)->getMembershipMonths();
						$months += $userMonths;
						ksort($months);
					}

					if ($member->hasLevels() || count($months)) {
						echo '<div class="mw_setting_box_content mw_user_member_item_body">';

						if ($member->hasLevels()) {
							echo '<div class="mw_user_member_item_list mw_user_member_item_list_levels">';
							echo '<div class="mw_user_member_item_list_label">' . __('Zařadit do členských úrovní', 'cms_member') . '</div>';
							echo '<div class="mw_user_member_item_list_body">';
							foreach ($member->getLevels() as $level) {
								?>
								<div class="mw_user_member_list_item">
									<?php
									echo mwAdminComponents::checkbox([
										'name' => 'member[' . $id . '][levels][' . $level->getId() . ']',
										'label' => $level->getName(),
										'value' => $level->getId(),
									], $user && $user->getMembership($id) ? $user->getMembership($id)->hasLevelAccess([$level->getId()]) : false);
									?>
								</div>
								<?php
							}
							echo '</div>';
							echo '</div>';
						}

						if (count($months)) {
							echo '<div class="mw_user_member_item_list">';
							echo '<div class="mw_user_member_item_list_label">' . __('Zpřístupnit měsíční obsah', 'cms_member') . '</div>';
							echo '<div class="mw_user_member_item_list_body">';
							foreach ($months as $month) {
								echo '<div class="mw_user_member_list_item">';
								echo mwAdminComponents::checkbox([
									'name' => 'member[' . $id . '][months][' . $month->getMonth() . ']',
									'label' => $month->getName(),
									'value' => $month->getMonth(),
								], $user && $user->getMembership($id) ? $user->getMembership($id)->hasMonthAccess($month->getMonth()) : false);
								echo '</div>';
							}
							echo '</div>';
							echo '</div>';
						}

						echo '</div>';
					}
				echo '</div>';
			}
		} else {
			echo '<div class="mw_setting_box mw_setting_box_content">';
			echo mwAdminComponents::messageBox(sprintf(__('Není vytvořena žádná členská sekce. Nejdříve je nutné <a href="%s">vytvořit členskou sekci</a>', 'cms_member'), mwSetting()->getObject('member_sections')->getUrl()), ['type' => 'info_gray']);
			echo '</div>';
		}
	}

	public static function memberSectionsListItemHead($args, $item, $i)
	{
		$title = '';
		if (isset($item['title']) && $item['title']) {
			$title = $item['title'];
		} elseif (isset($item['member']) && $item['member']) {
			$ms = mwMemberModule()->getMemberSection($item['member']);
			if ($ms !== null) {
				$title = $ms->getName();
			}
		}

		return $title;
	}

}
