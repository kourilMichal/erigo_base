<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

trait GpsTrait
{
	protected string $gpsLatitude = '';
	protected string $gpsLongitude = '';
	
	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\GpsInterface::getGpsLatitude()
	 */
	public function getGpsLatitude(): string
	{
		return $this->gpsLatitude;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\GpsInterface::setGpsLatitude()
	 */
	public function setGpsLatitude(string $gpsLatitude): void
	{
		$this->gpsLatitude = $gpsLatitude;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\GpsInterface::getGpsLongitude()
	 */
	public function getGpsLongitude(): string
	{
		return $this->gpsLongitude;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\GpsInterface::setGpsLongitude()
	 */
	public function setGpsLongitude(string $gpsLongitude): void
	{
		$this->gpsLongitude = $gpsLongitude;
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\GpsInterface::canShowGpsLocation()
	 */
	public function canShowGpsLocation(): bool
	{
		return (!empty($this->gpsLatitude) && !empty($this->gpsLongitude));
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\GpsInterface::getCanShowGpsLocation()
	 */
	public function getCanShowGpsLocation(): bool
	{
		return $this->canShowGpsLocation();
	}
}