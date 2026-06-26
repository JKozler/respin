<?php
class MwSellDashboard
{

	function __construct()
	{
	}

	public static function printDashboard()
	{
		$filter = '<div class="mw_dashboard_statistics_filter_container">';
		$filter .= '<div class="mw_dashboard_select_container">';
		$filter .= '<span>' . __('Zdroj', 'cms') . '</span>';

		$filter .= MwsOrderSource::getSelect([], '', 'mw_source_select mw_input_w mw_input_rounded');
		$filter .= '</div>';

		$filter .= mwAdminComponents::rangeSelect(['selected' => 'this-month']);
		$filter .= '</div>';

		$content = mwAdminComponents::title([
			'text' => __('Celkové statistiky', 'mwshop'),
			'onright' => $filter,
		], 'h2');

		// statistics
		$content .= '<div class="mw_dashboard_statistics">';
		$period = mwSetting::getPeriod('this-month');
		$content .= self::dashboardStatistics($period['from'], $period['to']);
		$content .= '</div>';

		// list
		$content .= mwAdminComponents::title([
			'text' => __('Nejnovější objednávky', 'mwshop'),
		], 'h2');

		$orderObject = mwSetting()->getObject(MWS_ORDER_SLUG);
		$listArgs = $orderObject->service()->getListArgs(1, 10);

		$content .= '<div class="mw_dashboard_list_container">';
		$content .= mwAdminComponents::table($listArgs, 'mw_table_list');
		$content .= '</div>';

		echo $content;
	}

	// @TODO save counted statistics by day
	public static function dashboardStatistics($from = null, $to = null, ?string $source = null)
	{
		$statistics = new MwSellStatistics($from, $to, $source);

		$unit = MWS()->getDefaultCurrency();

		$content = mwAdminComponents::statisticsMainBox([
			'value' => $statistics->getTotalPrice()->formatPrice() . ' ' . $unit,
			'text' => __('Tržby celkem', 'mwshop'),
			'icon' => 'dollar-sign',
		]);
		$content .= mwAdminComponents::statisticsBox([
			'value' => $statistics->getOrdersCount(),
			'text' => __('Objednávek', 'mwshop'),
			'icon' => 'file-text',
		]);
		$content .= mwAdminComponents::statisticsBox([
			'value' => MwsPrice::doFormatPrice($statistics->getAveragePrice()) . ' ' . $unit,
			'text' => __('Průměrná objednávka', 'mwshop'),
			'icon' => 'slash',
		]);
		$content .= mwAdminComponents::statisticsBox([
			'value' => $statistics->getPaidPrice()->formatPrice() . ' ' . $unit,
			'text' => __('Zaplaceno', 'mwshop'),
			'icon' => 'check-circle',
		]);
		$content .= mwAdminComponents::statisticsBox([
			'value' => $statistics->getNotPaidPrice()->formatPrice() . ' ' . $unit,
			'text' => __('Zbývá zaplatit', 'mwshop'),
			'icon' => 'clock',
		]);
		$content .= mwAdminComponents::statisticsBox([
			'value' => $statistics->getCanceledPrice()->formatPrice() . ' ' . $unit,
			'text' => __('Stornováno', 'mwshop'),
			'icon' => 'x',
		]);

		return $content;
	}

	public static function dashboardStatistics_ajax()
	{
		$period = mwSetting::getPeriod($_POST['period'], $_POST['from'] ?? '', $_POST['to'] ?? '');
		$source = $_POST['source'];
		echo self::dashboardStatistics($period['from'], $period['to'], $source !== '' ? $source : null);
		die();
	}
}
