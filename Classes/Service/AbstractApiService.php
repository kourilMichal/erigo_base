<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Service;

use TYPO3\CMS\Core\SingletonInterface;

abstract class AbstractApiService implements SingletonInterface
{
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	
	const CONTENT_TYPE_JSON = 'application/json';
	const CONTENT_TYPE_URL_ENCODED = 'application/x-www-form-urlencoded';
	
	abstract public function getServiceName(): string;
	
	protected function makeRequest(
	    string $url, 
	    string $method = self::METHOD_GET, 
	    array $params = [], 
	    array $headers = [], 
		int $jsonFlags = 0,
    ): ?array 
    {
		$ch = curl_init();
		
		if ($ch === false) {
			throw new \Exception('cURL initialization failed.');
		}
		
		if (count($headers) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->prepareHeaders($headers));
		}
		
		$authMethod = $this->getAuthMethod();
		
		if (!empty($authMethod)) {
			curl_setopt($ch, CURLOPT_HTTPAUTH, $authMethod);
			curl_setopt($ch, CURLOPT_USERPWD, $this->getAuthData());
		}
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$requestUrl = $this->getBaseUrl() . $url;
		$postParams = null;
		
		if ($method == self::METHOD_GET) {
			if (count($params) > 0) {
				if (strpos($requestUrl, '?') === false) {
					$requestUrl .= '?'. http_build_query($params);
					
				} else {
					$requestUrl .= '&'. http_build_query($params);
				}
			}
			
		} else {
			if ($method == self::METHOD_POST) {
				curl_setopt($ch, CURLOPT_POST, true);
				
			} else {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			}

			if (count($params) > 0) {
				switch ($headers ['Content-Type']) {
					case self::CONTENT_TYPE_JSON:
						$postParams = json_encode($params, $jsonFlags);
						break;
		
					case self::CONTENT_TYPE_URL_ENCODED:
						$postParams = http_build_query($params);
						break;
				}
				
				if ($postParams !== null) {
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
				}
			}
		}
		
		curl_setopt($ch, CURLOPT_URL, $requestUrl);
		
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		if ($info['http_code'] < 400) {
			return $this->prepareResponse($response, $url, $method, $params, $headers, $jsonFlags);
		}
		
		throw new \UnexpectedValueException($this->prepareExceptionMessage(
				'API call to URL <'. $info['url'] .'> returns code '. $info['http_code'] .'.', 
				$response, 
				$postParams
			));
	}
	
	protected function prepareHeaders(array $headers): array
	{
		$preparedHeaders = array();
		
		foreach ($headers as $headerName => $headerValue) {
			$preparedHeaders[] = $headerName .': '. $headerValue;
		}
		
		return $preparedHeaders;
	}
	
	protected function prepareResponse(
	    string|bool $response, 
	    string $url, 
	    string $method = self::METHOD_GET, 
	    array $params = [], 
		array $headers = [], 
	    int $jsonFlags = 0,
    ): ?array 
    {
		if ($response !== false) {
			return json_decode($response, true, 100, $jsonFlags);
		}
		
		return null;
	}
	
	protected function prepareExceptionMessage(
	    string $message, 
	    string|bool $response, 
	    ?array $postParams = null,
    ): string 
    {
	// response
		if (is_array($response)) {
			$response = json_encode($response);
		}
		
		$message .= ' ### Response: '. $response;
		
	// post params
		if ($postParams !== null) {
			if (is_array($postParams)) {
				$postParams = json_encode($postParams);
			}
			
			$message .= ' ### Posted params: '. $postParams;
		}
		
		return $message;
	}
	
	protected function addResponseToExceptionMessage(string $exceptionMessage, string|bool $response): string
	{
		if (is_array($response)) {
			$exceptionMessage .= ' ### Response (encoded): "'. json_encode($response) .'".';
			
		} else {
			$exceptionMessage .= ' ### Response: "'. $response .'".';
		}
		
		return $exceptionMessage;
	}
	
	
	abstract protected function getBaseUrl(): string;
	
	protected function getAuthData(): string
	{
		return '';
	}
	
	protected function getAuthMethod(): ?string
	{
		return null;
	}
}