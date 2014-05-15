<?php

namespace App\SourceBundle\Base;

use Symfony\Bundle\FrameworkBundle;
use App\SourceBundle\Base;
use App\SourceBundle\Helpers\Arr;
use Symfony\Component\Config\Definition\Exception\Exception;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\Exception\InvalidArgumentException;

class Controller extends FOSRestController {

	public function getHandler($entity, $handler = FALSE, $bundle = FALSE)
	{
		if ( ! $handler)
		{
			$params = explode('|', $entity);
			$entity = Arr::get($params, 0);
			$handler = Arr::get($params, 1);

			if( ! $handler)
				throw new InvalidArgumentException('Could not resolve handler name, please provide it');
		}

		// If bundle wasn't provided, trying to extract the name by the called controller
		if ( ! $bundle)
		{
			preg_match('/(?<BUNDLE>[\w]+)Bundle/', get_called_class(), $match);
			if ( ! Arr::get($match, 'BUNDLE'))
				throw new Exception('Could not extract bundle name, you should provide it in this case');

			$bundle = Arr::get($match, 'BUNDLE');
		}

		$gateway = $this->container->get('handler_gateway');

		return $gateway->getHandler($entity, $handler, $bundle);
	}
}