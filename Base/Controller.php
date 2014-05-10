<?php

namespace App\SourceBundle\Base;

use Symfony\Bundle\FrameworkBundle;
use App\SourceBundle\Base;
use App\SourceBundle\Helpers\Arr;
use Symfony\Component\Config\Definition\Exception\Exception;

class Controller extends FrameworkBundle\Controller\Controller {

	public function getTask($entity, $task, $bundle = FALSE)
	{
		// If bundle wasn't provided, trying to extract the name by the called controller
		if ( ! $bundle)
		{
			preg_match('/(?<BUNDLE>[\w]+)Bundle/', get_called_class(), $match);
			if ( ! Arr::get($match, 'BUNDLE'))
				throw new Exception('Could not extract bundle name, you should provide it in this case');

			$bundle = Arr::get($match, 'BUNDLE');
		}

		$gateway = $this->container->get('task_gateway');

		return $gateway->getTask($entity, $task, $bundle);
	}
}