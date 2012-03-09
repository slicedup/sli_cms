<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\models;

class Pages extends Leaves {

	protected $_meta = array(
		'source' => 'cms_pages'
	);

	public static function controller() {
		return 'cms_pages';
	}

}

?>