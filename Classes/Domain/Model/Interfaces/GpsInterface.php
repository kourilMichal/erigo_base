<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Interfaces;

interface GpsInterface
{
	public function getGpsLatitude(): string;
	
	public function setGpsLatitude(string $gpsLatitude): void;
	
	public function getGpsLongitude(): string;
	
	public function setGpsLongitude(string $gpsLongitude): void;

	public function canShowGpsLocation(): bool;

	public function getCanShowGpsLocation(): bool;
}