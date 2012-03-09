<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2010, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\models;

use sli_filters\util\Behaviors;

class Routes extends \lithium\data\Model{

	public $belongsTo = array(
		'Branches'
	);

	protected $_meta = array(
		'source' => 'cms_routes'
	);

	public static function __init(){
		parent::__init();
		static::_applyFilters();
	}

	protected static function _applyFilters() {
		Behaviors::apply(__CLASS__, array(
			'Serialized' => array(
				'fields' => array(
					'params' => 'json'
				)
			)
		));
	}

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
			'base' => null,
			'secure' => false
		);
		extract($options);
		return $base . '/' . $record->url;
	}
}