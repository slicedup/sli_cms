<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_cms\tests\mocks\models;

class Branches extends \sli_filters\tests\mocks\models\MockModel {

	protected $_meta = array(
		'source' => 'mock-source'
	);

	protected $_schema = array(
		'id' => array(
			'type' => 'integer',
			'length' => 10,
			'null' => false,
			'default' => null
		),
		'branch_id' => array(
			'type' => 'integer',
			'length' => 10,
			'null' => true,
			'default' => null
		),
		'branch_left' => array(
			'type' => 'integer',
			'length' => 10,
			'null' => false,
			'default' => null
		),
		'branch_right' => array(
			'type' => 'integer',
			'length' => 10,
			'null' => false,
			'default' => null
		),
		'title' => array(
			'type' => 'string',
			'length' => 128,
			'null' => false,
			'default' => null
		)
	);
}
?>