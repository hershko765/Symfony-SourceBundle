<?php

namespace App\SourceBundle\Interfaces;

interface Handler {

	/**
	 * @param array $paging
	 * @return $this
	 */
	public function setPaging(array $paging);

	/**
	 * @param array $filters
	 * @return $this
	 */
	public function setFilters(array $filters);

	/**
	 * @param array $settings
	 * @return $this
	 */
	public function setSettings(array $settings);

	/**
	 * @return array
	 */
	public function execute();
}