<?php

namespace App\SourceBundle\Base;

use App\SourceBundle\Helpers\Arr;
abstract class Model {

	/**
	 * set multiple values into the model
	 *
	 * @param $data
	 */
	public function setValues($data)
	{
		foreach($data as $col => $val)
		{
			if(method_exists($this, 'set'.ucwords($col)))
			{
				$this->{'set'.ucwords($col)}($val);
			}
		}
	}

	/**
	 * Convert entity values as array
	 * @return array
	 */
	public function asArray()
	{
		return $this->_asArray();
	}

	/**
	 * Convert entity values as JSON
	 * @return string
	 */
	public function asJSON()
	{
		return json_encode($this->_asArray());
	}

	/**
	 * Doing all the convert work,
	 * convert the entity to array
	 *
	 * @return array
	 */
	private function _asArray()
	{
		$asArr = [];
		$entity = explode('\\', get_class($this));
		$entity = $entity[count($entity) - 1];

		foreach((array) $this as $key => $val)
		{
			$key = explode('\\'.$entity, $key);
			$key = Arr::get($key, 1, 'NOTFOUND');
			$asArr[trim($key)] = $val;
		}

		return $asArr;
	}
}