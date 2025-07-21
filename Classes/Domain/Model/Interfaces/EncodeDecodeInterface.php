<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Interfaces;

interface EncodeDecodeInterface
{
	public function encode(): string;
	
	public function encodeProperty(string $property): mixed;
	
	public function decode(string $encodedData): void;
	
	public function decodeProperty(string $property, $encodedValue): void;
	
	public function getEncodeProperties(): array;
}