<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2010, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\models;

class Branches extends \lithium\data\Model{

	public $hasMany = array(
		'Routes'
	);

	public $hasOne = array(
		'Route'
	);

	public $belongsTo = array(
		'Branches',
		'Leaves'
	);

	protected $_meta = array(
		'source' => 'cms_branches'
	);

	/**
	 * Get the leaf record for th current branch
	 *
	 * @param Record $record
	 */
	public static function leaf($record) {
		$relationship = static::relations('Leaves')->data();
		extract($relationship);
		if (!isset($record->{$fieldName})) {
			$conditions = array_flip($key);
			foreach ($conditions as &$field) {
				$field = $record->{$field};
			}
			$record->{$fieldName} = $to::first(compact('conditions'));
			if ($record->{$fieldName}) {
				$record->{$fieldName}->branch($record);
			}
		}
		return $record->{$fieldName};
	}

	public static function branch($record) {
		return $record;
	}

	public static function url($record, $options = array()) {
		return $record->route()->url($options);
	}

	public static function route($record) {
		$relationship = static::relations('Route')->data();
		extract($relationship);
		if (!isset($record->{$fieldName})) {
			$conditions = array_flip($key);
			foreach ($conditions as &$field) {
				$field = $record->{$field};
			}
			$record->{$fieldName} = Routes::first(compact('conditions'));
		}
		return $record->{$fieldName};
	}

	public static function routes($record) {
		$relationship = static::relations('Routes')->data();
		extract($relationship);
		if (!isset($record->{$fieldName})) {
			$conditions = array_flip($key);
			foreach ($conditions as &$field) {
				$field = $record->$field;
			}
			$record->{$fieldName} = Routes::all(compact('conditions'));
		}
		return $record->{$fieldName};
	}

	public static function root($record) {}

	public static function parent($record, $filter = null) {}

	public static function parents($record, $filter = null) {}

	public static function children($record, $filter = null) {}

	public static function siblings($record, $filter = null) {}
}