<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Exception;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExceptionHandler implements SingletonInterface, LoggerAwareInterface
{
	use LoggerAwareTrait;
	
	public function logException(\Throwable $exception): void
	{
		try {
		    $applicationType = $this->getApplicationType();
		    
			$logMessage = $this->getLogMessage($exception, $applicationType);
			
			// PSR-3 Logger zápis
			if ($this->logger) {
			    $applicationMode = $this->getApplicationMode($applicationType);
			    
				$this->logger->critical($logMessage, [
					'application_mode' => $applicationMode,
					'exception' => $exception,
					'file' => $exception->getFile(),
					'line' => $exception->getLine(),
					'trace' => $exception->getTraceAsString(),
				]);
			}
			
			// Zápis do TYPO3 sys_log tabulky
			$this->writeLog($logMessage);
			
		} catch (\Throwable $e) {
		    // Fallback - pokud logování selže, nezapříčiníme další exception
		    error_log('ExceptionHandler failed: ' . $e->getMessage());
		}
	}

	/**
	 * Zápis do TYPO3 sys_log tabulky - aktualizováno pro TYPO3 v13
	 */
	protected function writeLog(string $logMessage): void
	{
		try {
		    $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_log');
		    
		    if (!$connection->isConnected()) {
			    return;
		    }
		    
		    $userId = 0;
		    $workspace = 0;
		    $data = [];
		    $backendUser = $this->getBackendUser();
		    
		    if ($backendUser instanceof BackendUserAuthentication) {
			    if (isset($backendUser->user['uid'])) {
				    $userId = (int) $backendUser->user['uid'];
			    }
			    
			    if (isset($backendUser->workspace)) {
				    $workspace = (int) $backendUser->workspace;
			    }
			    
			    if (!empty($backendUser->user['ses_backuserid'])) {
				    $data['originalUser'] = $backendUser->user['ses_backuserid'];
			    }
		    }

		    // V TYPO3 v13 používáme aktualizované hodnoty pro $GLOBALS['EXEC_TIME']
		    $currentTime = $GLOBALS['EXEC_TIME'] ?? time();

		    $connection->insert(
			    'sys_log',
			    [
				    'userid' => $userId,
				    'type' => 5,
				    'action' => 0,
				    'error' => 2,
				    'details_nr' => 0,
				    'details' => str_replace('%', '%%', $logMessage),
				    'log_data' => empty($data) ? '' : serialize($data),
				    'IP' => $this->getClientIp(),
				    'tstamp' => $currentTime,
				    'workspace' => $workspace,
			    ],
		    );
		} catch (\Throwable $e) {
		    // Fallback logging
		    error_log('Failed to write to sys_log: ' . $e->getMessage());
		}
	}

	/**
	 * Vytvoření log zprávy - aktualizováno pro TYPO3 v13
	 */
	protected function getLogMessage(\Throwable $exception, ?ApplicationType $applicationType): string
	{
		$exceptionCodeNumber = $exception->getCode() > 0 ? '#'. $exception->getCode() .': ' : '';
		
		$logMessage = $this->getLogTitle($exception) .': '. $exceptionCodeNumber . $exception->getMessage() .
			' | Exception thrown in file '. $exception->getFile() .' in line '. $exception->getLine();
		
		// Přidání URL informací pro frontend requesty
		if ($applicationType instanceof ApplicationType && $applicationType->isFrontend()) {
		    $requestUrl = $this->getRequestUrl();
		    if ($requestUrl) {
		        $logMessage .= '. Requested URL: '. $this->anonymizeToken($requestUrl);
		    }
		}
		
		return $logMessage;
	}
	
	/**
	 * Získání titulku pro log - vylepšeno pro lepší čitelnost
	 */
	protected function getLogTitle(\Throwable $exception): string
	{
		$class = get_class($exception);
		$classParts = explode('\\', $class);
		
		// Speciální handling pro Erigo extensions
		if (count($classParts) >= 4 && $classParts[0] === 'Erigo') {
			return $classParts[1] .' - '. str_replace('\\', '/', implode('\\', array_slice($classParts, 2)));
		}
		
		// Pro ostatní třídy použijeme zkrácený název
		if (count($classParts) > 2) {
		    return '...' . implode('\\', array_slice($classParts, -2));
		}
		
		return $class;
	}

	/**
	 * Získání BackendUserAuthentication - bez změn pro v13
	 */
	protected function getBackendUser(): ?BackendUserAuthentication
	{
		return $GLOBALS['BE_USER'] ?? null;
	}
	
	/**
	 * Anonymizace tokenů v URL - vylepšeno pro více typů tokenů
	 */
	protected function anonymizeToken(string $requestedUrl): string
	{
		$patterns = [
		    '/(?<=[tT]oken=)[0-9a-fA-F]{40}/' => '--AnonymizedToken--',
		    '/(?<=[aA]pi[Kk]ey=)[0-9a-zA-Z]{32,}/' => '--AnonymizedApiKey--',
		    '/(?<=[pP]assword=)[^&\s]+/' => '--AnonymizedPassword--',
		    '/(?<=[sS]ecret=)[^&\s]+/' => '--AnonymizedSecret--',
		];
		
		foreach ($patterns as $pattern => $replacement) {
		    $requestedUrl = preg_replace($pattern, $replacement, $requestedUrl);
		}
		
		return $requestedUrl;
	}
	
	/**
	 * Získání ApplicationType s fallback pro TYPO3 v13
	 */
	protected function getApplicationType(): ?ApplicationType
	{
	    try {
	        if (isset($GLOBALS['TYPO3_REQUEST']) && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface) {
		        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST']);
	        }
	    } catch (\Throwable $e) {
	        // ApplicationType::fromRequest může házet výjimky
	    }
	    
	    // Fallback detekce
	    if (defined('TYPO3_MODE') && TYPO3_MODE === 'BE') {
	        return ApplicationType::BACKEND;
	    }
	    
	    if (isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], '/typo3/')) {
	        return ApplicationType::BACKEND;
	    }
	    
	    // Default frontend
	    return ApplicationType::FRONTEND;
	}
	
	/**
	 * Získání application mode stringu
	 */
	protected function getApplicationMode(?ApplicationType $applicationType): string
	{
	    if (!$applicationType instanceof ApplicationType) {
	        return 'UNKNOWN';
	    }
	    
	    return $applicationType->isFrontend() ? 'FE' : 'BE';
	}
	
	/**
	 * Bezpečné získání client IP - aktualizováno pro TYPO3 v13
	 */
	protected function getClientIp(): string
	{
	    try {
	        return (string) GeneralUtility::getIndpEnv('REMOTE_ADDR');
	    } catch (\Throwable $e) {
	        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	    }
	}
	
	/**
	 * Bezpečné získání request URL - aktualizováno pro TYPO3 v13
	 */
	protected function getRequestUrl(): ?string
	{
	    try {
	        return GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
	    } catch (\Throwable $e) {
	        // Fallback
	        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'unknown';
	        $uri = $_SERVER['REQUEST_URI'] ?? '';
	        
	        return $protocol . '://' . $host . $uri;
	    }
	}
	
	/**
	 * Pomocná metoda pro formátování exception dat pro email reporty
	 */
	public function formatExceptionForEmail(\Throwable $exception): array
	{
	    return [
	        'class' => get_class($exception),
	        'message' => $exception->getMessage(),
	        'code' => $exception->getCode(),
	        'file' => $exception->getFile(),
	        'line' => $exception->getLine(),
	        'trace' => $exception->getTraceAsString(),
	        'timestamp' => date('Y-m-d H:i:s'),
	        'url' => $this->getRequestUrl(),
	        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
	        'application_type' => $this->getApplicationMode($this->getApplicationType()),
	    ];
	}
	
	/**
	 * Bulk logging více exceptions najednou
	 */
	public function logMultipleExceptions(array $exceptions): void
	{
	    foreach ($exceptions as $exception) {
	        if ($exception instanceof \Throwable) {
	            $this->logException($exception);
	        }
	    }
	}
}