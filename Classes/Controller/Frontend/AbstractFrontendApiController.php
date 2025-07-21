<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Controller\Frontend;

use Psr\Http\Message\ResponseInterface;

abstract class AbstractFrontendApiController extends AbstractFrontendController
{
	protected function resolveRestAction(...$arguments): ResponseInterface
	{
		$requestMethod = $this->request->getMethod();
		$actionName = $this->request->getControllerActionName();
		$finalAction = $actionName .'_'. $requestMethod;
		
		if (method_exists($this, $finalAction)) {
			return $this->$finalAction(...$arguments);
		}
		
		$this->throwStatus(405, null, $this->encodeResponse([
				'error' => 'Method '. $requestMethod .' is not allowed for action "'. $actionName .'".',
			]));
	}
	
	protected function encodeResponse(array $response): string
	{
		return json_encode($response);
	}
}