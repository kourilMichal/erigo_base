<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\LocaleModifier;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;

class PaginationMapper extends LocaleModifier
{
	use SiteLanguageAwareTrait;
	
	/**
	 * @see \TYPO3\CMS\Core\Routing\Aspect\LocaleModifier::__construct()
	 */
	public function __construct(array $settings)
	{
		if (!array_key_exists('default', $settings)) {
			$settings['default'] = 'page';
		}
		
		if (!array_key_exists('localeMap', $settings)) {
			$settings['localeMap'] = [
				[
					'locale' => 'cs_.*',
					'value' => 'strana',
				],
				[
					'locale' => 'sk_.*',
					'value' => 'strana',
				],
				[
					'locale' => 'en_.*',
					'value' => 'page',
				],
				[
					'locale' => 'de_.*',
					'value' => 'seite',
				],
			];
		}
		
		parent::__construct($settings);
	}
}