<?php

namespace App\SourceBundle\Base\Repository\Core;

use App\SourceBundle\Helpers\Arr;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\DBAL\Query\QueryBuilder;

class Filters {

	/**
	 * Filter type
	 * @var
	 */
	private $type;

	/**
	 * @var QueryBuilder
	 */
	private $qb;

	/**
	 * Available filters and there callback
	 * @var array
	 */
	private $map = [
		'='       => 'filterEqual',
		'>'       => 'filterBigger',
		'<'       => 'filterSmaller',
		'LIKE'    => 'filterLike',
		'BETWEEN' => 'filterBetween'
	];

	public function __construct() {}

	public function register(&$qb, $type)
	{
		if ( ! $this->isValidType($type))
			throw new Exception('Filter type "'.$type.'" is invalid, null or not supported');

		$this->qb = $qb;
		$this->type = $type;
	}

	public function addFilter($params)
	{
		$this->{$this->map[$this->type]}($params);
	}

	private function isValidType($type)
	{
		return array_key_exists($type, $this->map);
	}

	private function filterEqual($params)
	{
		$this->qb->where("entity.".$params['column'].' = :'.$params['filterName']);
		$this->qb->setParameter($params['filterName'], $params['value']);
	}

	private function filterLike($params)
	{
		$this->qb->where("entity.".$params['column'].' LIKE :'.$params['filterName']);
		$this->qb->setParameter($params['filterName'], '%'.$params['value'].'%');
	}

	private function filterBigger($params)
	{
		$this->qb->where("entity.".$params['column'].' > :'.$params['filterName']);
		$this->qb->setParameter($params['filterName'], $params['value']);
	}

	private function filterSmaller($params)
	{
		$this->qb->where("entity.".$params['column'].' > :'.$params['filterName']);
		$this->qb->setParameter($params['filterName'], $params['value']);
	}

	private function filterBetween($params)
	{
		$range = $params['value'];
		$param_1 = $params['filterName'].'_1';
		$param_2 = $params['filterName'].'_2';

		if ( ! Arr::get($range, 0) || ! Arr::get($range, 1))
			throw new Exception('Between filter excpet array with 2 values');

		$this->qb->where("entity.".$params['column'].' BETWEEN :'.$param_1.' AND :'.$param_2);
		$this->qb->setParameter($param_1, $range[0]);
		$this->qb->setParameter($param_2, $range[1]);
	}

} // End Filter 