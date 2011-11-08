<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2010, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\models;

use lithium\core;

use lithium\core\Libraries;
use BadMethodCallException;

class Leaves extends \lithium\data\Model{

	public $hasMany = array(
		'Branches'
	);

	public $hasOne = array(
		'Branch'
	);

	protected $_meta = array(
		'source' => 'cms_leaves'
	);

	/**
	 * Load the leave's handler class and prepare the route
	 *
	 * @param Record $record
	 */
	public static function loadClass($record) {
		if (!isset($record->class)) {
			$record->class = __CLASS__;
		}
		$class = $record->class;
		if (!class_exists($class)) {
			$class = Libraries::locate('models', $class);
		}
		return $class;
	}

	/**
	 * Create the route array for the requested record.
	 *
	 * @param Record $record
	 * @param Request $request
	 */
	public static function loadRoute($record, $request) {
		$config = array(
			'template' => '/' . $request->url,
			'params' => array(
				'controller' => 'leaves',
				'action' => 'view',
				'leaf' => $record
			),
			'options' => array()
		);
		return $config;
	}

	/**
	 * Load the leave's full record
	 *
	 * @param Record $record
	 */
	public static function load($record) {
		$class = static::loadClass($record);
		if ($class != get_called_class()) {
			return $class::load($record);
		}

		if ($record->model() != $class) {
			return $class::first($record->id);
		}
		return $record;
	}

	/**
	 * Get or set the current branch context for a leaf
	 *
	 * @param Record $record
	 * @param Record $branch
	 */
	public static function branch($record, $branch = null){
		$relationship = static::relations('Branch')->data();
		extract($relationship);
		if (is_object($branch) && $branch->model() == $to) {
			$record->{$fieldName} = $branch;
		} else {
			if (!isset($record->{$fieldName})) {
				$conditions = array_flip($key);
				foreach ($conditions as &$field) {
					$field = $record->{$field};
				}
				$record->{$fieldName} = $to::first(compact('conditions'));
			}
		}
		return $record->{$fieldName};
	}

	/**
	 *
	 * @param Record $record
	 */
	public static function leaf($record) {
		$class = __CLASS__;
		if ($record->model() != $class) {
			if (!isset($record->leaf)) {
				$record->leaf = $class::first($record->id);
			}
			return $record->leaf;
		}
		return $record;
	}

	public static function url($record, $options = array()) {
		return $record->branch()->url($options);
	}

	public static function route($record) {
		return $record->branch()->route();
	}

	public static function routes($record) {
		return $record->branch()->routes();
	}

	public static function root($record) {
		return $record->branch()->root();
	}

	public static function parent($record, $filter = null) {
		return $record->branch()->parent($filter);
	}

	public static function parents($record, $filter = null) {
		return $record->branch()->parents($filter);
	}

	public static function children($record, $filter = null) {
		return $record->branch()->children($filter);
	}

	public static function siblings($record, $filter = null) {
		return $record->branch()->siblings($filter);
	}
}