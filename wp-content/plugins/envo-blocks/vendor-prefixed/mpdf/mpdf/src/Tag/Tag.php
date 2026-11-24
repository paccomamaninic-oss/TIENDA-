<?php

namespace EnvoBlocks\Vendor\Mpdf\Tag;

use EnvoBlocks\Vendor\Mpdf\Strict;

use EnvoBlocks\Vendor\Mpdf\Cache;
use EnvoBlocks\Vendor\Mpdf\Color\ColorConverter;
use EnvoBlocks\Vendor\Mpdf\CssManager;
use EnvoBlocks\Vendor\Mpdf\Form;
use EnvoBlocks\Vendor\Mpdf\Image\ImageProcessor;
use EnvoBlocks\Vendor\Mpdf\Language\LanguageToFontInterface;
use EnvoBlocks\Vendor\Mpdf\Mpdf;
use EnvoBlocks\Vendor\Mpdf\Otl;
use EnvoBlocks\Vendor\Mpdf\SizeConverter;
use EnvoBlocks\Vendor\Mpdf\TableOfContents;

abstract class Tag
{

	use Strict;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\Mpdf
	 */
	protected $mpdf;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\Cache
	 */
	protected $cache;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\CssManager
	 */
	protected $cssManager;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\Form
	 */
	protected $form;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\Otl
	 */
	protected $otl;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\TableOfContents
	 */
	protected $tableOfContents;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\SizeConverter
	 */
	protected $sizeConverter;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\Color\ColorConverter
	 */
	protected $colorConverter;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\Image\ImageProcessor
	 */
	protected $imageProcessor;

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\Language\LanguageToFontInterface
	 */
	protected $languageToFont;

	const ALIGN = [
		'left' => 'L',
		'center' => 'C',
		'right' => 'R',
		'top' => 'T',
		'text-top' => 'TT',
		'middle' => 'M',
		'baseline' => 'BS',
		'bottom' => 'B',
		'text-bottom' => 'TB',
		'justify' => 'J'
	];

	public function __construct(
		Mpdf $mpdf,
		Cache $cache,
		CssManager $cssManager,
		Form $form,
		Otl $otl,
		TableOfContents $tableOfContents,
		SizeConverter $sizeConverter,
		ColorConverter $colorConverter,
		ImageProcessor $imageProcessor,
		LanguageToFontInterface $languageToFont
	) {

		$this->mpdf = $mpdf;
		$this->cache = $cache;
		$this->cssManager = $cssManager;
		$this->form = $form;
		$this->otl = $otl;
		$this->tableOfContents = $tableOfContents;
		$this->sizeConverter = $sizeConverter;
		$this->colorConverter = $colorConverter;
		$this->imageProcessor = $imageProcessor;
		$this->languageToFont = $languageToFont;
	}

	public function getTagName()
	{
		$tag = get_class($this);
		return strtoupper(str_replace('EnvoBlocks\Vendor\Mpdf\Tag\\', '', $tag));
	}

	protected function getAlign($property)
	{
		$property = strtolower($property);
		return array_key_exists($property, self::ALIGN) ? self::ALIGN[$property] : '';
	}

	abstract public function open($attr, &$ahtml, &$ihtml);

	abstract public function close(&$ahtml, &$ihtml);

}
