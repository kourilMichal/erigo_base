<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Traits;

use Erigo\ErigoBase\Utility\DomainUtility;

trait EncodeDecodeTrait
{
	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\EncodeDecodeInterface::encode()
	 */
	public function encode(): string
	{
		return DomainUtility::encode($this);
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\EncodeDecodeInterface::encodeProperty()
	 */
	public function encodeProperty(string $property): mixed
	{
		return DomainUtility::encodeProperty($this, $property);
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\EncodeDecodeInterface::decode()
	 */
	public function decode(string $encodedData): void
	{
		DomainUtility::decode($this, $encodedData);
	}

	/**
	 * @see \Erigo\ErigoBase\Domain\Model\Interfaces\EncodeDecodeInterface::decodeProperty()
	 */
	public function decodeProperty(string $property, $encodedValue): void
	{
		DomainUtility::decodeProperty($this, $property, $encodedValue);
	}
	
	public function getEncodedData(): string
	{
		return $this->encode();
	}
}