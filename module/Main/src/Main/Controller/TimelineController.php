<?php

namespace Main\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
require '../../../../../sparqllib.php';

/**
 *
 */
class TimelineController extends AbstractRestfulController
{
	public function getList()
	{
		$data = array(
				'French_Revolution',
				'Spanish_Civil_War',
				'...'
		);

		return $data;
	}

	public function get($id) {
		
	}

	public function create($data) {}

	public function update($id, $data) {}

	public function delete($id) {}
}
