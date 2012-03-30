<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2010, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\models;

use sli_scaffold\extensions\data\Model as Scaffold;
use sli_base\data\model\behavior\Inherited;
use lithium\core\Libraries;
use BadMethodCallException;

class Leaves extends \lithium\data\Model{

//	public $hasMany = array(
//		'Branches'
//	);
//
//	public $hasOne = array(
//		'Branch'
//	);

	protected $_meta = array(
		'source' => 'cms_leaves'
	);

	/**
	 * Model inheritance settings
	 *
	 * @var array
	 */
	public $inheritance = array();

	/**
	 * Model Inherited behavior settings
	 *
	 * @see Leaves::_applyFilters()
	 * @var array
	 */
	protected $_inheritance = array();

	/**
	 * Initialize model
	 */
	public static function __init(){
		$class = get_called_class();
		if ($class == __CLASS__) {
			$self = $class::_object();
			$self->hasMany[] = 'Branches';
			$self->hasOne[] = 'Branch';
		}
		parent::__init();
		static::_applyFilters();
	}

	/**
	 * Apply filters to model
	 */
	protected static function _applyFilters() {
		$class = get_called_class();
		$self = $class::_object();

		$inheritance = array(
			'base' => __CLASS__
		) + $self->inheritance;
		$self->_inheritance = Inherited::apply($class, $inheritance);

		$class::applyFilter('create', function($self, $params, $chain) {
			if ($entity = $chain->next($self, $params, $chain)) {
				$classPath = explode('\\', $self);
				$entity->class = end($classPath);
			}
			return $entity;
		});
	}

	public static function getScaffoldFields() {
		$self = static::_object();
		$class = get_class($self);
		$schema = Inherited::schema($class, $self->_inheritance);
		return array_keys($schema);
	}

	public static function getScaffoldFormFields() {
		$self = static::_object();
		$class = get_class($self);
		$schema = Inherited::schema($class, $self->_inheritance);
		return Scaffold::mapSchemaFields($class, null, $schema);
	}

	/**
	 * Load the leave's handler class and prepare the route
	 *
	 * @param Record $record
	 */
	public static function loadClass($record) {
		$class = $record->class;
		if (strpos($class, '\\') === false || !class_exists($class)) {
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
	public static function loadRoute($record, $route, $request) {
		$params = array(
			'leaf' => $record
		);
		if (is_array($route->params)) {
			$params += $route->params;
		}
		$params += array(
			'action' => 'view'
		);
		if (!isset($params['controller'])) {
			$controller = static::controller();
			$params += compact('controller');
		}
		return compact('params');
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
			$loaded = $class::first($record->id);
			if ($loaded && $record->model() == __CLASS__) {
//				$loaded->leaf = $record;
			}
			return $loaded;
		}
		return $record;
	}

	/**
	 * Obtain the default controller for a leaf
	 */
	public static function controller() {
		$class = get_called_class();
		if ($class !== __CLASS__) {
			do {
				$classPath = explode('\\', $class);
				$name = end($classPath);
				if ($controller = Libraries::locate('controllers', $name)) {
					return $controller;
				}
				$class = get_parent_class($class);
			} while ($class !== __CLASS__);
		}
		return 'leaves';
	}

	/**
	 * Get or set the current branch context for a leaf
	 *
	 * @param Record $record
	 * @param Record $branch
	 */
	public static function branch($record, $branch = null){
		$self = __CLASS__;
		$relationship = $self::relations('Branch')->data();
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