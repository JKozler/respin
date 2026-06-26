<?php

namespace Mioweb\Member;

use MwMemberCustomField;
use mwUser;

class MemberProfileAdmin
{

	public function __construct()
	{
		add_action('wp_footer', [$this, 'memberProfile']);

		if (isset($_POST['save_profile'])) {
			add_action('init', [$this, 'update_user_profile']);
		}
	}

	function memberProfile()
	{
		if (mwMemberModule()->isMemberPage()) {
			$member = mwMemberModule()->currentMember();

			?>
			<div id="member_profile_background"></div>
			<div id="member_profile">
				<h2><?php echo __('Můj profil', 'cms_member'); ?></h2>
				<form method="post" action="" enctype="multipart/form-data">
					<div class="member_profile_row member_profile_row_login">
						<div class="label"><?php echo __('Uživatelské jméno (nelze změnit)', 'cms_member'); ?></div>
						<span class="noinput"><?php echo $member->getLogin(); ?></span>
					</div>
					<div class="member_profile_row member_profile_row_first_name">
						<label><?php echo __('Křestní jméno', 'cms_member'); ?> </label>
						<input class="text" type="text" name="user[first_name]" value="<?php echo $member->getFirstName(); ?>" />
					</div>
					<div class="member_profile_row member_profile_row_last_name">
						<label><?php echo __('Příjmení', 'cms_member'); ?></label>
						<input class="text" type="text" name="user[last_name]" value="<?php echo $member->getLastName(); ?>" />
					</div>
					<div class="member_profile_row member_profile_row_last_domain">
						<label><?php echo __('Můj obor', 'cms_member'); ?></label>
						<input class="text" type="text" name="member_fields[domain]" value="<?php echo $member->getDomain(); ?>" />
					</div>
					<div class="member_profile_row member_profile_row_email">
						<label for="user_email"><?php echo __('E-mail', 'cms_member'); ?> <span><?php echo __('(povinný)', 'cms_member'); ?></span></label>
						<input class="text" type="text" name="user[user_email]" value="<?php echo $member->getEmail(); ?>" />
					</div>
					<div class="member_profile_row member_profile_row_description">
						<label for="description"><?php echo __('O mně', 'cms_member'); ?></label>
						<textarea class="text" type="text" name="user[description]" ><?php echo $member->getDescription(); ?></textarea>
					</div>

					<div class="member_profile_row member_profile_row_picture">
						  <label><?php echo __('Profilový obrázek', 'cms_member'); ?></label>
						  <div class="member_profile_avatar_row"><?php echo $member->getAvatar(); ?> <?php echo __('Svůj profilový obrázek (avatar) si můžete nastavit na adrese', 'cms_member') . ' <a target="_blank" href="https://cs.gravatar.com/">gravatar.com</a>. <small>' . __('Výhodou této služby je, že si bude váš profilový obrázek pamatovat pro všechny weby postavené na wordpressu, a když se kdekoli registrujete nebo vložíte komentář pod stejným e-mailem, bude se tento profilový obrázek automaticky zobrazovat.', 'cms_member'); ?></small></div>
					</div>

					<?php
					// custom fields
					$custom_fields = MwMemberCustomField::getAll();

					if ($custom_fields['count']) {
						foreach ($custom_fields['items'] as $field) { ?>
							<div class="member_profile_row member_profile_row_<?php echo $field->getId(); ?>">
								<label for="user_<?php echo $field->getId(); ?>"><?php echo $field->getName() ?></label>
								<?php

								$val = $member->getCustomField($field->getId());
								if ($field->getType() == 'text') {
									echo '<input type="text" class="text" value="' . $val . '" name="member_custom_field[' . $field->getId() . ']" />';
								} else {
									echo '<textarea class="text" name="member_custom_field[' . $field->getId() . ']" rows="5" cols="30">' . $val . '</textarea>';
								}

								if ($field->getExcerpt()) {
									echo '<p class="description">' . $field->getExcerpt() . '</p>';
								}
								?>

							</div>
							<?php
						}
					}
					?>

					<h2><?php echo __('Kontaktní informace', 'cms_member'); ?></h2>

					<div class="member_profile_row member_profile_row_url">
						  <label for="user_url"><?php echo __('Webové stránky', 'cms_member'); ?></label>
						  <input class="text" type="text" name="user[user_url]" value="<?php echo $member->getWebsite(); ?>" />
					</div>

					<?php

					$contactMethods = mwUser::getContactMethods();
					$content = '';

					foreach ($contactMethods as $mKey => $method) {
						?>
						<div class="member_profile_row member_profile_row_<?php echo $mKey; ?>">
							<label for="<?php echo $mKey; ?>"><?php echo $method; ?></label>
							<input class="text" type="text" name="user[<?php echo $mKey; ?>]" value="<?php echo $member->getContactInfo($mKey); ?>" />
						</div>
						<?php
					}
					?>

					<h2><?php echo __('Zobrazení v katalogu členů', 'cms_member'); ?></h2>
					<div class="member_profile_row_show_member">
						<input id="mem_fields_show_member" type="checkbox" value="1" <?php if ($member->showInMemberList()) { echo 'checked="checked"';} ?>" name="show_member" />
						<label for="mem_fields_show_member"><?php echo __('Zobrazit můj profil v katalogu členů', 'cms_member') ?></label>
					</div>
					<div class="member_profile_row_hide_email">
						<input id="mem_fields_hide_email" type="checkbox" value="1" <?php if ($member->hideEmailInMemberList()) { echo 'checked="checked"';} ?>" name="member_fields[hide_email]" />
						<label for="mem_fields_hide_email"><?php echo __('Nezobrazovat můj email v katalogu členů', 'cms_member') ?></label>
					</div>


					<h2><?php echo __('Nové heslo', 'cms_member'); ?></h2>

					<div class="member_profile_row member_profile_row_password">
						<label><?php echo __('Heslo', 'cms_member'); ?></label>
						<input class="text" type="password" name="user[user_pass]" autocomplete="new-password" value="<?php echo $_POST['USER']['user_pass'] ?? ''; ?>" />
					</div>
					<div class="member_profile_row member_profile_row_password2">
						<label><?php echo __('Znovu heslo', 'cms_member'); ?></label>
						<input class="text" type="password" name="pass2" autocomplete="new-password" value="<?php echo $_POST['pass2'] ?? ''; ?>" />
					</div>

					<div class="member_profile_button_row">
						<input class="member_profile_button" type="submit" value="<?php echo __('Uložit profil', 'cms_member'); ?>" name="save_profile"/>
						<input type="hidden" value="<?php echo $member->getId(); ?>" name="user[ID]"/>
						<input type="hidden" value="<?php echo mwMemberModule()->getPostId(); ?>" name="post_id"/>
					</div>

				</form>
				<a id="member_close_profile" href="#"><?php echo __('Zavřít profil', 'cms_member'); ?></a>
			</div>
			<?php
		}
	}

	function update_user_profile()
	{
			$user = $_POST['user'];
			$error = 0;

			if ($user['first_name'] != '' && $user['last_name'] != '') {
			$user['display_name'] = $user['first_name'] . ' ' . $user['last_name'];
			} elseif ($user['first_name'] != '') {
			$user['display_name'] = $user['first_name'];
			}

			if ($user['user_email'] != '' && is_email($user['user_email'])) {
			if (!empty($user['user_pass'])) {
				if (strlen($user['user_pass']) > 4) {
					if ($user['user_pass'] != $_POST['pass2']) {
						$error = 3;
					}
				} else {
					$error = 2;
				}
			} else {
				unset($user['user_pass']);
			}
			} else {
			$error = 1;
			}

			if (!$error) {
			wp_update_user($user);
			if (isset($_POST['member_custom_field'])) {
				update_user_meta($user['ID'], 'member_custom_field', $_POST['member_custom_field']);
			}
			if (isset($_POST['member_fields'])) {
				update_user_meta($user['ID'], 'member_fields', $_POST['member_fields']);
			}
			if (isset($_POST['show_member'])) {
				update_user_meta($user['ID'], 'mw_show_inmemberlist', $_POST['show_member']);
			} else {
				delete_user_meta($user['ID'], 'mw_show_inmemberlist');
			}
			}
			wp_redirect(get_permalink($_POST['post_id']) . '?profile_message=' . $error);
			die();
	}

}
