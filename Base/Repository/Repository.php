<?php
namespace App\SourceBundle\Base\Repository;

use App\SourceBundle\Helpers\Arr;
use Doctrine\ORM\Mapping;;
use App\SourceBundle\Base;
use DateTime;

abstract class Repository extends Core {

	/**
	 * Abilities array
	 *
	 * @var array
	 */
	protected $abilities = [];

	/**
	 * -- [ Creatable Model Ability ] --
	 * Fill a created column in the database every
	 * time that record is added
	 * - Requires Column Created with type = datetime
	 */
	const CREATABLE = 1;

	/**
	 * -- [ Creatable Model Ability ] --
	 * Fill updated column in the database every time
	 * that a record is updated
	 * - Requires Column updated with type = datetime
	 */
	const UPDATABLE = 2;

	/**
	 * -- [ Creatable Model Ability ] --
	 * Allow soft delete of a record,
	 * soft delete will fill deleted column in a date deleted
	 * - Requires Column updated with type = datetime
	 */
	const SOFT_DELETABLE = 3;

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
	 * Fill model by permissions
	 *
	 * @param $data
	 * @param $permission
	 * @return array
	 */
	public function hydrate($data, Base\Model &$model, $permission)
	{
		// Load table map
		$columns = array_keys($this->tableMap);
		$permissions = Arr::Column($this->tableMap, 1);

		// Filter by permission
		$colPerms        = array_combine($columns, $permissions);
		$convertedValues = array_intersect($colPerms, [ $permission, self::PERM_ALL ]);
		$convertedData   = array_intersect_key($data, $convertedValues);

		// Set abilities if exists
		$this->setAbilities($model);

		// Set Model with the filtered values
		$model->setValues($convertedData);
	}

	/**
	 * Set Created at value
	 *
	 * @ORM\PrePersist
	 */
	public function setAbilities(Base\Model &$model)
	{
		if(in_array(self::CREATABLE, $this->abilities) && method_exists($model, 'setCreated'))
			$model->setCreated(new DateTime());

		if(in_array(self::UPDATABLE, $this->abilities) && method_exists($model, 'setUpdated'))
			$model->setUpdated(new DateTime());

		if(in_array(self::SOFT_DELETABLE, $this->abilities) && method_exists($model, 'setDeleted'))
			$model->setDeleted(new Datetime());
	}
}

/**
 * Filter classes
 * Filter Converters
 */