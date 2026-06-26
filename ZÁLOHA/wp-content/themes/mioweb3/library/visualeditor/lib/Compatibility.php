<?php

namespace visualeditor;

class Compatibility
{

	public function __construct()
	{
	}
	public static function setNewCompatibility($code): array
	{
		if ($code['type'] == 'classic_bullets') {
			if (isset($code['style']['font'])) {
				$code['style']['text_font'] = $code['style']['font'];
			}
			$code['type'] = 'bullets'; // remove classic bullets element
		} elseif ($code['type'] == 'video' && isset($code['style']['setting'])) {
			if (isset($code['style']['setting']['autoplay'])) {
				$code['style']['autoplay'] = 1;
			}
			if (isset($code['style']['setting']['hide_control'])) {
				$code['style']['hide_control'] = 1;
			}
		} elseif ($code['type'] == 'testimonials' && !isset($code['style']['background_set'])) {
			if (in_array($code['style']['style'], ['1', '2', '3', '4'])) {
				$code['style']['background_set'] = [
					'corner' => '2',
					'shadow' => '2',
					'border' => '1',
				];
			}
			if ($code['style']['style'] == '2' || $code['style']['style'] == '3') {
				$code['style']['image_size'] = '2';
			} elseif ($code['style']['style'] == '1') {
				$code['style']['style'] = '2';
			}
		} elseif ($code['type'] == 'image' && !isset($code['style']['img_style'])) {
			if ($code['style']['style'] == '1') {
				$code['style']['img_style'] = '1';
			} elseif ($code['style']['style'] == '2' || $code['style']['style'] == '3') {
				$code['style']['img_style'] = '1';
				$code['style']['shadow'] = '5';
			} elseif ($code['style']['style'] == '4' || $code['style']['style'] == '5') {
				$code['style']['img_style'] = '2';
				if ($code['style']['style'] == '4') {
					$code['style']['corner'] = '2';
				}
				$code['style']['shadow'] = '2';
			} else {
				$code['style']['img_style'] = '3';
			}
		} elseif ($code['type'] === 'faq' && !isset($code['style']['background_set'])) {
				$code['style']['background_set'] = [
					'corner' => '',
					'shadow' => '',
					'border' => '1',
					'color' => $code['style']['background-color'],
				];
		} elseif ($code['type'] === 'member_login' && isset($code['style']['loginto']) && $code['style']['loginto'] !== '') {
			if (function_exists('mwMemberModule') && !mwMemberModule()->memberSectionIdExist($code['style']['loginto'])) {
				$code['style']['loginto'] = mwMemberModule()->getConvertTableSectionId($code['style']['loginto']) ?? '';
			}
		} elseif ($code['type'] === 'member_regform' && isset($code['style']['reginto']['section']) && $code['style']['reginto']['section'] !== '') {
			if (function_exists('mwMemberModule') && !mwMemberModule()->memberSectionIdExist($code['style']['reginto']['section'])) {
				$oldId = $code['style']['reginto']['section'];
				$newId = mwMemberModule()->getConvertTableSectionId($oldId) ?? '';
				$code['style']['reginto']['section'] = $newId;
				if ($newId && isset($code['style']['reginto'][$oldId]['levels'])) {
					$levels = [];
					foreach ($code['style']['reginto'][$oldId]['levels'] as $key => $level) {
						$newLevelId = mwMemberModule()->getConvertTableLevelId($oldId, $key);
						if ($newLevelId) {
							$levels[$newLevelId] = $newLevelId;
						}
					}
					$code['style']['reginto']['levels'][$newId] = $levels;
				}
			}
		} elseif ($code['type'] === 'member_progress' && isset($code['style']['member']) && $code['style']['member'] !== '') {
			if (function_exists('mwMemberModule') && !mwMemberModule()->memberSectionIdExist($code['style']['member'])) {
				$code['style']['member'] = mwMemberModule()->getConvertTableSectionId($code['style']['member']) ?? '';
			}
		} elseif ($code['type'] === 'member_users' && isset($code['style']['member_section']) && $code['style']['member_section']['section'] !== '') {
			if (function_exists('mwMemberModule') && !mwMemberModule()->memberSectionIdExist($code['style']['member_section']['section'])) {
				$oldId = $code['style']['member_section']['section'];
				$newId = mwMemberModule()->getConvertTableSectionId($oldId) ?? '';
				$code['style']['member_section']['section'] = $newId;
				if ($newId && isset($code['style']['member_section'][$oldId]['levels'])) {
					$levels = [];
					foreach ($code['style']['member_section'][$oldId]['levels'] as $level) {
						$newLevelId = mwMemberModule()->getConvertTableLevelId($oldId, $level);
						if ($newLevelId) {
							$levels[$newLevelId] = $newLevelId;
						}
					}
					$code['style']['member_section']['levels'][$newId] = $levels;
				}
			}
		} elseif ($code['type'] === 'members_list' && isset($code['style']['members']) && count($code['style']['members']) && function_exists('mwMemberModule')) {
			foreach ($code['style']['members'] as $key => $member) {
				$memberId = $member['member'];

				if (!(bool) $memberId) {
					continue;
				}

				if (is_string($memberId) && is_numeric($memberId)) {
					$memberId = (int) $memberId;
				}

				if (!mwMemberModule()->memberSectionIdExist($memberId)) {
					$code['style']['members'][$key]['member'] = mwMemberModule()->getConvertTableSectionId($memberId) ?? '';
				}
			}
		}

		return $code;
	}
}
