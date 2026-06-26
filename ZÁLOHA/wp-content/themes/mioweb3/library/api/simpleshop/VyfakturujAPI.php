<?php declare(strict_types=1);

namespace Mioweb\Api\SimpleShop;

class VyfakturujAPI extends \VyfakturujAPI
{

	public function initWPPlugin(string $domain, string $version, string $apiKey)
	{
		return $this->fetchPost(
			'wpplugin/init/',
			['domain' => $domain, 'plugin_version' => $version, 'api_key' => $apiKey]
		);
	}

	public function sendConversionTable(array $table, string $domain, array $newSections)
	{
		$finalTable = new \stdClass();
		foreach ($table as $key => $row) {
			if (isset($row['levels'])) {
				$levels = new \stdClass();
				foreach ($row['levels'] as $lk => $lv) {
					$levels->{(string) $lk} = $lv;
				}
				$row['levels'] = $levels;
			}
			$finalTable->{(string) $key} = $row;
		}
		mwlog(MWLS_MEMBER, sprintf('Sending convert table to SimpleShop: "%s"', json_encode($finalTable)));

		mwlog(MWLS_MEMBER, sprintf('Sending list of member sections to SimpleShop: "%s"', json_encode($newSections)));

		return $this->fetchPost(
			'wpplugin/conversion-table/',
			['conversion_table' => $finalTable, 'sections' => $newSections, 'domain' => $domain]
		);
	}

}
