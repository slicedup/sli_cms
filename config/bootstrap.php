<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

use sli_base\storage\Registry;
use lithium\action\Dispatcher;

Registry::set('sli_cms.default', array(
	'routing' => array(
		'match' => '.*'
	),
	'connection' => 'default'
));

Dispatcher::config(array(
	'classes' => array(
		'router' => 'sli_cms\net\http\Router'
	)
));