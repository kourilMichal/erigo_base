<?php

namespace Erigo\ErigoBase\TCA;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ParamHelper
{
    public function showValueField(array $data): bool
    {
        $paramUid = (int)($data['record']['param'] ?? 0);
        if ($paramUid === 0) {
            return false;
        }
        
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
        ->getQueryBuilderForTable('sys_param');
        
        $paramRecord = $queryBuilder
        ->select('*')
        ->from('sys_param')
        ->where(
            $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($paramUid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAssociative();
            
            if (!is_array($paramRecord)) {
                return false;
            }
            
            $type = $paramRecord['type'] ?? '';
            $inputMode = $paramRecord['input_mode'] ?? '';
            
            $fieldMapping = [
                'text' => [
                    'input'    => 'value_input',
                    'textarea' => 'value_textarea',
                    'rte'      => 'value_rte',
                ],
                'number' => [
                    'integer' => 'value_integer',
                    'decimal' => 'value_decimal',
                ],
                'boolean' => 'value_boolean',
                'date'    => 'value_date',
            ];
            
            $expectedField = $fieldMapping[$type][$inputMode] ?? ($fieldMapping[$type] ?? null);
            
            return ($expectedField === $data['conditionParameters'][0]);
    }
    
    public function getValueFieldType(array &$params)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
        ->getQueryBuilderForTable('sys_param');
        
        $paramUid = (int)($params['row']['param'] ?? 0);
        
        if ($paramUid === 0) {
            return;
        }
        
        $paramRecord = $queryBuilder
        ->select('type', 'input_mode', 'title')
        ->from('sys_param')
        ->where(
            $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($paramUid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAssociative();
            
            if (!$paramRecord) {
                return;
            }
            
            $params['title'] = $paramRecord['title'];
            
            $type = $paramRecord['type'] ?? '';
            $inputMode = $paramRecord['input_mode'] ?? '';
            
            $fieldMapping = [
                'text' => [
                    'input'    => ['type' => 'input', 'eval' => 'trim'],
                    'textarea' => ['type' => 'text', 'eval' => 'trim'],
                    'rte'      => ['type' => 'text', 'enableRichtext' => true, 'eval' => 'trim'],
                ],
                'number' => [
                    'integer' => ['type' => 'input', 'eval' => 'int'],
                    'decimal' => ['type' => 'input', 'eval' => 'double2'],
                ],
                'boolean' => ['type' => 'check'],
                'date'    => ['type' => 'input', 'renderType' => 'inputDateTime', 'eval' => 'date'],
            ];
            
            $expectedField = $fieldMapping[$type][$inputMode] ?? ($fieldMapping[$type] ?? null);
            
            if ($expectedField) {
                $params['config'] = array_merge($params['config'], $expectedField);
            }
    }
    
    
    public function getValueField(array $parameters, $parentObject = null): void
    {
        $paramUid = (int)($parameters['row']['param'] ?? 0);
        if ($paramUid > 0) {
            $paramRecord = BackendUtility::getRecord('sys_param', $paramUid);
            if (is_array($paramRecord)) {
                $parameters['title'] = $paramRecord['title'];
            }
        }
    }
  
    
    public function getParamOptions(array &$params): void
    {
        $params['items'] = [];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
        ->getQueryBuilderForTable('sys_param');
        
        $existingParams = [];
        if (!empty($params['row']['params']) && is_array($params['row']['params'])) {
            foreach ($params['row']['params'] as $param) {
                if (!empty($param['param'])) {
                    $existingParams[] = (int)$param['param'];
                }
            }
        }
        
        $query = $queryBuilder->select('uid', 'title')
        ->from('sys_param')
        ->orderBy('title', 'ASC');
        
        if (!empty($existingParams)) {
            $query->where(
                $queryBuilder->expr()->notIn(
                    'uid',
                    $queryBuilder->createNamedParameter($existingParams, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                    )
                );
        }
        
        $rows = $query->execute()->fetchAllAssociative();
        
        foreach ($rows as $row) {
            $params['items'][] = [$row['title'], $row['uid']];
        }
    }
}
