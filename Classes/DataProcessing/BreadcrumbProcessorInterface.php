<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\DataProcessing;

interface BreadcrumbProcessorInterface
{
	public function getExtensionName(): string;
	
	public function processBreadcrumbs(array &$breadcrumbs): void;
}