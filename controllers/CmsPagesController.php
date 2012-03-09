<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\controllers;

class CmsPagesController extends \lithium\action\Controller {

	public function view() {
		$page = $this->request->params['leaf']->load();
		$this->set(compact('page'));
	}

}
?>