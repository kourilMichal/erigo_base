<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\LocaleModifier;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;

class FilterMapper extends LocaleModifier
{
	use SiteLanguageAwareTrait;
	
	/**
	 * @see \TYPO3\CMS\Core\Routing\Aspect\LocaleModifier::__construct()
	 */
	public function __construct(array $settings)
	{
		if (!array_key_exists('default', $settings)) {
			$settings['default'] = 'filter';
		}
		
		if (!array_key_exists('localeMap', $settings)) {
			$settings['localeMap'] = [
				[
					'locale' => 'cs_.*',
					'value' => 'filtr',
				],
				[
					'locale' => 'sk_.*',
					'value' => 'filter',
				],
				[
					'locale' => 'en_.*',
					'value' => 'filter',
				],
				[
					'locale' => 'de_.*',
					'value' => 'filter',
				],
			];
		}
		
		parent::__construct($settings);
	}
}