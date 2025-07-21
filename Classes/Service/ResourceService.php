<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Service;

use TYPO3\CMS\Core\Resource\{File, Folder, ResourceFactory, ResourceStorage};
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Erigo\ErigoBase\Domain\Model\FileReference;
use Erigo\ErigoBase\Domain\Repository\FileReferenceRepository;

class ResourceService implements SingletonInterface
{
	protected ResourceStorage $resourceStorage;
	
	public function __construct(
	    protected ResourceFactory $resourceFactory, 
	    protected FileReferenceRepository $fileReferenceRepository,
    ) {
		$this->resourceStorage = $this->resourceFactory->getDefaultStorage();
	}
	
	public function createFileReference(
	    string $filePath, 
	    string $downloadDestinationFolder = null, 
		array $fileReferenceData = [],
    ): ?FileReference 
    {
		$destinationFile = null;
		
		$resourceStorageConfig = $this->resourceStorage->getConfiguration();
		$filePath = preg_replace('@^'. $resourceStorageConfig['basePath'] .'@', '', trim($filePath, '/'));
		$isRemoteFile = preg_match('@^https?://@', $filePath);
		
		if (!$isRemoteFile && $this->resourceStorage->hasFile($filePath)) {
			$destinationFile = $this->resourceStorage->getFile($filePath);
			
		} else {
			if ($downloadDestinationFolder == null) {
				throw new \Exception('Destination folder for download is not specified.');
			}
			
			$destinationFolder = $this->getDestinationFolder(trim($downloadDestinationFolder, '/'));
			
			if ($destinationFolder instanceof Folder) {
				if ($isRemoteFile) {
					$destinationFile = $this->downloadFile($filePath, $destinationFolder);
					
				} else {
					/**
					 * @todo
					 */
				}
				
			} else {
				throw new \Exception('Destination folder for download does not exist and cannot be created.');
			}
		}
		
		if ($destinationFile instanceof File) {
			$fileReferenceData = array_merge($fileReferenceData, [
				'uid_local' => $destinationFile->getUid(),
				'uid_foreign' => uniqid('NEW_'),
				'uid' => uniqid('NEW_'),
				'crop' => null,
			]);
			
			$fileReferenceOriginalResource = $this->resourceFactory->createFileReferenceObject($fileReferenceData);
			
			$fileReference = GeneralUtility::makeInstance(FileReference::class);
			$fileReference->setOriginalResource($fileReferenceOriginalResource);
			
			return $fileReference;
		}
			
		return null;
	}
	
	public function downloadFile(string $filePath, Folder $destinationFolder): ?File
	{
		$destinationFile = null;
		$fileName = basename($filePath);
		$fileContent = file_get_contents($filePath);
		
		if ($fileContent !== false) {
			try {
				if ($destinationFolder->hasFile($fileName)) {
					$destinationFile = $this->resourceStorage->getFile(
					    trim($destinationFolder->getIdentifier(), '/') .'/'. $fileName,
				    );
						
				} else {
					$destinationFile = $destinationFolder->createFile($fileName);
				}
		
			} catch (\Exception $e) {}
		
			if ($destinationFile instanceof File) {
				if (sha1($fileContent) != $destinationFile->getSha1()) {
					$destinationFile->setContents($fileContent);
				}
			}
				
			unset($fileContent);
		}
		
		return $destinationFile;
	}

	public function getDestinationFolder(string $folderIdentifier): ?Folder
	{
		$folder = null;
		
		try {
			$folder = $this->resourceStorage->getFolder($folderIdentifier);
			
		} catch (\Exception $e) {
			$folder = $this->createFolder($folderIdentifier);
		}
		
		return $folder;
	}
	
	public function createFolder(string $folderIdentifier): ?Folder
	{
		$folderIdentifierParts = explode('/', trim($folderIdentifier, '/'));
		$folder = $this->resourceStorage->getRootLevelFolder();
		
		foreach ($folderIdentifierParts as $folderIdentifierPart) {
			try {
				if ($this->resourceStorage->hasFolderInFolder($folderIdentifierPart, $folder)) {
					$folder = $this->resourceStorage->getFolderInFolder($folderIdentifierPart, $folder);
				
				} else {
					$folder = $this->resourceStorage->createFolder($folderIdentifierPart, $folder);
				}
				
			} catch (\Exception $e) {
				$folder = null;
				break;
			}
		}
		
		return $folder;
	}
	
	public function removeFileReference(FileReference $fileReference): void
	{
		$this->fileReferenceRepository->remove($fileReference);
	}
}