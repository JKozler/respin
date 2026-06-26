<?php

// @TODO refactor to use XML generator
class MwsRss
{

	public $heureka_cats;

	public $zbozi_cats;

	public static function addCustomRss()
	{
		add_feed('heureka', ['MwsRss', 'heureka_rss_feed']);
		add_feed('zbozi', ['MwsRss', 'zbozi_rss_feed']);
		add_feed('google', ['MwsRss', 'google_rss_feed']);
	}

	public static function clearText($text)
	{
		return str_replace('&', '&amp;', $text);
	}

	public static function getProducts()
	{
		$args = ['post_type' => MWS_PRODUCT_SLUG, 'posts_per_page' => -1];
		$wp_query = new WP_Query($args);

		return $wp_query->posts;
	}

	public static function checkProductValue($price_comparators, $name)
	{
		$value = '';
		if (isset($price_comparators[$name]) && !empty($price_comparators[$name])) {
			$value = $price_comparators[$name];
		}

		return $value;
	}

	public static function getProductArray(MwsProduct $product, $isVariant = false, $variantId = 0)
	{
		$status = $product->getAvailabilityStatus();
		$canBuy = $product->canBuy($status);
		if (!$canBuy) {
			return [];
		}

		$imgurl = $product->getThumbnail()->getUrl('large');
		$price_comparators = get_post_meta($isVariant ? $product->getProduct()->getId() : $product->getId(), 'price_comparators', true);

		$product_name = self::clearText($product->getName());

		$name_comparators = self::clearText(self::checkProductValue($price_comparators, 'productname'));
		if (!empty($name_comparators)) {
				$product_name = $isVariant ? str_replace($product->getProduct()->getName(), $name_comparators, $product_name) : $name_comparators;
		}

		$name_add = self::clearText(self::checkProductValue($price_comparators, 'productname_addition'));
		$full_name = !empty($name_add) ? $product_name . ' ' . $name_add : $product_name;

		$product_array = [
			'id' => $product->getId(),
			'exclude' => self::checkProductValue($price_comparators, 'product_hide_xml'),
			'productname' => $product_name,
			'product' => $full_name,
			'excerpt' => ($isVariant ? self::clearText($product->getProduct()->getExcerpt()) : self::clearText($product->getExcerpt())),
			'url' => $product->getDetailUrl(),
			'img' => $imgurl, // test if no image
			'price' => $product->getPrice()->getPriceVatIncluded(),
			'vat' => $product->getPrice()->getVatPercentage(),
			'ean' => $product->getCodes()->getCode(MwsProductCode::EAN),
			'brand' => $product->getBrand() ?? '',
			'category' => '',
		];

		if ($isVariant) {
			$product_array['variant_group'] = $product->getProduct()->getId();
			$product_array['url'] .= '?variant=' . $variantId;
		}

		$id_for_taxonomy = $isVariant ? $product->getProduct()->getId() : $product->getId();
		$product_array['category_heureka'] = self::checkProductValue($price_comparators, 'heureka_categoryname');
		$product_array['category_zbozi'] = self::checkProductValue($price_comparators, 'zbozi_categoryname');
		$taxonomy_terms = get_the_terms($id_for_taxonomy, MWS_PRODUCT_CAT_SLUG);
		if (isset($taxonomy_terms[0])) {
			$product_array['category'] = $taxonomy_terms[0]->name;
		}

		// product gallery
		foreach ($product->getGallery() as $gal_image) {
			$target = wp_get_attachment_image_src($gal_image, 'large');
			$product_array['gallery'][] = $target[0];
		}

		// product properties
		$i = 0;
		$product_array['param'] = [];
		if ($isVariant) {
			// @TODO why so complicated?
			/** @var MwsPropertyValue[] $variantValues */
			$variantValues = $product->getVariantValues();
			if (isset($variantValues[0])) {
				$product_array['param'][$i]['name'] = self::clearText($variantValues[0]->getProperty()->getName());
				$product_array['param'][$i]['value'] = self::clearText($variantValues[0]->getName()); // @TODO what unit?
			}
			++$i;
		}

		foreach ($product->getProperties() as $propValue) {
			if (empty($product_array['param']) || array_search($propValue->getName(), array_column($product_array['param'], 'value')) === false) {
				$product_array['param'][$i]['name'] = self::clearText($propValue->getProperty()->getName());
				$product_array['param'][$i]['value'] = self::clearText($propValue->getName()) . ' ' . $propValue->getProperty()->getUnit();
				$i++;
			}
		}

		// availability
		$product_array['available'] = $status ? true : false;

		return $product_array;
	}

	public static function heureka_rss_feed()
	{
		$query = self::getProducts();
		$heureka_cats = MwHeureka::getCategoryList();

		header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
		echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';

		echo '<SHOP>';

		if (count($query)) {
			foreach ($query as $post) {
				$product = MwsProduct::createNew($post);

				MWS()->current()->setProduct($product);

				$isVariantRoot = $product->getStructure() === MwsProductStructureType::Variants;

				if ($isVariantRoot) {
					foreach ($product->getVariants() as $variant_id => $variant) { // @TODO not variant ID!
						$product_array = self::getProductArray($variant, true, $variant_id);
						if (!empty($product_array) && !$product_array['exclude']) {
							self::printHeurekaItem($product_array, $heureka_cats);
						}
					}
				} else {
					$product_array = self::getProductArray($product);
					if (!empty($product_array) && !$product_array['exclude']) {
						self::printHeurekaItem($product_array, $heureka_cats);
					}
				}
			}
		}
		echo '</SHOP>';
	}

	public static function printHeurekaItem($product, $heureka_cats)
	{
		echo '<SHOPITEM>';
		echo '<ITEM_ID>' . $product['id'] . '</ITEM_ID>';
		echo '<PRODUCTNAME>' . $product['productname'] . '</PRODUCTNAME>';
		echo '<PRODUCT>' . $product['product'] . '</PRODUCT>';
		echo '<DESCRIPTION>' . strip_tags($product['excerpt']) . '</DESCRIPTION>';
		echo '<URL>' . $product['url'] . '</URL>';
		echo '<IMGURL>' . $product['img'] . '</IMGURL>';
		echo '<MANUFACTURER>' . $product['brand'] . '</MANUFACTURER>';

		if (isset($product['gallery'])) {
			foreach ($product['gallery'] as $gal_image) {
				echo '<IMGURL_ALTERNATIVE>' . $gal_image . '</IMGURL_ALTERNATIVE>';
			}
		}

		$cat = $product['category'];
		if (isset($product['category_heureka']) && $product['category_heureka']) {
			$cat = $heureka_cats[$product['category_heureka']] ?? $product['category'];
		}
		if ($cat) {
			echo '<CATEGORYTEXT>' . $cat . '</CATEGORYTEXT>';
		}

		echo '<PRICE_VAT>' . $product['price'] . '</PRICE_VAT>';
		echo '<VAT>' . $product['vat'] . '%</VAT>';
		echo '<EAN>' . $product['ean'] . '</EAN>';

		if (isset($product['param']) && count($product['param'])) {
			foreach ($product['param'] as $param) {
				echo '<PARAM>';
				echo '<PARAM_NAME>' . $param['name'] . '</PARAM_NAME>';
				echo '<VAL>' . $param['value'] . '</VAL>';
				echo '</PARAM>';
			}
		}

		if ($product['available']) {
			echo '<DELIVERY_DATE>0</DELIVERY_DATE>';
		} else {
			echo '<DELIVERY_DATE></DELIVERY_DATE>';
		}

		if (isset($product['variant_group'])) {
			echo '<ITEMGROUP_ID>' . $product['variant_group'] . '</ITEMGROUP_ID>';
		}
		echo '</SHOPITEM>';
	}


	public static function zbozi_rss_feed()
	{
		$query = self::getProducts();
		$zbozi_cats = MwZboziCz::getCategoryList();

		header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
		echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';

		echo '<SHOP xmlns="http://www.zbozi.cz/ns/offer/1.0">';

		if (count($query)) {
			foreach ($query as $post) {
				$product = MwsProduct::createNew($post);

				MWS()->current()->setProduct($product);

				$isVariantRoot = $product->getStructure() === MwsProductStructureType::Variants;

				if ($isVariantRoot) {
					foreach ($product->getVariants() as $variant_id => $variant) { // @TODO not variant ID!
						$product_array = self::getProductArray($variant, true, $variant_id);
						if (!empty($product_array) && !$product_array['exclude']) {
							self::printZboziItem($product_array, $zbozi_cats);
						}
					}
				} else {
					$product_array = self::getProductArray($product);
					if (!empty($product_array) && !$product_array['exclude']) {
						self::printZboziItem($product_array, $zbozi_cats);
					}
				}
			}
		}
		echo '</SHOP>';
	}

	public static function printZboziItem($product, $zbozi_cats)
	{
		echo '<SHOPITEM>';
		echo '<ITEM_ID>' . $product['id'] . '</ITEM_ID>';
		echo '<PRODUCTNAME>' . $product['productname'] . '</PRODUCTNAME>';
		echo '<PRODUCT>' . $product['product'] . '</PRODUCT>';
		echo '<DESCRIPTION>' . strip_tags($product['excerpt']) . '</DESCRIPTION>';
		echo '<URL>' . $product['url'] . '</URL>';
		echo '<IMGURL>' . $product['img'] . '</IMGURL>';
		echo '<MANUFACTURER>' . $product['brand'] . '</MANUFACTURER>';

		if (isset($product['gallery'])) {
			foreach ($product['gallery'] as $gal_image) {
				echo '<IMGURL>' . $gal_image . '</IMGURL>';
			}
		}

		$cat = $product['category'];
		if (isset($product['category_zbozi']) && $product['category_zbozi']) {
			$cat = $zbozi_cats[$product['category_zbozi']] ?? $product['category'];
		}
		if ($cat) {
			echo '<CATEGORYTEXT>' . $cat . '</CATEGORYTEXT>';
		}

		echo '<PRICE_VAT>' . $product['price'] . '</PRICE_VAT>';
		echo '<EAN>' . $product['ean'] . '</EAN>';

		if (isset($product['param']) && count($product['param'])) {
			foreach ($product['param'] as $param) {
				echo '<PARAM>';
				echo '<PARAM_NAME>' . $param['name'] . '</PARAM_NAME>';
				echo '<VAL>' . $param['value'] . '</VAL>';
				echo '</PARAM>';
			}
		}

		if ($product['available']) {
			echo '<DELIVERY_DATE>0</DELIVERY_DATE>';
		} else {
			echo '<DELIVERY_DATE>-1</DELIVERY_DATE>';
		}

		if (isset($product['variant_group'])) {
			echo '<ITEMGROUP_ID>' . $product['variant_group'] . '</ITEMGROUP_ID>';
		}
		echo '</SHOPITEM>';
	}

	public static function google_rss_feed()
	{
		$query = self::getProducts();

		header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
		echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';

		echo '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">';
		echo '<channel>';
		echo '<title>' . get_bloginfo() . '</title>';
		echo '<link>' . get_bloginfo('url') . '</link>';
		echo '<description>' . get_bloginfo('description') . '</description>';

		if (count($query)) {
			foreach ($query as $post) {
				$product = MwsProduct::createNew($post);

				MWS()->current()->setProduct($product);

				$isVariantRoot = $product->getStructure() === MwsProductStructureType::Variants;

				if ($isVariantRoot) {
					foreach ($product->getVariants() as $variant_id => $variant) { // @TODO not variant ID!
						$product_array = self::getProductArray($variant, true, $variant_id);
						if (!empty($product_array) && !$product_array['exclude']) {
							self::printGoogleItem($product_array);
						}
					}
				} else {
					$product_array = self::getProductArray($product);
					if (!empty($product_array) && !$product_array['exclude']) {
						self::printGoogleItem($product_array);
					}
				}
			}
		}
		echo '</channel>';
		echo '</rss>';
	}

	public static function printGoogleItem($product)
	{
		echo '<item>';
		echo '<g:id>' . $product['id'] . '</g:id>';
		echo '<g:title>' . $product['productname'] . '</g:title>';
		echo '<g:description>' . strip_tags($product['excerpt']) . '</g:description>';
		echo '<g:link>' . $product['url'] . '</g:link>';
		echo '<g:image_link>' . $product['img'] . '</g:image_link>';

		if (isset($product['gallery'])) {
			foreach ($product['gallery'] as $gal_image) {
				echo '<g:additional_image_link>' . $gal_image . '</g:additional_image_link>';
			}
		}

		if ($product['category']) {
			echo '<g:product_type>' . $product['category'] . '</g:product_type>';
		}
		echo '<g:condition>new</g:condition>';

		$price = $product['price'];
		$dec = (float) $price != floor($price) ? 2 : 0;
		$print_price = number_format(round((float) $price, 2), $dec, '.', '');

		echo '<g:price>' . $print_price . ' ' . strtoupper(MWS()->getDefaultCurrency('key')) . '</g:price>';
		echo '<g:gtin>' . $product['ean'] . '</g:gtin>';
		echo '<g:brand>' . $product['brand'] . '</g:brand>';
		if (!$product['ean'] && !$product['brand']/* && $condition === 'new'*/) {
			echo '<g:identifier_exists>false</g:identifier_exists>';
		}

		if ($product['available']) {
			echo '<g:availability>in stock</g:availability>';
		} else {
			echo '<g:availability>out of stock</g:availability>';
		}

		if (isset($product['variant_group'])) {
			echo '<g:item_group_id>' . $product['variant_group'] . '</g:item_group_id>';
		}
		echo '</item>';
	}

}
