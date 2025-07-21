<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2021 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Domain\Model\Interfaces;

interface SynchInterface
{
	const TYPE_IMPORT = 'import';
	const TYPE_EXPORT = 'export';

	const ACTION_JOIN = 'join';
	const ACTION_CREATE = 'create';
	const ACTION_UPDATE = 'update';
	const ACTION_DELETE = 'delete';
	const ACTION_RESTORE = 'restore';
	
	
	public function getSynchHistory(): string;
	
	public function setSynchHistory(string $synchHistory): void;

	public function getSynchHistoryItems(): array;
	
	public function addSynchHistoryItem(array $synchHistoryItem, int $limit = 0): void;
}