<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\models;

use lithium\action\Response;

class Redirects extends Leaves {

	protected $_meta = array(
		'source' => 'cms_redirects'
	);

	public static function loadRoute($record, $request) {
		$route = parent::loadRoute($record, $request);
		$route['params'] = array();
		$route['options'] = static::_redirect($record, $request);
		return $route;
	}

	public static function _redirect($record, $request) {
		$options = array('location' => '/', 'status' => 302, 'head' => true, 'exit' => false);
		if ($redirect = $record->load()) {
			$data = $redirect->data();
			$options = array_filter($data) + $options;
		}
		return function() use ($options){
			return new Response($options);
		};
	}
}
?>