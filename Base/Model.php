<?php

namespace App\SourceBundle\Base;
use App\SourceBundle\Helpers\Arr;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use DateTime;

/**
 * Class Model
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @package App\SourceBundle\Base
 */
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
			$method = 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $col)));
			if(method_exists($this, $method))
			{
				$this->{$method}($val);
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