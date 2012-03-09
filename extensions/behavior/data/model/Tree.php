<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\extensions\behavior\data\model;

use lithium\data\model\Query;

class Tree extends \sli_base\data\model\Behavior {

	protected static $_settings = array(
		'parent' => 'parent_id',
		'left' => 'lft',
		'right' => 'rght',
	);

	/**
	 * Save Filter
	 *
	 */
	public static function saveFilter($model, $params, $chain) {
		extract($params);
		$settings = static::$__settings[$model];
		extract($settings);
		$relocate = false;
		$attr = $entity->export();

		if (!empty($options['whitelist'])) {
			//@todo deal with the whitelist
		}
		if ($entity->exists()) {
			if (isset($entity->$parent)) {
				if ($attr['data'][$parent] != $entity->$parent) {
					$relocate = true;
				}
				//@todo additional checking here 161-188
			}
		} else {
			if (isset($entity->$parent)) {
				if (!static::parent($entity)) {
					//@todo bad parent, fail
				}
				$entity->$left = 0;
				$entity->$right = 0;
			} else {
				$edge = static::_right($model, $right);
				$entity->$left = $edge + 1;
				$entity->$right = $edge + 2;
			}
		}

		$params = compact(array_keys($params));

		if (!($save = $chain->next($model, $params, $chain))) {
			return $save;
		}

		if (!$attr['exists']) {
			static::_setParent($entity, $entity->key());
		} elseif ($relocate) {
			static::_setParent($entity);
		}

		return $save;

	}

	/**
	 * Delete filter
	 */
	public static function deleteFilter($model, $params, $chain) {
		extract($params);
		$settings = static::$__settings[$model];
		extract($settings);

		$conditions = $entity->key();
		$fields = array($left, $right);
		if ($updated = $model::first(compact('conditions', 'fields'))) {
			$entity->set($updated->data());
		}

		if ($chain->next($model, $params, $chain) && $entity->$right && $entity->$left) {
			$distance = $entity->$right - $entity->$left + 1;
			if ($distance > 2) {
				$conditions = array(
					$left => array(
						'between' => array(
							$entity->$left + 1,
							$entity->$right - 1
						)
					)
				);

				$conn = $model::connection();
				$options = array(
					'type' => 'delete',
				);
				$options += compact('model', 'conditions');
				$query = new Query($options);
				$conn->delete($query, $options);
			}

			$increment = false;
			$conditions = array(
				'>' => $entity->$right
			);
			static::_sync($model, $distance, compact('conditions', 'increment'));
		}
	}

	/**
	 * Parent getter
	 */
	public static function parent($entity, $params = array(), $find = 'first') {
		$model = $entity->model();
		$settings = static::$__settings[$model];
		extract($settings);
		if (isset($entity->$parent)) {
			return $model::first($entity->$parent);
		}
	}

	public static function ancestors($entity, $params = array(), $find = 'all') {
		$model = $entity->model();
		$settings = static::$__settings[$model];
		extract($settings);
		$conditions = array(
			$left => array('<=' => $entity->$left),
			$right => array('>=' => $entity->$right)
		);
	}

	public static function children($entity, $params = array(), $find = 'all') {
		$model = $entity->model();
		$settings = static::$__settings[$model];
		extract($settings);
		$key = $model::meta('key');
		$conditions = array(
			$parent => $entity->$key
		);
	}

	public static function descendants($entity, $params = array(), $find = 'all') {
		$model = $entity->model();
		$settings = static::$__settings[$model];
		extract($settings);
		$conditions = array(
			$left => array('>' => $entity->$left),
			$right => array('<' => $entity->$right)
		);
	}

	public static function siblings($entity, $params = array(), $find = 'all') {
		$model = $entity->model();
		$settings = static::$__settings[$model];
		extract($settings);
		$conditions = array(
			$parent => $entity->$parent
		);
	}

	protected static function _find() {

	}

	protected function _findParams($finder, $entity, $params) {

	}

	protected static function _findFilters() {
		$class = get_called_class();
		$filters = array(
			'parent',
			'ancestors',
			'children',
			'descendants',
			'siblings'
		);
		foreach ($filters as $method) {
			$filter = function($model, $params, $chain) use ($method, $class){
				if (isset($params['entity'])) {

				}
				return false;
			};
		}
	}

	public static function move($entity, $distance = 0) {
		if (!$distance) {
			return false;
		}
	}

	public static function validate() {}

	public static function recover() {}

	/**
	 * Set the entity parent and sync all records
	 */
	protected static function _setParent($entity, $key = null) {
		$model = $entity->model();
		$settings = static::$__settings[$model];
		extract($settings);

		$edge = static::_right($model, $right, $key);

		if (empty($entity->$parent)) {
			$conditions = array(
				'between' => array(
					$entity->$left,
					$entity->$right
				)
			);
			$distance = $edge - $entity->$left + 1;
			static::_sync($model, $distance, compact('conditions', 'key'));

			$increment = false;
			$conditions = array('>' => $entity->$left);
			$distance = $entity->$right - $entity->$left + 1;
			static::_sync($model, $distance, compact('conditions', 'key', 'increment'));
		} else {
			if (!$parentNode = $model::first($entity->$parent)) {
				return false;
			}
			if ($entity->key() == $parentNode->key()) {
				return false;
			} elseif ($entity->$left < $parentNode->$left && $parentNode->$right < $entity->$right) {
				return false;
			}

			if (empty($entity->$left) && empty($entity->$right)) {
				$conditions = array('>=' => $parentNode->$right);
				static::_sync($model, 2, compact('conditions', 'key'));
				$entity->$left = $parentNode->$right;
				$entity->$right = $parentNode->$right + 1;
				$entity->save(null, array(
					'validate' => false,
					'whitelist' => array($left, $right),
					'callbacks' => false,
				));
			} else {
				$conditions = array(
					'between' => array(
						$entity->$left,
						$entity->$right
					)
				);
				$distance =  $edge - $entity->$left + 1;
				static::_sync($model, $distance, compact('conditions', 'key'));

				$distance = $entity->$right - $entity->$left + 1;
				if ($entity->$left > $parentNode->$left) {
					if ($entity->$right < $parentNode->$right) {
						$increment = false;
						$conditions = array(
							'between' => array(
								$entity->$right,
								$parentNode->$right -1
							)
						);
						static::_sync($model, $distance, compact('conditions', 'key', 'increment'));

						$distance = $edge - $parentNode->$right + $distance + 1;
						$conditions = array(
							'>' => $edge
						);
						static::_sync($model, $distance, compact('conditions', 'key', 'increment'));
					} else {
						$conditions = array(
							'between' => array(
								$parentNode->$right,
								$entity->$right
							)
						);
						static::_sync($model, $distance, compact('conditions', 'key'));

						$distance = $edge - $parentNode->$right + 1;
						$increment = false;
						$conditions = array(
							'>' => $edge
						);
						static::_sync($model, $distance, compact('conditions', 'key', 'increment'));
					}
				} else {
					$increment = false;
					$conditions = array(
						'between' => array(
							$entity->$right,
							$parentNode->$right -1
						)
					);
					static::_sync($model, $distance, compact('conditions', 'key', 'increment'));

					$distance = $edge - $parentNode->$right + $distance + 1;
					$conditions = array(
						'>' => $edge
					);
					static::_sync($model, $distance, compact('conditions', 'key', 'increment'));
				}
			}

			$conditions = $entity->key();
			$fields = array($left, $right);
			if ($updated = $model::first(compact('conditions', 'fields'))) {
				$entity->set($updated->data());
			}
		}
		return true;
	}

	protected static function _sync($model, $distance, array $options = array()) {
		if ($distance === 0 || $distance === '0') {
			return;
		}
		$settings = static::$__settings[$model];
		extract($settings);
		$options += array(
			'key' => null,
			'increment' => true,
			'conditions' => array(),
			'field' => 'both'
		);
		extract($options);

		if ($field == 'both') {
			static::_sync($model, $distance, array('field' => $left) + $options);
			$field = $right;
		}

		$conditions = array($field => $conditions);
		if ($key) {
			$val = array('!=' => reset($key));
			$conditions[key($key)] = $val;
		}
		$operator = $increment ? '+' : '-';
		$fields = "`{$field}` = `{$field}` {$operator} {$distance}";

		$conn = $model::connection();
		$query = new Query(array(
			'type' => 'update',
		) + compact('data', 'model', 'conditions'));

		$params = compact('fields') + $query->export($conn);
		$sql = $conn->renderCommand('update', $params, $query);
		return $conn->read($sql, array('return' => 'resource'));
	}

	/**
	 * Get the far right edge index
	 */
	protected static function _right($model, $right, $key = null) {
		$conditions = array();
		if ($key) {
			$val = array('!=' => reset($key));
			$conditions[key($key)] = $val;
		}

		$conn = $model::connection();
		$options = array('type' => 'read') + compact('model', 'conditions');
		$query = $model::invokeMethod('_instance', array('query', $options));

		$query->fields("MAX($right) as count", true);
		$query->map(array($query->alias() => array('count')));
		list($record) = $conn->read($query)->data();
		return isset($record['count']) ? intval($record['count']) : 0;
	}

	/**
	 * Get the left edge index
	 */
	protected static function _left($class, $left) {
		$conn = $model::connection();
		$options = array('type' => 'read') + compact('model');
		$query = $model::invokeMethod('_instance', array('query', $options));

		$query->fields("MIN({$left}) as count", true);
		$query->map(array($query->alias() => array('count')));
		list($record) = $conn->read($query)->data();
		return isset($record['count']) ? intval($record['count']) : null;
	}
}

?>