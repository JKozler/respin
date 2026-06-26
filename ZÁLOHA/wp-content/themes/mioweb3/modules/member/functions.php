<?php

function field_type_member_sections($field, $meta, $group_name, $group_id, $user_id, $all_meta)
{
	$tagname = $group_name . '[' . $field['id'] . ']';
	$tagid = $group_id . '_' . $field['id'];

	$user = null;
	if ($user_id) {
		$user = mwMember::getOneById($user_id);
	}

	MwMemberFields::memberProfileFields($user);
}

function field_type_membership_creator($field, $meta, $group_name, $group_id, $user_id, $all_meta)
{
	$content = $meta ?? [];
	$tagname = $group_name . '[' . $field['id'] . ']';
	$tagid = $group_id . '_' . $field['id'];

	echo MwMemberFields::membershipCreator($content, $tagname, $tagid);
}


function field_type_noaccess_content($field, $meta, $group_name, $group_id, $user_id, $all_meta)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');

	$tagname = $group_name;
	$tagid = $group_id;

	echo mwAdminComponents::inputSublabel([
		'label' => __('Zobrazit stránku:', 'cms_member'),
	]);

	echo mwAdminComponents::selectPage([
		'name' => $tagname . '[noaccess_page_id]',
		'tag_id' => $tagid . '_noaccess_page_id',
		'add_button' => true,
		'edit_button' => true,
		'whisperer' => true,
		'lazy_loading' => true,
	], $all_meta['noaccess_page_id'] ?? '');

	echo '<div class="set_form_subrow">';
	echo mwAdminComponents::inputSublabel([
		'label' => __('Nebo vypsat text (pokud nevyberete stránku):', 'cms_member'),
	]);
	echo mwAdminComponents::textarea([
		'name' => $tagname . '[noaccess_text]',
		'id' => $tagid . '_noaccess_text',
	], stripslashes($all_meta['noaccess_text'] ?? ($field['content']['noaccess_text'] ?? '')));
	echo '</div>';
}
function field_type_month_member($field, $meta, $group_name, $group_id)
{
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	$r = '';

	$content = $meta != '' ? $meta : ($field['content'] ?? '');

	$curyear = date('Y', current_time('timestamp'));
	$curmonth = date('n', current_time('timestamp'));

	$startyear = 2015;
	$endyear = intval($curyear) + 5;

	if ($content) {
		$ce = str_split($content, 4);
		$value = [
			'year' => $ce[0],
			'month' => $ce[1],
		];
	} else {
		$value = [
			'year' => $curyear,
			'month' => $curmonth,
		];
	}

	$r .= '<div class="mw_month_member_container">';

	$r .= '<select name="' . $name . '[month]" id="' . $id . '_unit">';
	for ($month = 1; $month <= 12; $month++) {
		$v = str_pad($month, 2, '0', STR_PAD_LEFT);
		$r .= '<option value="' . $v . '" ' . ($value['month'] == $v ? 'selected="selected"' : '') . '>' . $v . '</option>';
	}
	$r .= '</select>';

	$r .= '<select name="' . $name . '[year]" id="' . $id . '_unit">';
	for ($year = $startyear; $year <= $endyear; $year++) {
		$r .= '<option value="' . $year . '" ' . ($value['year'] == $year ? 'selected="selected"' : '') . '>' . $year . '</option>';
	}
	$r .= '</select>';

	$r .= '</div>';

	echo $r;
}

function field_type_selectmember($field, $meta, $group_name, $group_id, $user_id, $all_meta)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');

	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	echo MwMemberFields::memberSectionSelect($field, $content, $name, $id);
}

function field_type_fapi_notification($field, $meta, $group_id, $tagid, $member_id)
{
	if (!mwApiConnect()->getApi('fapi')->isConnected()) {
		echo mwAdminComponents::messageBox(__('Aby bylo možné vytvářet nové účty pomocí FAPI, je potřeba nejprve zadat přihlašovací údaje k FAPI v <strong>Propojení aplikací</strong>.', 'cms_member'), ['type' => 'error']);
	}

	$memberSection = mwMemberModule()->getMemberSection($member_id);
	if ($memberSection !== null) {
		echo '<div class="member_notification">';
		echo '<div class="member_notification_url"><strong>' . home_url() . '/?add_new_member=' . $memberSection->getId() . '</strong></div>';
		if (count($memberSection->getLevels())) {
			echo '<table class="member_level_notifications_url">';
			foreach ($memberSection->getLevels() as $level) {
				echo '<tr>';
				echo '<td class="mlnu_label">' . __('Do členské úrovně:', 'cms_member') . ' <strong>' . $level->getName() . '</strong></td>';
				echo '<td>' . home_url() . '/?add_new_member=' . $memberSection->getId() . '&addlevel=' . $level->getId() . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}

		echo '</div>';
	}
}

function field_type_notification_atributes($field, $meta, $group_id, $tagid, $member_id)
{
	?>
	<table class="mw_table mw_table_style_1 notification_atribute_list">
		<tr>
			<td><i>level</i></td>
			<td><?php echo __('Nastaví uživateli do jaké nebo do jakých členských úrovní má přístup. Hodnotou je ID členské úrovně. Pokud chceme uživatele vložit do více členských úrovní oddělíme je pomlčou (např. 0-1-5).', 'cms_member'); ?></td>
		</tr>
		<tr>
			<td><i>addlevel</i></td>
			<td><?php echo __('Přidá přístup do členské úrovně a zároveň (na rozdíl od atributu level) zachová ty do kterých už přístup má. Hodnotou je ID členské úrovně. Pokud chceme uživatele vložit do více členských úrovní oddělíme je pomlčou (např. 0-1-5).', 'cms_member'); ?></td>
		</tr>
		<tr>
			<td><i>send_email</i></td>
			<td><?php echo __('Určuje zda se má poslat klientovi email po zavolání notifikace. Defaultně se posílá. Pro vypnutí nasavte hodnotu na 0.', 'cms_member'); ?></td>
		</tr>
		<tr>
			<td><i>date</i></td>
			<td><?php echo __('Nastaví datum registrace na zadanou hodnotu. Defaultně se nastaví na den kdy byla notifikace zavolána. Formát atributu je yyyy-mm-dd.', 'cms_member'); ?></td>
		</tr>
		<tr>
			<td><i>time</i></td>
			<td><?php echo __('Nastaví čas registrace na zadanou hodnotu. Defaultně se nastaví na čas kdy byla notifikace zavolána. Formát atributu je hh:mm.', 'cms_member'); ?></td>
		</tr>
		<tr>
			<td><i>days</i></td>
			<td><?php echo __('Omezí členství na zadaný počet dní. Po vypršení této doby se nelze do členské sekce přihlásit.', 'cms_member'); ?></td>
		</tr>
		<tr>
			<td><i>setexp</i></td>
			<td><?php echo __('Nastaví datum expirace členství na konkrétní hodnotu. Formát hodnoty je dd.mm.yyyy. Pokud nastavíte hodnotu na 0, tak se časově omezené členství zruší a členství přejde do neomezeného režimu.', 'cms_member'); ?></td>
		</tr>
		<tr>
			<th colspan="2"><?php echo __('Měsíční členství', 'cms_member'); ?></th>
		</tr>
		<tr>
			<td><i>month</i></td>
			<td><?php echo __('Zpřístupní uživateli zadané měsíce ve formátu yyyymm. Více měsíců musí být odděleno pomlčkou. Hodnotou může být taky rok ve formátu yyyy, který zpřístupní všechny měsíce daného roku.', 'cms_member'); ?></td>
		</tr>
		<tr>
			<td><i>month_num</i></td>
			<td><?php echo __('Zpřístupní uživateli zadaný počet následujících měsíců, které zatím nemá koupené.', 'cms_member'); ?></td>
		</tr>
	</table>
	<?php
}

function field_type_fapi_notification_log($field, $meta, $group_id, $tagid, $member_id)
{
	$notifications_option = get_option('mem_notification_debug');

	$notifications = $notifications_option[$member_id] ?? null;

	if ($notifications && is_array($notifications)) {
		$notifications = array_reverse($notifications);
		echo '<table class="mw_table mw_notifications_log ve_page_statistic_field mw_table_style_2">';

		$i = 1;
		foreach ($notifications as $not) {
			$class = $i ? 'class="odd"' : '';

			echo '<tr ' . $class . '>';
			echo '<td>';
			echo '<div>';
			echo date('d.m.Y H:i:s', $not['time']);
			echo ' <span style="color: ' . ($not['status'] ? '#4ea600' : '#d30000') . '">' . $not['error'] . '</span>';
			echo '</div>';
			if (isset($not['url'])) {
				echo '<small>' . $not['url'] . '</small>';
			}
			echo '</td>';
			echo '</tr>';

			$i = $i ? 0 : 1;
		}
		echo '</table>';
	} else {
		?>
			<div><?php echo __('Zatím nebyly provedeny žádné notifikace.', 'cms_member'); ?></div>
		<?php
	}
}

function field_type_member_fields($field, $meta, $group_name, $group_id, $userId, $all_meta)
{
	echo MwMemberFields::memberFields($userId, $field, getTagName($group_name, $field['id']));
}

if (!function_exists('insert_with_markers_on_top')) {

	function insert_with_markers_on_top($filename, $marker, $insertion)
	{
		if ((!file_exists($filename) && (!is_writable(dirname($filename)) || !touch($filename))) || !is_writable($filename)) {
			return false;
		}

		if (!is_array($insertion)) {
			$insertion = explode("\n", $insertion);
		}

		if (!is_resource($fp = fopen($filename, 'rb+'))) {
			return false;
		}

		$start_marker = '# BEGIN ' . $marker;
		$end_marker = '# END ' . $marker;

		// Attempt to get a lock. If the filesystem supports locking, this will block until the lock is acquired.
		flock($fp, LOCK_EX);

		$lines = [];
		while (!feof($fp)) {
			$lines[] = rtrim(fgets($fp), "\r\n");
		}

		// Split out the existing file into the preceding lines, and those that appear after the marker
		$pre_lines = $post_lines = $existing_lines = [];
		$found_marker = $found_end_marker = false;
		foreach ($lines as $line) {
			if (!$found_marker && strpos($line, $start_marker) !== false) {
				$found_marker = true;

				continue;
			}

			if (! $found_end_marker && strpos($line, $end_marker) !== false) {
				$found_end_marker = true;

				continue;
			}

			if (! $found_marker) {
				$pre_lines[] = $line;
			} elseif ($found_marker && $found_end_marker) {
				$post_lines[] = $line;
			} else {
				$existing_lines[] = $line;
			}
		}

		// Check to see if there was a change
		if ($existing_lines === $insertion) {
			flock($fp, LOCK_UN);
			fclose($fp);

			return true;
		}

		// Generate the new file data
		$new_file_data = $found_marker && $found_end_marker ? implode("\n", array_merge(
				$pre_lines,
				[$start_marker],
				$insertion,
				[$end_marker],
				$post_lines
			)) : implode("\n", array_merge(
				[$start_marker],
				$insertion,
				[$end_marker],
				$pre_lines
			));

		// Write to the start of the file, and truncate it to that length
		fseek($fp, 0);
		$bytes = fwrite($fp, $new_file_data);
		if ($bytes) {
			ftruncate($fp, ftell($fp));
		}
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);

		return (bool) $bytes;
	}
}
