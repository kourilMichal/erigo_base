<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\StaticValueMapper;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;

class MonthsMapper extends StaticValueMapper
{
	use SiteLanguageAwareTrait;
	
	/**
	 * @see \TYPO3\CMS\Core\Routing\Aspect\StaticValueMapper::__construct()
	 */
	public function __construct(array $settings)
	{
		if (!array_key_exists('map', $settings)) {
			$settings['map'] = [
				'january' => 1,
				'february' => 2,
				'march' => 3,
				'april' => 4,
				'may' => 5,
				'june' => 6,
				'july' => 7,
				'august' => 8,
				'september' => 9,
				'october' => 10,
				'november' => 11,
				'december' => 12,
			];
		}
		
		if (!array_key_exists('localeMap', $settings)) {
			$settings['localeMap'] = [
				[
					'locale' => 'cs_.*',
					'map' => [
						'leden' => 1,
						'unor' => 2,
						'brezen' => 3,
						'duben' => 4,
						'kveten' => 5,
						'cerven' => 6,
						'cervenec' => 7,
						'srpen' => 8,
						'zari' => 9,
						'rijen' => 10,
						'listopad' => 11,
						'prosinec' => 12,
					],
				],
			];
		}
		
	    parent::__construct($settings);
	}
}