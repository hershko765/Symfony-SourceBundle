<?php

namespace App\SourceBundle\Base;

use App\SourceBundle\Helpers\Arr;

abstract class TaskManager {

	/**
	 * Inject dependencies into class properties
	 */
	public function __construct()
	{
		$args = func_get_args();
		$DIarray = Arr::get($args, count(func_get_args()) - 1);
		foreach ($DIarray as $key => $DIClass)
		{
			$this->{$DIClass} = Arr::get($args, $key);
		}
	}

	/**
	 * Convert Validator object to array
	 * @param $errors
	 * @return array
	 */
	protected function errorsToArr($errors)
	{
		$errorArr = [];
		foreach ($errors as $error)
		{
			$errorArr[$error->getPropertyPath()] = $error->getMessage();
		}

		return $errorArr;
	}

	public function setFilters($filters)
	{
		$this->filters = $filters;
		return $this;
	}

	public function setSettings($settings)
	{
		$this->settings = $settings;
		return $this;
	}

	public function setPaging($paging)
	{
		$this->paging = $paging;
		return $this;
	}

	abstract function execute();
}