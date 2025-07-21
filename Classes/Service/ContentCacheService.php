<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2019 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Service;

use TYPO3\CMS\Core\SingletonInterface;

class ContentCacheService implements SingletonInterface
{
	protected array $data = [];
	
	public function add(array $data): void
	{
		$this->data[intval($data['uid'])] = $data;
	}
	
	public function get(int $uid): ?array
	{
		if (array_key_exists($uid, $this->data)) {
			return $this->data[$uid];
		}
		
		return null;
	}
}