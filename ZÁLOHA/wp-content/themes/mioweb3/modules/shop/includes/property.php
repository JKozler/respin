<?php
/**
 * @TODO refactor
 * Helper class to safely access product property attributes.
 *
 * @class MwsProperty
 */
class MwsProperty extends mwPost
{

	private $meta = null;

	private $_values = null;

	/**
	 * Creates new instance of class as a wrapper of WP_Post object.
	 *
	 * @param WP_Post $post
	 */
	public function __construct(WP_Post $post)
	{
		parent::__construct($post);
		$this->loadMeta();
	}


	public function getType(): string
	{
		return isset($this->meta['type'])
			? MwsPropertyType::checkedValue($this->meta['type'], MwsPropertyType::Text)
			: MwsPropertyType::Text;
	}

	/**
	 * Optional unit string appended to the value in print outs.
	 */
	public function getUnit(): string
	{
		return (string) ($this->meta['unit'] ?? '');
	}

	/** @return MwsPropertyValue[] */
	public function getValues(): array
	{
		if ($this->_values === null) {
			$this->_values = [];
			foreach ($this->meta['values'] ?? [] as $value) {
				if (is_array($value) && isset($value['name'])) {
					$this->_values[] = new MwsPropertyValue(
						$this,
						$value['name'],
						isset($value['id']) ? sanitize_key($value['id']) : ''
					);
				}
			}
		}

		return $this->_values;
	}

	/** Load metadata of the class. Uses cached data if present. */
	private function loadMeta(): array
	{
		if ($this->meta === null) {
			$this->meta = MWDB()->getPostMeta($this->getId(), MWS_PROPERTY_META_KEY)[0] ?? [];
		}

		return $this->meta;
	}

	/**
	 * @TODO refactor
	 * Print HTML input element to edit value of the property (not to define a property!).
	 * @param string $name HTML attribute "name"
	 * @param string $id HTML attribute "id"
	 * @param string $value Currently assigned value into the editor. For TEXT it is the text. For ENUMERATION it is
	 *                            {@link MwsPropertyValue::id}.
	 * @param string $css Optional CSS classes for the input element.
	 * @param string $placeholder Optional string when value is empty.
	 * @param string $hint
	 * @param bool $disabled If the editor should be disabled, that is in read-only state.
	 * @param bool $enableEmpty If true then selector allows an empty value to be selected.
	 * @return string HTML formatted input element to edit value of a property.
	 */
	public function htmlEditor($name, $id, $value, $enableEmpty = false)
	{
		$res = '';
		switch ($this->getType()) {
			case MwsPropertyType::Enumeration:
				$options = [];
				if ($enableEmpty) {
					$options[] = [
						'name' => __('(bez hodnoty)', 'mwshop'),
						'value' => '',
					];
				}

				$hasSelection = false;
				/** @var MwsPropertyValue $propValue */
				foreach ($this->getValues() as $propValue) {
					$options[] = [
						'name' => $propValue->getName(),
						'value' => $propValue->getId(),
					];
				}

				$res = mwAdminComponents::select([
					'name' => $name,
					'tag_id' => $id,
					'options' => $options,
				], $value, 'mws_property_editor');

				break;
			default:
				$res = mwAdminComponents::input([
					'name' => $name,
					'id' => $id,
				], $value);

				break;
		}

		return $res;
	}

	/**
	 * Find corresponding value {@link MwsPropertyValue} instance.
	 * For ENUMERATION type the value is checked against defined values.
	 * For TEXT type value is simply created from the passed value.
	 *
	 * @param string $value Value to be found. For ENUMERATION this is value's id.
	 * @param bool $emptyAsNull If set to true, then empty value returns null.
	 * @return MwsPropertyValue|null On success value instance is return, null on failure.
	 */
	public function getValue(string $value, bool $emptyAsNull = false): ?MwsPropertyValue
	{
		foreach ($this->getValues() as $enumValue) {
			if ($enumValue->getId() == $value) { // @TODO sanitize value
				return $enumValue;
			}
		}
		switch ($this->getType()) {
			case MwsPropertyType::Enumeration:
				break;
			default:
				// Create automagically new value.
				$isEmpty = empty($value) && $value !== '0';
				if ($emptyAsNull && $isEmpty) {
					return null;
				}

				$newValue = new MwsPropertyValue($this, $value);
				$this->_values[] = $newValue;

				return $newValue;
		}

		return null;
	}

	/**
	 * Get all instances of {@link MwsProperty} as an array.
	 *
	 * @param array $queryArgs Optional argument for {@link WP_Query}. Default will filter only published instances.
	 * @return MwsProperty[]
	 */
	public static function getAll(array $queryArgs = ['post_status' => 'publish'], $paged = false): array
	{
		$args = array_merge(
			[
				'post_type' => MWS_PROPERTY_SLUG,
			],
			$queryArgs,
			['posts_per_page' => -1]
		);

		return self::getQuery($args, $paged);
	}

}

class MwsPropertyValue
{

	private $_property;

	private $_id;

	private $_name;

	public function __construct(MwsProperty $property, string $name, string $id = '')
	{
		$this->_property = $property;
		$this->_name = $name;
		$this->_id = $id === '' ? sanitize_title($name, '', 'save') : $id;
	}

	public function getProperty(): MwsProperty
	{
		return $this->_property;
	}

	/**
	 * @TODO maybe rename?
	 * Stored value as a text
	 */
	public function getName(): string
	{
		return $this->_name;
	}

	/**
	 * Id of the value (this is stored into DB). Basically sanitized $name.
	 */
	public function getId(): string
	{
		return $this->_id;
	}

	/**
	 * @TODO refactor
	 * Form serialized form of instance as array.
	 * @return array
	 */
	public function serialize()
	{
		$res = [
			'property' => $this->getProperty()->getId(),
		];
		switch ($this->getProperty()->getType()) {
			case MwsPropertyType::Enumeration:
				$res['valueId'] = $this->_id;

				break;
			default:
				$res['value'] = $this->_name;

				break;
		}

		return $res;
	}

	/**
	 * @TODO refactor
	 * Get serialized form of instance.
	 * @param array $serialized Array with serialized value.
	 * @return MwsPropertyValue|null
	 */
	public static function unserialize($serialized)
	{
		$res = null;
		if (is_array($serialized)) {
			if (isset($serialized['property'])) {
				$property = MwsProperty::getOneById($serialized['property']);
				if ($property) {
					switch ($property->getType()) {
						case MwsPropertyType::Enumeration:
							if (isset($serialized['valueId'])) {
								$res = $property->getValue($serialized['valueId']);
							}

							break;
						default:
							if (isset($serialized['value'])) {
								$res = $property->getValue($serialized['value']);
							}
					}
				}
			}
		}

		return $res;
	}

	/**
	 * @TODO refactor
	 * Serialize list of property values.
	 * @param array $values Array of {@link MwsPropertyValue} instances.
	 * @return array Simple array that can be directly used for PHP serialization.
	 */
	public static function serializeArray($values)
	{
		$res = [];
		if (is_array($values)) {
			/** @var MwsPropertyValue $value */
			foreach ($values as $value) {
				$res[] = $value->serialize();
			}
		}

		return $res;
	}

	/**
	 * @TODO refactor
	 * Load previously serialized values.
	 * @param array $serializedValues Array of serialized {@link MwsPropertyValue} instances.
	 * @return array Array of {@link MwsPropertyValue} instances.
	 */
	public static function unserializeArray($serializedValues)
	{
		$res = [];
		foreach ($serializedValues as $serialized) {
			$value = self::unserialize($serialized);
			if ($value) {
				$res[] = $value;
			}
		}

		return $res;
	}

}
