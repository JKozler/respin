<?php

function field_type_campaigns($field, $meta, $tagid, $tagname, $campId)
{
	$pages = mw_get_pages();
	$newid = 0;
	$campaign = [];

	?>
	<div class="mioweb_campaign_setting_container">

		<?php

		$content = get_option(CAMPAIGN_OPTION);

		if (is_array($content['campaigns'])) {
			foreach ($content['campaigns'] as $id => $camp) {
				$newid = $id + 1;

				if ($id == $campId) {
					$campaign = $camp;
				}
			}
		}

		?>


					<div class="campaign_set_box_container">
						<div class="campaign_set_box campaign_set_box_squeeze mw_rounded mw_shadow_b">
							<label class="campaign_set_box_label" for="<?php echo $tagid . '_squeeze'; ?>">
								<?php echo __('Vstupní stránka', 'cms_mioweb'); ?>
							</label>
							<div class="campaign_set_box_content">
								<?php
								echo mwAdminComponents::selectPage([
									'name' => $tagname . '[squeeze]',
									'tag_id' => $tagid . '_squeeze',
									'add_button' => true,
									'whisperer' => true,
								], ($campaign['squeeze'] ?? ''), 'campaing_select_page');
								?>
							</div>
						</div>

						<div class="campaign_set_box_container_arrow"><?php echo mw_icon('icon-arrow-down'); ?></div>
					</div>
					<div class="campaign_set_box_info">
						<h3><?php echo __('Vstupní stránka', 'cms_ve'); ?></h3>
						<p><?php echo __('Vstupní stránka musí obsahovat formulář, který má jako děkovací stránku nastavenou url první stránky s obsahem zdarma. V url nesmí chybět speciální atribut (setuser) s kódem, který zajistí návštěvníkovi přístup. Poté co jej návštěvník vyplní je přesměrován na první stránku kampaně.', 'cms_ve'); ?></p>
					</div>

					<div class="cms_clear"></div>


					<div class="campaign_set_box_container">

						<?php
						if (!isset($campaign['page'])) {
						?>
							<div class="campaign_set_box campaign_set_box_page mw_rounded mw_shadow_b">
								<?php MwCampaignsModule::campaign_page($tagid . '_page_0', $tagname . '[page][0]', ['page' => ''], '1. ' . __('Stránka s obsahem zdarma', 'cms_mioweb'), false); ?>
							</div>
							<?php
							$newid = 1;
						} else {
							$i = 0;
							foreach ($campaign['page'] as $pid => $page) {
								?>
								<div class="campaign_set_box campaign_set_box_page mw_rounded mw_shadow_b">
									<?php MwCampaignsModule::campaign_page($tagid . '_page_' . $i, $tagname . '[page][' . $i . ']', $campaign['page'][$pid], ($i + 1) . '. ' . __('Stránka s obsahem zdarma', 'cms_mioweb'), ($i > 0 ? true : false), $campaign); ?>
								</div>
								<?php
								$i++;
							}
							$newid = $i;
						}
						?>
						<div class="campaign_set_button_container">
							<?php
							echo mwAdminComponents::button([
								'icon' => 'plus',
								'button_text' => __('Přidat stránku', 'cms_mioweb'),
								'style' => 'secondary',
								'link' => '#',
								'attrs' => 'data-id="' . $newid . '" data-name="' . $tagname . '" data-tagid="' . $tagid . '"',
							], 'mioweb_add_campaign_page');
							?>
						</div>
					</div>
					<div class="campaign_set_box_info">
						<h3><?php echo __('Stránky s obsahem zdarma', 'cms_ve'); ?></h3>
						<p><?php echo __('Stránky s obsahem zdarma obsahují videa nebo jiný hodnotný obsah, pomocí kterého předáte svým návštěvníkům základy svého know-how, přesvědčíte je o svých znalostech nebo kvalitách vašeho produktu, sdělíte jim co je můžete dále naučit a získate jejich důvěru. Na konci této série je vlastní prodej nabízeného výukového programu nebo jiného vašeho produktu.', 'cms_ve'); ?></p>
					</div>

					<div class="cms_clear"></div>


	</div>
	<?php
}

function field_type_selectcampaign($field, $meta, $group_id)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');
	$campaigns = get_option('campaign_basic');
	if (isset($campaigns['campaigns'])) {
		?>
		<select name="<?php echo $group_id . '[', $field['id'] . ']'; ?>"
				id="<?php echo $group_id . '_' . $field['id']; ?>">
		<?php
		echo '<option value="" ' . ($content === '' ? 'selected="selected"' : '') . '>' . __(' - Vyberte kampaň - ', 'cms_mioweb') . '</option>';
		foreach ($campaigns['campaigns'] as $id => $campaign) {
			echo '<option value="' . $id . '" ' . ($content !== '' && $content == $id ? 'selected="selected"' : '') . '>' . $campaign['name'] . '</option>';
		}
		?>
		</select>
		<?php
	} else {
		echo mwAdminComponents::messageBox(__('Není vytvořena žádná kampaň.', 'cms_mioweb'), ['type' => 'error']);
	}
}
