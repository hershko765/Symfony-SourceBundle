<?php

namespace App\SourceBundle\Base\Repository;

use Doctrine\ORM\QueryBuilder;
use App\SourceBundle\Helpers\Arr;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\SourceBundle\Components\SilentLog;


abstract class Core extends EntityRepository {

	/**
	 * Define permissions
	 */
	const PERM_NONE   = 0;
	const PERM_CREATE = 1;
	const PERM_UPDATE = 2;
	const PERM_ALL    = 3;

	/**
	 * List of available filters, to be overwritten by the child repository
	 * @var array $filterMap
	 */
	protected $filterMap;

	/**
	 * Service container
	 * @var ContainerBuilder
	 */
	protected $services;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * List of table fields, permissions and covert types
	 * @var
	 */
	protected $tableMap;


	/**
	 * table primary key
	 * @var string
	 */
	protected $pk = 'id';

	public function __construct($em, Mapping\ClassMetadata $class)
	{
		$this->services = new ContainerBuilder();
		$this->services->register('Filters', 'App\SourceBundle\Base\Repository\Core\Filters');
		parent::__construct($em, $class);
	}

	/**
	 * Add filters to query
	 *
	 * @param QueryBuilder $qb
	 * @param array        $filters
	 */
	protected function addFilters(QueryBuilder &$qb, array $filters)
	{
		$filtersIdx = Arr::Column($this->filterMap, NULL, 0);
		$filtersService = $this->services->get('filters');

		foreach($filters as $filterName => $value)
		{
			if(method_exists($this, 'filter'.ucwords($filterName)))
			{
				$this->{'filter'.ucwords($filterName)}($qb, $value);
				continue;
			}

			if ( ! $this->isAllowedFilter($filterName)) continue;
			$column   = $filtersIdx[$filterName][1];
			$operator = $filtersIdx[$filterName][2];

			$filtersService->register($qb, $operator);
			$filtersService->addFilter([ 'column' => $column, 'value'  => $value, 'filterName' => $filterName ]);
		}
	}

	/**
	 * Add paging options to query
	 *
	 * @param QueryBuilder $qb
	 * @param array        $paging
	 */
	protected function addPaging(QueryBuilder &$qb, array $paging)
	{
		if (Arr::get($paging, 'page'))
		{
			list($page, $perPage) = $paging['page'];
			$paging['limit'] = $perPage;
			$paging['offset'] = ($page != 0 ? $page - 1 : 1) * $perPage;
		}

		// Add limit if given
		if (Arr::get($paging, 'limit'))
			$qb->setMaxResults($paging['limit']);

		// Add offset if given
		if (Arr::get($paging, 'offset'))
			$qb->setFirstResult($paging['offset']);

		// Adding order type ( ASC, DESC ) if exists and sort if exists
		$qb->orderBy(Arr::get($paging, 'sort') ? 'entity.'.$paging['sort'] : 'entity.'.$this->pk, Arr::get($paging, 'order') == 'DESC' ? 'DESC' : 'ASC');
	}

	/**
	 * Add settings to query
	 *
	 * @param QueryBuilder $qb
	 * @param              $settings
	 */
	protected function addSettings(QueryBuilder &$qb, $settings)
	{
		if(Arr::get($settings, 'select'))
		{
			foreach ($settings['select'] as $idx => $select)
			{
				if ( ! $this->isColumnExists($select)) continue;
				if($idx === 0) $qb->select('entity.'.$select);
				else $qb->addSelect('entity.'.$select);
			}
		}
	}

	/**
	 * Check if a given filter is allowed by the filter map
	 * of the repository, create a silent report if not
	 *
	 * @param $filter
	 * @return bool
	 */
	protected function isAllowedFilter($filter)
	{
		$filters = Arr::Column($this->filterMap, 1, 0);
		if ( ! array_key_exists($filter, $filters))
		{
			SilentLog::getInstance()->silentLog([
					'message' => 'Not allowed filter request access',
					'metadata' => 'filterName='.$filter.'&table='.$this->tableName
				]);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if a given column is exists in the table map list
	 *
	 * @param $col
	 * @return bool
	 */
	protected function isColumnExists($col)
	{
		if ( ! Arr::get($this->tableMap, $col))
		{
			SilentLog::getInstance()->silentLog([
					'message'  => 'Column not exists on select settings',
					'metadata' => 'column='.$col.'&table='.$this->tableName
				]);
			return FALSE;
		}
		return TRUE;
	}

} // End Core 