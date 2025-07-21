<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

use IntlDateFormatter;
use NumberFormatter;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IntlUtility
{
	const DATE_FORMAT_NONE = 'none';
	const DATE_FORMAT_SHORT = 'short';
	const DATE_FORMAT_MEDIUM = 'medium';
	const DATE_FORMAT_LONG = 'long';
	const DATE_FORMAT_FULL = 'full';
	
	
	public static function getCurrentLocale(): string
	{
		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		
		$locale = $pageRenderer->getLanguage();
		if ($locale == 'default') {
			$locale = 'en';
		}
		
		return $locale;
	}
	
	public static function formatNumber($number, array $attributes = []): string
	{
		return static::getNumberFormatter(NumberFormatter::DECIMAL, $attributes)->format($number);
	}
	
	public static function formatPercent($number, array $attributes = []): string
	{
		return static::getNumberFormatter(NumberFormatter::PERCENT, $attributes)->format($number);
	}
	
	public static function formatCurrency($number, string $currencyCode, array $attributes = []): string
	{
		return static::getNumberFormatter(NumberFormatter::CURRENCY, $attributes)->formatCurrency(
		    $number, 
		    $currencyCode,
	    );
	}
	
	protected static function getNumberFormatter(int $style, array $attributes = []): NumberFormatter
	{
		$numberFormatter = NumberFormatter::create(static::getCurrentLocale(), $style);
		
		foreach ($attributes as $attrName => $attrValue) {
			$numberFormatter->setAttribute($attrName, $attrValue);
		}
		
		return $numberFormatter;
	}
	
	public static function formatDate(
	    \DateTime|string $date, 
	    string $dateFormat = self::DATE_FORMAT_MEDIUM, 
		string $timeFormat = self::DATE_FORMAT_SHORT,
    ): string 
    {
		$dateFormatter = IntlDateFormatter::create(
			static::getCurrentLocale(), 
			static::getDateFormatConstant($dateFormat), 
			static::getDateFormatConstant($timeFormat),
		);
		
		if (is_string($date)) {
			$date = new \DateTime($date);
		}
		
		return $dateFormatter->format($date);
	}
	
	public static function getDateFormatConstant(string $constantName): ?int
	{
		$constants = [
			self::DATE_FORMAT_NONE => IntlDateFormatter::NONE,
			self::DATE_FORMAT_SHORT => IntlDateFormatter::SHORT,
			self::DATE_FORMAT_MEDIUM => IntlDateFormatter::MEDIUM,
			self::DATE_FORMAT_LONG => IntlDateFormatter::LONG,
			self::DATE_FORMAT_FULL => IntlDateFormatter::FULL,
		];
		
		if (array_key_exists($constantName, $constants)) {
			return $constants[$constantName];
		}
		
		return null;
	}
}