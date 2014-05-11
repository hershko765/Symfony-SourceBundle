<?php

namespace App\SourceBundle\Base;

use App\SourceBundle\Helpers\Arr;
use App\SourceBundle\Interfaces\Handler;

abstract class HandlerManager implements Handler {

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

	public function setFilters(array $filters)
	{
		$this->filters = $filters;
		return $this;
	}

	public function setSettings(array $settings)
	{
		$this->settings = $settings;
		return $this;
	}

	public function setPaging(array $paging)
	{
		$this->paging = $paging;
		return $this;
	}

	abstract function execute();
}