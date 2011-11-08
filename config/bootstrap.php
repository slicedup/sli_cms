<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

use sli_libs\core\LibraryRegistry;
use lithium\action\Dispatcher;

LibraryRegistry::init('sli_cms');

LibraryRegistry::add('sli_cms', 'default', array(
	'routing' => array(
		'match' => '.*'
	)
));

Dispatcher::config(array(
	'classes' => array(
		'router' => 'sli_cms\net\http\Router'
	)
));