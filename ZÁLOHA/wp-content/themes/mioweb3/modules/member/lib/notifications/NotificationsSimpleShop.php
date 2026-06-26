<?php

namespace Mioweb\Member;

class NotificationsSimpleShop
{

	public static function authorization(int $sectionId, string $memberApiKey): ?array
	{
		if (isset($_GET['email'], $_GET['first_name'], $_GET['last_name']) && $_GET['email']) {
			$array = [$_GET['email'], $_GET['first_name'] . ' ' . $_GET['last_name'], $_GET['voice_id'], $memberApiKey, 'SimpleShopMioWeb'];
			$simpleshop_hash = sha1(implode('|', $array));

			if ($_GET['simpleshop_hash'] === $simpleshop_hash) {
				return [
					'user_email' => $_GET['email'] ?? '',
					'first_name' => $_GET['first_name'] ?? '',
					'last_name' => $_GET['last_name'] ?? '',
				];
			}

			Notifications::updateDebug($sectionId, __('Notifikace neproběhla. Neplatný hash notifikace.', 'cms_member'));
		} else {
			Notifications::updateDebug($sectionId, __('Notifikace neproběhla. Notifikace neobsahuje všechny potřebné údaje.', 'cms_member'));
		}

		return null;
	}


}
