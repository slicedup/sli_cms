<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2010, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\models;

class Routes extends \lithium\data\Model{

	public $belongsTo = array(
		'Branches'
	);

	protected $_meta = array(
		'source' => 'cms_urls'
	);

	/**
	 * Get the branch record for a route
	 *
	 * @param Record $record
	 */
	public static function branch($record){
		$relationship = static::relations('Branches')->data();
		extract($relationship);
		if (!isset($record->{$fieldName})) {
			$conditions = array_flip($key);
			foreach ($conditions as &$field) {
				$field = $record->{$field};
			}
			$record->{$fieldName} = $to::first(compact('conditions'));
		}
		return $record->{$fieldName};
	}

	/**
	 * Get the leaf record for the routes branch record
	 *
	 * @param Record $record
	 */
	public static function leaf($record) {
		if ($branch = static::branch($record)) {
			return $branch->leaf();
		}
	}

	public function url($record, array $options = array()) {
		$options += array(
			'full' => false,
			'base' => '/',
			'secure' => false
		);
		extract($options);
		return $base . $record->url;
	}
}