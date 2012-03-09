<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\net\http;

use sli_base\storage\Registry;

class Router extends \lithium\net\http\Router {

	protected static $_classes = array(
		'route' => 'sli_cms\net\http\Route',
		'model' => 'sli_cms\models\Routes'
	);

	/**
	 * Matches the current request firstly by url against cms configs.
	 * If a match is found the request is than matched against stored
	 * cms routes, if a matching cms route is found, it is parsed, and
	 * added to the top of the route stack prior to being parsed in the
	 * default router.
	 *
	 * @param unknown_type $request
	 * @return multitype:
	 */
	public static function parse($request) {
		$route = static::$_classes['route'];
		$configs = Registry::get('sli_cms');
		foreach ($configs as $name => $config) {
			$matched = $match = $config['routing']['match'];
			if (is_string($match)) {
				$matched = preg_match("@{$config['routing']['match']}@", $request->url);
			}
			if ($matched && $route = static::cmsRoute($request)) {
				array_unshift(static::$_configurations, $route);
				break;
			}
		}
		return parent::parse($request);
	}

	/**
	 * Attempts to match a request against a stored cms route, load the
	 * subsequent leaf and obtain routing parameters from the leave's
	 * handler class.
	 *
	 * @param Request $request
	 * @param array $filters
	 */
	public static function cmsRoute($request, $filters = array()) {
		$model = static::$_classes['model'];
		$query = array(
			'conditions' => array(
				'url' => $request->url
			)
		);

		$filter = function ($self, $params, $chain) {
			extract($params);
			return $model::first($query);
		};
		$params = compact('request', 'model', 'query');
		$match = static::_filter(__FUNCTION__, $params, $filter, $filters);

		if ($match && $leaf = $match->leaf()) {
			if ($class = $leaf->loadClass($request)) {
				$route = static::$_classes['route'];
				$template = $match->url(array(
					'base' => $request->env('base')
				));
				$param = $options = array();

				$config = $class::loadRoute($leaf, $match, $request);
				extract($config);
				if (is_callable($options)) {
					$options = array('handler' => $options);
				}
				return new $route(compact('template', 'params') + $options);
			}
		}
	}
}

?>