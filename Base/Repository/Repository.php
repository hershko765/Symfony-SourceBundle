<?php
namespace App\SourceBundle\Base\Repository;

use App\SourceBundle\Helpers\Arr;
use Doctrine\ORM\Mapping;;

abstract class Repository extends Core {

	/**
	 * Generic finding records by custom options
	 * ## Example
	 * filters  - [ 'name'   => 'some name'       ]
	 * paging   - [ 'limit'  => 5, 'offset' => 10 ]
	 * settings - [ 'select' => [ 'id', 'title'   ]
	 *
	 * @param array $filters
	 * @param array $paging
	 * @param array $settings
	 */
	public function collect(array $filters = [], array $paging = [], array $settings = [])
	{
		$qb = $this->createQueryBuilder('entity');

		$this->addFilters($qb, $filters);
		$this->addPaging($qb, $paging);
		$this->addSettings($qb, $settings);

		return $qb->getQuery()->getArrayResult();
	}

	/**
	 * Save entity object
	 *
	 * @param $model
	 * @return mixed
	 */
	public function save($model)
	{
		$em = $this->getEntityManager();
		$em->persist($model);
		$em->flush();

		return $model;
	}

	/**
	 * Delete a record
	 *
	 * @param $id
	 */
	public function delete($id)
	{
		$em = $this->getEntityManager();

		$addon = $this->find($id);
		if ($addon)
		{
			$em->remove($addon);
			$em->flush();
		}

		return $addon;
	}

	/**
	 * @param $data
	 * @param $permission
	 * @return array
	 */
	public function convert($data, $permission)
	{
		$columns = array_keys($this->tableMap);
		$permissions = Arr::Column($this->tableMap, 1);
		$colPerms = array_combine($columns, $permissions);
		$convertedValues = array_intersect($colPerms, [$permission, self::PERM_ALL]);

		$convertedData = array_intersect_key($data, $convertedValues);

		return $convertedData;
	}
}

/**
 * Filter classes
 * Filter Converters
 */