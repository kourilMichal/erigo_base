<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Middleware;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\{Site, SiteLanguage};
use Erigo\ErigoBase\Routing\Enhancer\ApiEnhancer;

class ApiMiddleware implements MiddlewareInterface
{
	/**
	 * @see \Psr\Http\Server\MiddlewareInterface::process()
	 * @see \TYPO3\CMS\Frontend\Middleware\PageResolver::process()
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$apiPathRegex = '#^'. ApiEnhancer::PATH_PREFIX .'/#';
		
		if (preg_match($apiPathRegex, $request->getUri()->getPath())) {
		    $site = $request->getAttribute('site', null);
			$language = $request->getAttribute('language', null);
			
			$hasSiteConfiguration = (
			    $language instanceof SiteLanguage && 
			    $site instanceof Site
			);
			
			if ($hasSiteConfiguration) {
				$previousResult = $request->getAttribute('routing', null);
				
				if ($previousResult) {
					$requestId = (string)($request->getQueryParams()['id'] ?? '');
					
					if (empty($requestId)) {
						try {
							$pageArguments = $site->getRouter()->matchRequest($request, $previousResult);
							
						} catch (RouteNotFoundException $e) {
							$apiPath = preg_replace($apiPathRegex, '/', $request->getUri()->getPath());
							
							return new HtmlResponse(
							    json_encode(['error' => 'Path "'. $apiPath .'" is invalid.']), 
							    404,
						    );
						}
					}
				}
			}
		}
		
		return $handler->handle($request);
	}
}