<?php
namespace Mioweb\Lib;

use mwAdminComponents;
use WP_Theme;

class License
{

	const DEFAULT_EXPIRATION = 99999;

	private ?string $_licenseNumber;

	private $_currentStatus; // null, false, string, array (wp get_transient)

	private ?array $_data = null;

	private array $_modules;

	private ?string $_licenseType = null;

	private ?string $_hostingType = null;

	private int $_expire = self::DEFAULT_EXPIRATION; // days to expire

	function __construct(bool $editMode = false, array $installedVersion = [])
	{
		$license = get_option('web_option_license');
		$this->_licenseNumber = $license['license'] ?? null;
		$this->_currentStatus = $this->getStatusTransient();
		$this->_modules = self::loadModules();

		if ($editMode && $this->_licenseNumber) {
			if (!$this->_modules) {
				$this->check();
			}
			if (!isset($this->_currentStatus['license']) || $this->getNumber() !== $this->_currentStatus['license'] || $this->_currentStatus['code'] !== 'success') {
				$this->_currentStatus = [];
			}

			if (!$this->_currentStatus || isset($_GET['checkLicense'])) {
				$this->check();
			}

			// send statistics
			add_action('init', 'mwSendStatisticsInit');

			// license info
			$data = get_option('mw_licence_info', null);
			if (!is_array($data)) {
				$data = null;
			}

			$this->_data = $data;
			$licenseType = $this->_data['type'] ?? null;
			$hostingType = $this->_data['hosting']->hosting_type ?? null;
			$this->_licenseType = self::loadLicenseType($licenseType, $hostingType);
			$this->_hostingType = $this->loadHostingType();
			$this->_expire = isset($this->_data['expire_at']) && $this->_data['expire_at'] ? ceil((strtotime($this->_data['expire_at']) - current_time('timestamp')) / 86400) : self::DEFAULT_EXPIRATION;
			if ($this->isExpired()) {
				add_action('init', [$this, 'redirectFromAdmin']);
				add_action('body_class', [$this, 'addBodyClass']);
			}
		}

		if (!$this->_modules || count($this->_modules) < 1) {
			$this->_modules = ['blog'];

			if (isset($installedVersion['funnels'])) {
				$this->_modules[] = 'funnels';
			}
			if (isset($installedVersion['mioweb'])) {
				$this->_modules[] = 'mioweb';
			}
			if (isset($installedVersion['member'])) {
				$this->_modules[] = 'member';
			}
			if (isset($installedVersion['shop'])) {
				$this->_modules[] = 'shop';
			}
		}

		if (mw_is_lite_editor()) {
			$this->_modules = ['blog', 'lite'];
		}

		if ($this->isHosting()) {
			define('DISABLE_WP_CRON', true); // Cron is run in CLI mode instead
		}
	}

	public function getNumber(): ?string
	{
		return $this->_licenseNumber;
	}

	private function getStatusTransient()
	{
		return get_transient('cms_license');
	}

	public function isHosting(): bool
	{
		return $this->_licenseType !== 'lifetime' && $this->_licenseType !== 'recurring';
	}

	public function isRecurrent(): bool
	{
		return $this->_licenseType === 'recurring';
	}

	public function isExpired(): bool
	{
		return ($this->isHosting() || $this->isRecurrent()) && $this->_expire < -7;
	}

	public function getSourceGroup()
	{
		return isset($this->_data['source']) ? $this->_data['source']->group : '';
	}

	public static function loadLicenseType(?string $licenseType, ?string $hostingType): string
	{
		if ($licenseType !== null) {
			$type = $licenseType;
			if (
				($type === 'lifetime' && $hostingType !== null)
				|| ($type === 'hosting' && $hostingType === 'hosting-only')
			) {
				$type = 'hosting-only';
			}
		} else {
			$type = 'lifetime';
		}

		return $type;
	}

	function loadHostingType()
	{
		if ($this->_licenseType !== 'lifetime' && isset($this->_data['hosting'])) {
			return $this->_data['hosting']->hosting_type;
		}

		return null;
	}

	public function check()
	{
		$url = LICENSE_SERVER . 'license/activate';

		$url .= '/?url=' . get_home_url();
		$url .= '&serial_number=' . $this->getNumber();
		$url .= '&php_version=' . urlencode(mw_get_php_version_main_part());

		$response = wp_remote_post($url, [
			'method' => 'GET',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => [],
		]);

		$newstatus = [];
		$newstatus['license'] = $this->getNumber();

		$return = json_decode(wp_remote_retrieve_body($response));

		if (is_wp_error($response) || !isset($return->status) || (isset($return->error) && $return->error->message !== 'Parameter serial_number can not be empty.')) {
			$modules = get_option('cms_license_modules');
			$try = get_option('cms_license_try');
			if (!empty($modules) && (!$try || $try > time())) {
				$newstatus['code'] = 'success';
				if (!$try) {
					update_option('cms_license_try', time() + (7 * 24 * 60 * 60));
				}
			} else {
				$newstatus['code'] = 'error';
			}
		} else {
			$newstatus['code'] = $return->status === 'activation-already-match' ? 'success' : $return->status;
			$modules = isset($return->modules) ? (array) $return->modules : null;

			$mw_licence_info = (array) $return;

			// Check latest available MW version
			$mw_licence_info['allowed_version'] = true;
			$latestAvailableRelease = $mw_licence_info['last_version'] ?? null;

			if ($latestAvailableRelease !== null) {
				$themeData = wp_get_theme();
				$currentVersion = $themeData instanceof WP_Theme ? $themeData->get('Version') : null;
				$mw_licence_info['installed_version'] = $currentVersion;

				if ($currentVersion && version_compare($currentVersion, $latestAvailableRelease->version, '>')) {
					$mw_licence_info['allowed_version'] = false;
				}
			}

			update_option('mw_licence_info', $mw_licence_info);
			$this->_data = $mw_licence_info;
			update_option('cms_license_modules', $modules);
			$this->_modules = $modules;
			delete_option('cms_license_try');
		}
		set_transient('cms_license', $newstatus, 24 * HOUR_IN_SECONDS);
		$this->_currentStatus = $newstatus;
	}

	public function isValid()
	{
		return $this->_licenseNumber && isset($this->_currentStatus['code']) && $this->_currentStatus['code'] === 'success';
	}

	public function hasModule(string $module): bool
	{
		return in_array($module, $this->_modules);
	}

	function addBodyClass($classes)
	{
		$classes[] = 'mw_hosting_expired';

		return $classes;
	}

	function redirectFromAdmin()
	{
		if (is_admin()) {
			wp_redirect(get_home_url());
			die();
		}
	}

	private static function loadModules()
	{
		global $wpdb;

		$option = 'cms_license_modules';
		$allOptions = wp_load_alloptions();

		if (isset($allOptions[$option])) {
			$value = $allOptions[$option];
		} else {
			$value = wp_cache_get($option, 'options');

			if ($value === false) {
				$row = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option));

				// Has to be get_row() instead of get_var() because of funkiness with 0, false, null values.
				if (is_object($row)) {
					$value = $row->option_value;
					wp_cache_add($option, $value, 'options');
				} else { // Option does not exist, so we must cache its non-existence.
					return [];
				}
			}
		}

		return maybe_unserialize($value);
	}

	public static function getStatusCode($licence): string
	{
		$status = get_transient('cms_license');
		$returnCode = $status['code'];

		if ($licence) {
			$type = 'error';
			switch ($returnCode) {
				case 'success':
					$text = __('Licenční číslo je platné.', 'cms');
					$type = 'confirm';

					break;
				case 'not-found':
					$text = __('Neplatné licenční číslo.', 'cms');

					break;
				case '-1':
					$text = __('Platnost této zkušební licence již vypršela. Pokud se vám šablona líbí a chcete v tvorbě webu pokračovat, <strong><a target=\"_blank\" href=\"https://www.mioweb.cz/objednavka/\">kupte si plnou verzi Mioweb šablony zde.</a></strong>.', 'cms');

					break;
				case 'already-activated':
					$text = __('Zadané licenční číslo je již rezervováno pro jinou doménu. Zkuste zadat jiné nebo toto licenční číslo uvolněte.', 'cms');

					break;
				case 'domain-name-already-taken':
					$text = __('Pro tuto doménu je již aktivovaná jiná licence. Prosím uvolněte ji.', 'cms');

					break;
				case 'hosting-domain-not-match':
					$text = __('Neplatný klíč pro tento hosting.', 'cms');

					break;
				case 'hosting-only-not-lifetime-license':
					$text = __('Licenční číslo není platné. K aktivaci webu je potřeba použít licenční číslo jedné z vašich doživotních licencí. Zakoupené doživotní licence naleznete ve <a href="https://mujucet.mioweb.cz/licenses" target="_blank">Vašem zákaznickém účtu Miowebu</a>.', 'cms');

					break;
				case 'hosting-not-found':
					$text = __('Hosting pro toto licenční číslo byl již smazán. Licenční číslo je neplatné.', 'cms');

					break;
				case 'hosting-have-extra-license':
					$text = __('Pro tento hosting je už nastavena jiná doživotní licence.', 'cms');

					break;
				case 'error':
					$text = __('Nedaří se spojit s licenčním serverem. Pravděpodobně došlo k jeho dočasnému výpadku. Zkuste se, prosím, přihlásit později. Webové stránky fungují pro vaše návštěvníky samozřejmě dále.', 'cms');

					break;
				default:
					$text = __('Nastala neznámá chyba:', 'cms') . ' ' . $returnCode;

					break;
			}
			if ($text) {
				return mwAdminComponents::messageBox($text, ['type' => $type]);
			}
		}

		return '';
	}

	function sendNotify($type = 'homepage-edit')
	{
		if ($this->isHosting()) {
			$url = LICENSE_SERVER . 'app-notification';

			wp_remote_post($url, [
				'method' => 'POST',
				'timeout' => 0.01,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => false,
				'headers' => [],
				'body' => [
					'url' => get_home_url(),
					'serial_number' => MW()->getLicense()->getNumber(),
					'type' => $type,
				],
			]);
		}
	}

	public function writeDays($days)
	{
		$text = $days;
		if ($days > 4) {
			$text .= ' ' . __('dní', 'cms_ve');
		} elseif ($days > 1) {
			$text .= ' ' . __('dny', 'cms_ve');
		} else {
			$text .= ' ' . __('den', 'cms_ve');
		}

		return $text;
	}

	public function showAlerts()
	{
		$days = $this->_expire;
		$text = '';
		$class = 'blue';
		if (($this->_licenseType === 'hosting' || $this->_licenseType === 'recurring') && $days < 15) {
			if ($this->_hostingType === 'trial') {
				if ($days > 0) {
					$text = __('Zkušební verze končí za', 'cms_ve');
					$text .= ' ' . $this->writeDays($days);
				}
			} else {
				if ($days < 15 && $days > 0) {
					$text = $this->_licenseType === 'recurring' ? __('Platnost licence končí za', 'cms_ve') : __('Předplacené období končí za', 'cms_ve');
					$text .= ' ' . $this->writeDays($days);
					$text .= '. ' . __('Prodloužit', 'cms_ve');
					$class = 'orange';
				} elseif ($days == 0) {
					$text = __('Dnes končí předplacené období. Prodloužit', 'cms_ve');
					$class = 'red';
				} elseif ($days < 0 && $days >= -7) {
					$text = $this->_licenseType === 'recurring' ? __('Platnost licence skončila. Blokace za', 'cms_ve') : __('Předplacené období skončilo. Blokace za', 'cms_ve');
					$daysLeft = 7 + $days;
					if ($daysLeft === 0) {
						$text .= __(' < 1 den', 'cms_ve');
					} else {
						$text .= ' ' . $this->writeDays($daysLeft);
					}
					$text .= '. ' . __('Prodloužit', 'cms_ve');
					$class = 'red';
				}
			}

			if ($text) {
				echo '<li class="tutorial_info">';
				echo '<a target="_blank" href="' . MY_ACCOUNT_URL . '" class="mw_ti_' . $class . '">';
				echo $text;
				echo '</a>';
				echo '</li>';
			}
		}
		if ($this->_licenseType === 'hosting-only' && $days < 15) {
			if ($days < 15 && $days > 0) {
				$text = __('Platnost hostingu končí za', 'cms_ve');
				$text .= ' ' . $this->writeDays($days);
				$text .= '. ' . __('Prodloužit', 'cms_ve');
				$class = 'orange';
			} elseif ($days == 0) {
				$text = __('Dnes končí platnost hostingu. Prodloužit', 'cms_ve');
				$class = 'red';
			}

			if ($text) {
				echo '<li class="tutorial_info">';
				echo '<a target="_blank" href="' . MY_ACCOUNT_URL . '" class="mw_ti_' . $class . '">';
				echo $text;
				echo '</a>';
				echo '</li>';
			}
		} elseif ($this->_licenseType === 'lifetime') {
			$theme = basename(dirname(dirname(dirname(__FILE__))));
			$wp_info = get_option('_site_transient_update_themes');
			$mw_info = get_option('mioweb_update_info');

			if (isset($wp_info->response[$theme]) || isset($mw_info['extend'])) {
				echo '<li class="new_version_info">';
				echo '<a class="mioweb_new_version_info" target="_blank" href="#" title="' . __('Informace o nové verzi Miowebu', 'cms_ve') . '">' . __('Nová verze', 'cms_ve') . '</a>';
				$this->newVersionPopup($mw_info);
				echo '</li>';
			}
		}
	}

	function newVersionPopup($info)
	{
		?>
		<div class="cms_nodisp mioweb_new_version_info_popup">
			<div class="mioweb_new_version_info_popup_content">
				<div class="mioweb_update_info">
					<h3><?php echo __('Máme pro vás', 'cms_ve'); ?></h3>
					<h2><?php echo __('Novou verzi Miowebu', 'cms_ve'); ?></h2>
					<?php if (isset($info['extend'])) { ?>
						<p><?php echo __('Aktualizaci nelze provést. Vypršelo obodobí, po které byly aktualizace a podpora k dispozici.<br />Aby bylo možné aktualizace stáhnout, je potřeba toto období prodloužit.', 'cms_ve'); ?></p>
						<a class="cms_button" target="_blank"
						   href="<?php echo MY_ACCOUNT_URL . '/licenses/' ?>"><?php echo __('Prodloužit podporu a aktualizace o rok', 'cms_ve'); ?></a>
					<?php } else { ?>
						<p><?php echo __('Po kliknutí na tlačítko níže se dostanete na stránku s aktualizacemi. Ve spodní části stránky v seznamu šablon zaškrtněte Mioweb 3.0 a klikněte na tlačítko „Aktualizovat šablony“.', 'cms_ve'); ?></p>
						<a class="cms_button" target="_blank"
						   href="<?php echo admin_url('update-core.php#update-themes-table'); ?>"><?php echo __('Přejít k aktualizaci', 'cms_ve'); ?></a>
					<?php } ?>
				</div>
				<div class="mioweb_update_news">

					<div class="mioweb_update_version">
						<h2>Mioweb <?php echo $info['current_version']['version']; ?></h2>
						<small><?php echo __('Vydáno', 'cms_ve'); ?><?php echo date('d. m. Y', strtotime($info['current_version']['released_at'])); ?></small>
						<div class="mioweb_update_version_text"><?php echo $info['current_version']['note']; ?></div>
					</div>

					<?php
					foreach ($info['previous_versions'] as $ver) {
						?>
						<div class="mioweb_update_version">
							<h2>Mioweb <?php echo $ver->version; ?></h2>
							<small><?php echo __('Vydáno', 'cms_ve'); ?><?php echo date('d. m. Y', strtotime($ver->released_at)); ?></small>
							<div class="mioweb_update_version_text"><?php echo $ver->note; ?></div>
						</div>
						<?php
					}
					?>
				</div>

			</div>
		</div>
		<?php
	}

	function generateExpiredInfo()
	{
		$url = 'https://mujucet.mioweb.cz/hostings/list';
		echo '<div class="mw_hosting_info_container">';
		echo '<div class="mw_hosting_info_logo"><img src="' . get_bloginfo('template_url') . '/library/visualeditor/images/default/logo1.png" alt="Mioweb" /></div>';
		echo '<a class="mw_hosting_info_back_link" href="' . $url . '">' . __('Můj účet', 'cms') . mw_content_icon_set('arrow-right') . '</a>';
		echo '<div class="mw_hosting_info_container_inner">';

		echo '<h2>' . __('Platnost webu vypršela a jeho<br>editace byla zablokována', 'cms') . '</h2>';

		if ($this->_licenseType === 'hosting-only') {
			echo '<p>' . __('Pro opětovnou funkčnost prodlužte předplatné svého webhostingu.', 'cms') . '</p>';
			echo '<p>' . sprintf(__('Ve svém <a href="%s">zákaznickém účtu</a> najdete fakturu, kterou stačí jednoduše uhradit a jedeme dále. Jestliže fakturu nemáte, napište nám na <a href="mailto:fakturace@mioweb.cz">fakturace@mioweb.cz</a> a vše vyřešíme.', 'cms'), $url) . '</p>';
			echo '<p>' . __('Pokud tento web nepotřebujete a webhosting neprodloužíte, bude jeho provoz během několika dnů trvale ukončen.', 'cms') . '</p>';
			echo '<a class="cms_button cms_button_rounded_corners cms_button_big" href="' . $url . '">' . __('PRODLOUŽIT WEBHOSTING', 'cms') . '</a>';
		} elseif ($this->_licenseType === 'recurring') {
			echo '<p>' . __('Pro opětovnou funkčnost prodlužte platnost licence.', 'cms') . '</p>';
			echo '<p>' . sprintf(__('Ve svém <a href="%s">zákaznickém účtu</a> najdete fakturu, kterou stačí jednoduše uhradit a jedeme dále. Jestliže fakturu nemáte, napište nám na <a href="mailto:fakturace@mioweb.cz">fakturace@mioweb.cz</a> a vše vyřešíme.', 'cms'), $url) . '</p>';
			echo '<a class="cms_button cms_button_rounded_corners cms_button_big" href="' . $url . '">' . __('PRODLOUŽIT PŘEDPLATNÉ', 'cms') . '</a>';
		} else {
			echo '<p>' . __('Pro opětovnou funkčnost prodlužte předplatné Mioweb tarifu.', 'cms') . '</p>';
			echo '<p>' . sprintf(__('Ve svém <a href="%s">zákaznickém účtu</a> najdete fakturu, kterou stačí jednoduše uhradit a jedeme dále. Jestliže fakturu nemáte, napište nám na <a href="mailto:fakturace@mioweb.cz">fakturace@mioweb.cz</a> a vše vyřešíme.', 'cms'), $url) . '</p>';
			echo '<p>' . __('Pokud tento web nepotřebujete a tarif neprodloužíte, bude jeho provoz během několika dnů trvale ukončen.', 'cms') . '</p>';
			echo '<a class="cms_button cms_button_rounded_corners cms_button_big" href="' . $url . '">' . __('PRODLOUŽIT PŘEDPLATNÉ', 'cms') . '</a>';
		}

		echo '</div>';
		echo '</div>';
	}

}
