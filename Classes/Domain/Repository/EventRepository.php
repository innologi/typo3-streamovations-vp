<?php
namespace Innologi\StreamovationsVp\Domain\Repository;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use Innologi\StreamovationsVp\Library\Rest\Repository;
/**
 * Event/Session Repository
 *
 * Event/Session API:
 * - [at] : optional : date
 * - [from] : optional : start date (inclusive)
 * - [through] : optional : end date (inclusive)
 * - [tags] : optional : alphanum, colon-separated list
 * - [category] : optional : alphanum
 * - [subcategory] : optional : alphanum
 *
 * Supported date formats:
 * - yyyy-mm-dd
 * - hh:ii:ss
 * - yyyy-mm-ddThh:ii:ss
 *
 * @package streamovations_vp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class EventRepository extends Repository {

	// DateTime formats
	const FORMAT_DATE = 'Y-m-d';
	const FORMAT_TIME = 'H:i:s';
	const FORMAT_DATETIME = 'Y-m-d\TH:i:s';

	/**
	 * @var string
	 */
	protected $category = '';

	/**
	 * @var string
	 */
	protected $subCategory = '';

	/**
	 * @var string
	 */
	protected $tags = '';

	/**
	 * Sets category
	 *
	 * @param string $category
	 * @return EventRepository
	 */
	public function setCategory($category) {
		$this->category = $category;
		return $this;
	}

	/**
	 * Sets subcategory
	 *
	 * @param string $subCategory
	 * @return EventRepository
	 */
	public function setSubCategory($subCategory) {
		$this->subCategory = $subCategory;
		return $this;
	}

	/**
	 * Sets tags
	 * @param string $tags
	 * @return EventRepository
	 */
	public function setTags($tags) {
		$this->tags = $tags;
		return $this;
	}

	/**
	 * Returns events of a specific date/time as per $format
	 *
	 * @param \DateTime $dateTime
	 * @param string $format
	 * @return array
	 */
	public function findAt(\Datetime $dateTime, $format) {
		$request = $this->createRequest()
			->addArgument('at', $dateTime->format($format));
		return $this->addSharedArguments($request)->send();
	}

	/**
	 * Returns events of a specific date
	 *
	 * @param \DateTime $dateTime
	 * @return array
	 */
	public function findAtDate(\DateTime $dateTime) {
		return $this->findAt($dateTime, self::FORMAT_DATE);
	}

	/**
	 * Returns events of a specific date/time
	 *
	 * @param \DateTime $dateTime
	 * @return array
	 */
	public function findAtDateTime(\DateTime $dateTime) {
		return $this->findAt($dateTime, self::FORMAT_DATETIME);
	}

	/**
	 * Returns events between and including(!) $from and $to dates/times
	 *
	 * @param \DateTime $from
	 * @param \DateTime $to (optional)
	 * @return array
	 */
	public function findBetweenDateTimeRange(\DateTime $from, \DateTime $to = NULL) {
		$request = $this->createRequest()
			->addArgument('from', $from->format(self::FORMAT_DATETIME));
		if ($to !== NULL) {
			$request->addArgument('through', $to->format(self::FORMAT_DATETIME));
		}
		return $this->addSharedArguments($request)->send();
	}

	/**
	 * Applies optional arguments to request:
	 * - category
	 * - subcategory
	 * - tags
	 *
	 * @param \Innologi\StreamovationsVp\Library\Rest\RequestInterface $request
	 * @return \Innologi\StreamovationsVp\Library\Rest\RequestInterface
	 */
	protected function addSharedArguments(\Innologi\StreamovationsVp\Library\Rest\RequestInterface $request) {
		if (isset($this->category[0])) {
			$request->addArgument('category', $this->category);
		}
		if (isset($this->subCategory[0])) {
			$request->addArgument('subcategory', $this->subCategory);
		}
		if (isset($this->tags[0])) {
			$request->addArgument('tags', $this->tags);
		}
		return $request;
	}

}
