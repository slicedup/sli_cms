<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\extensions\behavior\data\model;

class Inherited extends \sli_base\data\model\Behavior {

	protected static $_settings = array(
		'base' => null,
		'parent' => array(),
		'transient' => array(),
		'fieldName' => '__inherited',
		'fieldMap' => array(),
		'key' => array()
	);

	protected static function _apply($class, &$settings) {
		parent::_apply($class, $settings);
		if (empty($settings['base'])) {
			$base = $class;
			while (($class = get_parent_class($base)) != 'lithium\data\Model') {
				$base = get_parent_class($base);
			}
			$settings['base'] = $base;
		}
	}

	public static function schema($model, $settings) {
		$schema = $model::schema();
		if (static::_isBase($model, $settings)) {
			return $schema;
		}
		do {
			$model = get_parent_class($model);
			$schema = $model::schema() + $schema;
		} while (!static::_isBase($model, $settings));
		return $schema;
	}

	public static function createFilter($model, $params, $chain, $settings) {
		//create a merged record with all fields by merging created record for all
		if (!static::_isBase($model, $settings)) {
			$parent = get_parent_class($model);
			while (static::_isTransient($parent, $settings)) {
				$parent = get_parent_class($model);
			}
			$inherited = $parent::create();
		}

		$entity = $chain->next($model, $params, $chain);

		if (isset($inherited)) {
			$data = $entity->data() + $inherited->data();
			$entity->set($data);
			$entity->$settings['fieldName'] = $inherited;
		}

		return $entity;
	}

	public static function saveFilter($model, $params, $chain, $settings) {
		extract($params);
		//set data to record
		if (!empty($data)) {
			$entity->set($data);
			$data = array();
		}
		//create parent record first
		if (isset($entity->$settings['fieldName'])) {
			$parent = $entity->$settings['fieldName'];
			$parentModel = $parent->model();
			$parentData = $entity->data();
			unset($parentData[$settings['fieldName']]);
			if (!$parent->exists()) {
				unset($parentData[$parentModel::meta('key')]);
			}
			$parent->set($parentData);
			$parent->save();
			if (!$entity->exists()) {
				$entity->set($parent->key());
			}
		}
		$params = compact(array_keys($params));
		return $chain->next($model, $params, $chain);
	}

	public static function validateFilter($model, $params, $chain, $settings) {
		//validate all
		return $chain->next($model, $params, $chain);
	}

	public static function findFilter($model, $params, $chain, $settings) {
		$parent = null;
		if (!static::_isBase($model, $settings)) {
			$parent = get_parent_class($model);
			while (static::_isTransient($parent, $settings)) {
				$parent = get_parent_class($model);
			}
		}

		$result = $chain->next($model, $params, $chain);

		if ($parent) {
			if ($result) {
				$apply = function(&$entity) use($model, $parent, $settings){
					$conditions = $entity->key();
					if ($inherited = $parent::first(compact('conditions'))) {
						$data = $entity->data() + $inherited->data();
						$entity->set($data);
						$entity->$settings['fieldName'] = $inherited;
					}
				};
				if ($result instanceOf \lithium\data\Entity) {
					$apply($result);
				} elseif ($result instanceOf \lithium\data\Collection) {
					foreach ($result as $entity) {
						$apply($entity);
					}
				}
			}
		}
		return $result;
	}

	public static function deleteFilter($model, $params, $chain, $settings) {
		if ($delete = $chain->next($model, $params, $chain)) {
			extract($params);
			if (isset($entity->$settings['fieldName'])) {
				$inherited = $entity->$settings['fieldName'];
				if (!$inherited->delete()) {
					$entity->save();
					$delete = false;
				}
			}
		}
		return $delete;
	}

	protected static function _isBase($model, $settings) {
		return $model == $settings['base'];
	}

	protected static function _isTransient($model, $settings) {

	}
}
?>