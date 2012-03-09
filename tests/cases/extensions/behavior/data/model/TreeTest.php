<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\tests\cases\extensions\behavior\data\model;

use lithium\data\Connections;
use sli_cms\extensions\behavior\data\model\Tree;

class TreeTest extends \lithium\test\Unit {

	/*
	protected static $model = 'sli_cms\tests\mocks\models\Branches';

	protected static $config;

	public function setUp() {
		$model = static::$model;
		$model::resetFilters();
		$this->config =& Tree::apply($model, array(
			'parent' => 'branch_id',
			'left' => 'branch_left',
			'right' => 'branch_right'
		));
	}
	*/

	protected static $model = 'sli_cms\models\Branches';

	public function _init() {
		$model = static::$model;
		$this->config =& Tree::apply($model, array(
			'parent' => 'branch_id',
			'left' => 'branch_left',
			'right' => 'branch_right'
		));
	}

	public function testBinding() {
		$model = static::$model;
	}

	public function testOne() {
		$model = static::$model;
		$b1 = $model::create();
		$b1->leaf_id = '1001';
		$b1->save();

		v($b1->data());

		$b2 = $model::create();
		$b2->leaf_id = '1002';
		$b2->branch_id = $b1->id;
		$b2->save();

		v($b2->data());

		$b3 = $model::create();
		$b3->leaf_id = '1003';
		$b3->branch_id = $b1->id;
		$b3->save();

		v($b3->data());

//		$b1->delete();

//		$b1->delete();
//		$b2->delete();
	}
}
?>