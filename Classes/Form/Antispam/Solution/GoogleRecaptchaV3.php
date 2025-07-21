<?php

namespace Erigo\ErigoBase\Form\Antispam\Solution;

use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\{Error, Result};
use Erigo\ErigoBase\Form\Antispam\AbstractAntispamSolution;
use Erigo\ErigoBase\ViewHelpers\Form\AntispamProtectionViewHelper;

class GoogleRecaptchaV3 extends AbstractAntispamSolution
{
    /**
     * @see \Erigo\ErigoBase\Form\Antispam\AntispamSolutionInterface::isValid()
     */
    public function isValid(): bool
    {
        return !empty($this->options['siteKey']) && !empty($this->options['secretKey']);
    }
    
    /**
     * @see \Erigo\ErigoBase\Form\Antispam\AbstractAntispamSolution::prepareProtectionField()
     */
    public function prepareProtectionField(
        AntispamProtectionViewHelper $protectionField,
        AssetCollector $assetCollector
        ): void {
            // Z konfigu si přečteme SiteKey
            $siteKey = $this->options['siteKey'] ?? '';
            // Volitelně si vezmeme 'action' (pokud není, dáme 'submit')
            $action = $this->options['action'] ?? 'submit';
            
            // 1) Vložíme do HEAD reCAPTCHA v3 script s "render=siteKey"
            $assetCollector->addJavaScript(
                'antispam_google_recaptcha_v3_api',
                'https://www.google.com/recaptcha/api.js?render=' . $siteKey
                );
            
            $inlineScript = <<<JS
grecaptcha.ready(function() {
    grecaptcha.execute('{$siteKey}', {action: '{$action}'}).then(function(token) {
        var el = document.getElementById('g-recaptcha-response');
        if (el) {
            el.value = token;
        }
    });
});
JS;
            
            $assetCollector->addInlineJavaScript('antispam_google_recaptcha_v3_inline', $inlineScript);
    }
    
    /**
     * @see \Erigo\ErigoBase\Form\Antispam\AntispamSolutionInterface::validateProtectionValue()
     */
    public function validateProtectionValue(string $protectionFieldValue, string $protectionFieldName): Result
    {
                
        $result = GeneralUtility::makeInstance(Result::class);
        
        if (!$this->verifyResponse($protectionFieldValue)) {
            $result->forProperty($protectionFieldName)->addError(
                GeneralUtility::makeInstance(
                    Error::class,
                    'Invalid reCAPTCHA response.',
                    1587659364
                    )
                );
        }
        
        return $result;
    }
    
    protected function verifyResponse(string $response): bool
    {

        $secretKey = $this->options['secretKey'] ?? '';
        $action = $this->options['action'] ?? 'submit';

        $threshold = $this->options['threshold'] ?? 0.5;
        
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $params = [
            'secret'   => $secretKey,
            'response' => $response,
            'remoteip' => GeneralUtility::getIndpEnv('REMOTE_ADDR'),
        ];
        
        $rawResponse = GeneralUtility::getUrl(
            $verifyUrl . '?' . http_build_query($params)
            );
        
        if (!$rawResponse) {
            return false;
        }
        
        $resultArray = json_decode($rawResponse, true);
        if (empty($resultArray['success'])) {
            return false;
        }
        
        $score  = $resultArray['score']  ?? 0;
        $rAction = $resultArray['action'] ?? '';
        

        if ($score >= $threshold && $rAction === $action) {
            return true;
        }
        
        return false;
    }
}
