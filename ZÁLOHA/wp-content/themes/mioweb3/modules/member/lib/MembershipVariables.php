<?php declare(strict_types=1);

namespace Mioweb\Member;

use mwMember;

class MembershipVariables extends \MwVariables
{

	public static function getMembershipVariables(mwMember $member, ?string $password, array $saved, ?int $memberSectionId = null): array
	{
		$login = '';
		$loginTableItems = [];
		$memberSection = null;
		$membershipInfoItems = [];
		$expirationDate = __('(členství je neomezeno)', 'cms_member');
		$levels = '';
		$months = '';

		$updateInfo = '';
		$loginUrl = '';

		// basic membership info
		$membershipInfoItems[] = [
			'title' => __('Email', 'cms_member'),
			'value' => $member->getEmail(),
		];
		if (trim($member->getFullName())) {
			$membershipInfoItems[] = [
				'title' => __('Jméno', 'cms_member'),
				'value' => $member->getFullName(),
			];
		}

		// if is added or updated only one membership
		if ($memberSectionId) {
			$memberSection = mwMemberModule()->getMemberSection($memberSectionId);
			$membership = $member->getMembership($memberSectionId, true);

			$expiration = $membership->getEnd('date');
			if ($expiration) {
				$expirationDate = $expiration;
			}

			if ($membership) {
				// start of membership
				$membershipInfoItems[] = [
					'title' => __('Členství od', 'cms_member'),
					'value' => $membership->getStart('datetime'),
				];
				// end of membership

				if ($expiration) {
					$membershipInfoItems[] = [
						'title' => __('Členství do', 'cms_member'),
						'value' => $expiration,
					];
				}
			}

			// levels on add
			if (isset($saved['added'][$memberSectionId]['levels'])) {
				$addedLevels = $saved['added'][$memberSectionId]['levels'];
				$i = 0;
				foreach ($addedLevels as $membershipLevel) {
					$level = MemberLevel::getOneById($membershipLevel->getLevelId());
					if ($i > 0) {
						$levels .= ', ';
					}
					$levels .= $level ? $level->getName() : '';

					$i++;
				}
				if ($levels) {
					$membershipInfoItems[] = [
						'title' => count($addedLevels) > 1 ? __('Úrovně', 'cms_member') : __('Úroveň', 'cms_member'),
						'value' => $levels,
					];
				}
			}

			// on update
			if (isset($saved['updated'][$memberSectionId])) {
				$updateInfo .= self::getUpdatedInfo($memberSectionId, $saved['updated'][$memberSectionId], $member);
			}
		} elseif (count($saved['updated'])) {
			$i = 0;
			foreach ($saved['updated'] as $memberSectionId => $updated) {
				if ($i > 0) {
					$updateInfo .= '<br>';
				}
				$updateInfo .= self::getUpdatedInfo($memberSectionId, $updated, $member, true);
				$i++;
			}
		}

		// login message
		$password = $password ?: __('Vaše heslo, které na tomto webu již používáte. (Pokud si heslo nepamatujete, vygenerujte si na přihlašovací stránce nové)', 'cms_member');

		if ($memberSection) {
			$loginUrl = $memberSection->getLoginUrl();
			$loginUrl = $loginUrl ? '<a href="' . $loginUrl . '">' . $loginUrl . '</a>' : '';
			$loginTableItems[] = [
				'title' => __('Přihlašovací stránka', 'cms_member'),
				'value' => $loginUrl ?: __('Přihlašovací stránka není nastavena. Kontaktujte správce webu.', 'cms_member'),
			];
		} elseif ($memberSectionId === null) {
			// login page for multiple member sections
			foreach ($saved['added'] + $saved['updated'] as $memberSectionId => $addedData) {
				$ms = mwMemberModule()->getMemberSection($memberSectionId);

				if ($ms) {
					$loginUrl = $ms->getLoginUrl();
					$loginUrl = $loginUrl ? '<a href="' . $loginUrl . '">' . $loginUrl . '</a>' : '';
					$login .= sprintf(__('Do členské sekce %s se můžete přihlásit na adrese:', 'cms_member'), $ms->getName());
					$login .= '<br>';
					$login .= $loginUrl ?: __('Přihlašovací stránka není nastavena. Kontaktujte správce webu.', 'cms_member');
					$login .= '<br><br>';
				}
			}
		}

		$loginTableItems[] = [
			'title' => __('Přihlašovací jméno', 'cms_member'),
			'value' => $member->getLogin(),
		];
		$loginTableItems[] = [
			'title' => __('Heslo', 'cms_member'),
			'value' => $password,
		];
		$login .= self::formatTable($loginTableItems);

		// variables
		$variables = [
			'LOGIN' => $login,
			'USER_LOGIN' => $member->getLogin(),
			'LOGIN_URL' => $loginUrl,
			'MEMBER_SECTION_NAME' => $memberSection ? $memberSection->getName() : '',
			'MEMBERSHIP_INFO' => self::formatTable($membershipInfoItems),
			'DASHBOARD_URL' => $memberSection ? $memberSection->getUrl() : '',
			'USER_NAME' => $member->getName(),
			'EXPIRATION_DATE' => $expirationDate,
			'LEVEL_NAME' => $levels,
			'LEVELS' => $levels,
			'UPDATE_INFO' => $updateInfo,
			'MONTHS' => $months,
		];

		return static::addAliases($variables);
	}

	public static function getUpdatedInfo(int $memberSectionId, array $updatedData, mwMember $member, $showSection = false): string
	{
		$updateInfo = '';

		if ($showSection) {
			$memberSection = mwMemberModule()->getMemberSection($memberSectionId);
			if ($memberSection) {
				$updateInfo .= sprintf(__('Vaše členství v členské sekci <strong>%s</strong> bylo změněno:', 'cms_member'), $memberSection->getName()) . '<br>';
			}
		}

		$membership = $member->getMembership($memberSectionId, true);

		$updateInfo .= '<ul>';
		// membership start
		if (isset($updatedData['start'])) {
			$updateInfo .= '<li>' . __('Začátek členství byl změněn na', 'cms_member') . ' ' . $membership->getStart('datetime') . '.</li>';
		}

		// levels on update
		if (isset($updatedData['levels'])) {
			foreach ($updatedData['levels'] as $membershipLevel) {
				$level = MemberLevel::getOneById($membershipLevel->getLevelId());
				$updateInfo .= '<li>' . __('Byl přidán přístup do úrovně', 'cms_member') . ' ' . $level->getName() . '.</li>';
			}
		}
		// months on update
		if (isset($updatedData['months'])) {
			foreach ($updatedData['months'] as $month) {
				$updateInfo .= '<li>' . __('Byl přidán přístup do měsíce', 'cms_member') . ' ' . $month->getName() . '.</li>';
			}
		}
		// expiration on update
		if (array_key_exists('expiration', $updatedData)) {
			$expiration = $membership->getEnd('date');
			if ($expiration) {
				$updateInfo .= '<li>' . __('Byla změněna platnost vašeho členství. Členství nyní vyprší', 'cms_member') . ' ' . $expiration . '.</li>';
			} else {
				$updateInfo .= '<li>' . __('Platnost vašeho členství byla nastavena na neomezené', 'cms_member') . '.</li>';
			}
		}
		$updateInfo .= '</ul>';

		if ($showSection && $memberSection && $memberSection->getLoginUrl()) {
			$updateInfo .= sprintf(__('Přihlásit do této členské sekce se můžete zde: %s', 'cms_member'), $memberSection->getLoginUrl()) . '<br>';
		}

		return $updateInfo;
	}

	public static function formatTable(array $items): string
	{
		if (!count($items)) {
			return '';
		}

		$tdStyle = 'padding: 3px;';
		$thStyle = 'padding: 3px 10px 3px 0;';

		$rows = '';
		foreach ($items as $item) {
			$rows .= '<tr>'
			. '<td style="' . $thStyle . '">' . $item['title'] . ':</td>'
			. '<td style="' . $tdStyle . '">' . $item['value'] . '</td>'
			. '</tr>';
		}

		return '<table style="border-spacing:0; padding-top: 8px;">'
			. '<tbody>' . $rows . '</tbody>'
			. '</table>';
	}

	public static function addAliases(array $variables): array
	{
		$variables['PRIHLASOVACI_UDAJE'] = $variables['LOGIN'] ?? null;
		$variables['PRIHLASOVACI_STRANKA'] = $variables['LOGIN_URL'] ?? null;
		$variables['PRIHLASOVACI_JMENO'] = $variables['USER_LOGIN'] ?? null;
		$variables['NAZEV_CLENSKE_SEKCE'] = $variables['MEMBER_SECTION_NAME'] ?? null;
		$variables['NASTENKA'] = $variables['DASHBOARD_URL'] ?? null;
		$variables['INFORMACE_O_CLENSTVI'] = $variables['MEMBERSHIP_INFO'] ?? null;
		$variables['JMENO_CLENA'] = $variables['USER_NAME'] ?? null;
		$variables['DATUM_EXPIRACE'] = $variables['EXPIRATION_DATE'] ?? null;
		$variables['NAZEV_UROVNE'] = $variables['LEVEL_NAME'] ?? null;
		$variables['ZMENY_CLENSTVI'] = $variables['UPDATE_INFO'] ?? null;
		$variables['MESICNI_CLENSTVI'] = $variables['MONTHS'] ?? null;

		return $variables;
	}

	public static function getVariableList(): array
	{
		$list = [
			[
				'code' => 'PRIHLASOVACI_UDAJE',
				'desc' => __('Vypíše odkaz na přihlašovací stránku, přihlašovací jméno a heslo člena. Pokud má člen vytvořen účet v minulosti, tak se heslo nahradí informací, že má použít pro přihlášení stejné heslo, které na webu již používá.'),
				'show' => ['email', 'import'],
			],
			[
				'code' => 'PRIHLASOVACI_STRANKA',
				'desc' => __('Odkaz na přihlašovací stránku členské sekce.'),
				'show' => ['email'],
			],
			[
				'code' => 'NASTENKA',
				'desc' => __('Odkaz na nástěnku členské sekce.'),
				'show' => ['email'],
			],
			[
				'code' => 'PRIHLASOVACI_JMENO',
				'desc' => __('Přihlašovací jméno člena.'),
				'show' => ['email'],
			],
			[
				'code' => 'NAZEV_CLENSKE_SEKCE',
				'desc' => __('Název členské sekce.'),
				'show' => ['email'],
			],
			[
				'code' => 'NAZEV_UROVNE',
				'desc' => __('Vypíše název členské úrovně, případně všech členských úrovní do kterých byl člen přidán.'),
				'show' => ['email'],
			],
			[
				'code' => 'INFORMACE_O_CLENSTVI',
				'desc' => __('Vypíše tabulku s informacemi o členství. Tabulka obsahuje email a jméno člena, úroveň/úrovně členství a datum expirace (pokud je členství omezeno).'),
				'show' => ['email'],
			],
			[
				'code' => 'DATUM_EXPIRACE',
				'desc' => __('Datum expirace členství. Pokud členství není omezeno, tak vypíše "(členství je neomezeno)".'),
				'show' => ['email'],
			],

		];

		return $list;
	}

}
