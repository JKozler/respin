<?php declare(strict_types=1);

namespace Mioweb\Library\MwaConnect;

use Mioweb\Member\Notifications;

class MwaConnect
{

	public function connect()
	{
		// connect user
		if (isset($_POST['__majv'])) {
			$this->connect_user();
		}

		// delete license info after change on license server
		if (isset($_POST['mw_update_license'])) {
			delete_transient('cms_license');
			die();
		}
	}

	function connect_user()
	{
		$log = $this->check_login();

		if ($log !== 'success') {
			mwlog(MWLS_GENERAL, '[MWACONNECT] Error while connecting to user. Error: ' . $log, MWLL_WARNING);
			$redirect_to = wp_login_url();
			wp_safe_redirect($redirect_to);
		}

		exit;
	}

	function check_login()
	{
		if (!isset($_POST['token']) || !$_POST['token']) {
			return false;
		}

		$user_info = $this->getUserInfo($_POST['token']);

		if (isset($user_info->error)) {
			return false;
		}

		$mail = $user_info->email;
		$cid = $user_info->id;

		$update_user = [
			'user_email' => $mail,
			'role' => 'administrator',
		];

		$user_by_mail = $this->getUserByMail($mail);
		$user_by_cid = $this->getUserByCid($cid);
		$user = false;

		$scope = isset($user_info->scope) ? json_decode($user_info->scope) : null;
		$allowCreate = $scope === null || (isset($scope->wp->wp_role->mode) && $scope->wp->wp_role->mode === 'add');

		// email exist, cid exist, same user
		if ($user_by_mail && $user_by_cid && $user_by_cid->ID == $user_by_mail->ID) {
			$user = $user_by_mail;
		} else {
			// delete this customerId from all users
			$this->clearCid($cid);

			// email exist, cid not exist
			if ($user_by_mail && !$user_by_cid) {
				$user = $user_by_mail;
				update_user_meta($user->ID, 'mw_user_connect_id', $cid);
			} elseif (!$user_by_mail && $user_by_cid) {
				// email not exist, cid exist
				$user = $user_by_cid;
				update_user_meta($user->ID, 'mw_user_connect_id', $cid);
			} elseif (!$user_by_mail && !$user_by_cid) {
				// email not exist, cid not exist
				if (!$allowCreate) {
					return 'User creation is disabled.';
				}

				$user_id = $this->createUser($mail);
				if (is_wp_error($user_id)) {
					return $user_id->get_error_message();
				}
				$user = get_user_by('email', $mail);
				update_user_meta($user->ID, 'mw_user_connect_id', $cid);
			} elseif ($user_by_mail->ID != $user_by_cid->ID) {
				// email exist, cid exist, different users
				delete_user_meta($user_by_cid->ID, 'mw_user_connect_id');
				$user = $user_by_mail;
				update_user_meta($user->ID, 'mw_user_connect_id', $cid);
			}
		}

		if (is_wp_error($user)) {
			return $user->get_error_message();
		}

		if (!$user) {
			return 'User is empty.';
		}

		if ($user_info->first_name && !$user->first_name) {
			$update_user['first_name'] = $user_info->first_name;
		}
		if ($user_info->last_name && !$user->last_name) {
			$update_user['last_name'] = $user_info->last_name;
		}
		if (!$user->display_name && $user_info->first_name && $user_info->last_name) {
			$update_user['display_name'] = $user_info->first_name . ' ' . $user_info->last_name;
		}

		// update info in user
		$update_user['ID'] = $user->ID;

		// set role & member sections
		if ($scope !== null) {
			// set role
			$roles = ['subscriber', 'member', 'contributor', 'author', 'editor', 'administrator'];
			$new_role = $scope->wp->wp_role->role;
			$new_role_key = array_search($new_role, $roles);

			$user_role_key = 0;
			foreach ($user->roles as $role) {
				$role_key = array_search($role, $roles);
				if ($role_key > $user_role_key) {
					$user_role_key = $role_key;
				}
			}

			if ($new_role_key >= $user_role_key) {
				$update_user['role'] = $new_role;
			}

			// set member sections
			if (isset($scope->wp->mioweb_sections) && is_array($scope->wp->mioweb_sections)) {
				foreach ($scope->wp->mioweb_sections as $mw_section) {
					if ($mw_section->mode == 'delete') {
						if (isset($mw_section->levels)) {
							Notifications::stopMembershipByEmail($mail, $mw_section->section, $mw_section->levels);
						} else {
							Notifications::stopMembershipByEmail($mail, $mw_section->section);
						}
					} else { // add to member section
						$client = [
							'user_email' => $mail,
						];
						$params = [
							'send_email' => false,
						];
						if (isset($mw_section->levels)) {
							$params['levels'] = $mw_section->levels;
						}
						Notifications::addMembership($client, $mw_section->section, $params, false, 'by_mw_login');
					}
				}
			}
		}

		wp_update_user($update_user);
		if ($user->user_login != $mail) {
			$this->changeLogin($user->ID, $mail);
		}

		// log user in
		$this->login($user);

		return 'success';
	}

	function getUserInfo($token)
	{
		$url = LICENSE_SERVER . 'auth/login/machine';

		$url .= '/?url=http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$url .= '&token=' . $token;

		$response = wp_remote_post($url, [
			'method' => 'GET',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => [],
		]);

		return json_decode(wp_remote_retrieve_body($response));
	}

	function login($user)
	{
		wp_clear_auth_cookie();
		wp_set_current_user($user->ID);
		wp_set_auth_cookie($user->ID);
		$redirect_to = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		//$redirect_to = home_url();
		wp_safe_redirect($redirect_to);
		exit();
	}

	function changeLogin($id, $mail)
	{
		global $wpdb;

		$new_username = esc_sql(sanitize_user($mail));
		$q = $wpdb->prepare("UPDATE $wpdb->users SET user_login = %s WHERE ID = %d", $new_username, $id);
		$wpdb->query($q);
	}

	function getUserByMail($mail)
	{
		return get_user_by('email', $mail);
	}

	function getUserByCid($cid)
	{
		$user = false;
		$users = get_users(['meta_key' => 'mw_user_connect_id', 'meta_value' => $cid]);
		if ($users) {
			$user = $users[0];
		}

		return $user;
	}

	function clearCid($cid)
	{
		$users = get_users(['meta_key' => 'mw_user_connect_id', 'meta_value' => $cid]);
		if ($users) {
			foreach ($users as $user) {
				delete_user_meta($user->ID, 'mw_user_connect_id');
			}
		}
	}

	function createUser($mail)
	{
		$random_password = wp_generate_password(12, false);

		return wp_create_user($mail, $random_password, $mail);
	}

}
