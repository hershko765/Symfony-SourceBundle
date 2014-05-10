<?php

namespace App\SourceBundle\Base;

use Symfony\Bundle\FrameworkBundle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\SourceBundle\Helpers\Arr;

class TaskGateway {

	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerBuilder
	 */
	private $task_container;
	private $container;

	public function __construct($container)
	{
		$this->container = $container;
		$this->task_container =  new ContainerBuilder();
	}

	public function registerContainer()
	{

	}

	/**
	 * Get task class
	 *
	 * @param $bundle
	 * @param $entity
	 * @param $task
	 * @throws Exception
	 * @return TaskManager object
	 */
	public function getTask($entity, $task, $bundle = FALSE)
	{
		// Creating class alias for task container
		$task_alias = strtolower($bundle).'_'.strtolower($entity).'_'.strtolower($task);
		// Get task class name
		$task_class = 'App\\'.ucwords($bundle).'Bundle\Entities\Task\\'.ucwords($entity).'\\'.ucwords($task);
		if(class_exists($task_class))
		{
			// Checking if the task container already have that task, if not creating one
			if ( ! $this->task_container->has($task_alias))
			{
				$this->registerTaskDependency($task_class, $task_alias);
			}

			return $this->task_container->get($task_alias);
		}

		throw new Exception('Task class not found!');
	}

	/**
	 * Register task and inject dependencies
	 *
	 * @param $task
	 * @param $alias
	 * @throws \Symfony\Component\Config\Definition\Exception\Exception
	 */
	private function registerTaskDependency($task, $alias)
	{
		// Register task
		$taskReg = $this->task_container->register($alias, $task);
		// Getting construct method to inject dependencies
		$ref = new \ReflectionClass($task);
		$argKeys = [];
		// Inject dependencies
		foreach($ref->getProperties() as $prop)
		{
			$docs = $prop->getDocComment();
			preg_match('/@DI([ ]{0,})\((?<props>.*)\)/', $docs, $match);
			if ( ! Arr::get($match, 'props')) continue;
			$docs = Arr::formatArray(Arr::get($match, 'props'), '=');
			$alias = Arr::get($docs, 'alias', $prop->getName());
			// Checking if the container has the dependency
			if ( ! $this->container->has($alias))
				throw new Exception('Service '.$alias.' is not registered within service manager!');

			// Add argument to the task registration
			$argKeys[] = $prop->getName();
			$taskReg->addArgument($this->container->get($alias));
		}

		$taskReg->addArgument($argKeys);
	}
} // End TaskGateway 