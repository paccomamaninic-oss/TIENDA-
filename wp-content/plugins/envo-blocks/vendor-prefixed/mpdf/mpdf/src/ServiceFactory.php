<?php

namespace EnvoBlocks\Vendor\Mpdf;

use EnvoBlocks\Vendor\Mpdf\Color\ColorConverter;
use EnvoBlocks\Vendor\Mpdf\Color\ColorModeConverter;
use EnvoBlocks\Vendor\Mpdf\Color\ColorSpaceRestrictor;
use EnvoBlocks\Vendor\Mpdf\Css\BorderMerger;
use EnvoBlocks\Vendor\Mpdf\Css\CssMerger;
use EnvoBlocks\Vendor\Mpdf\Css\CssParser;
use EnvoBlocks\Vendor\Mpdf\Css\InlinePropertyConverter;
use EnvoBlocks\Vendor\Mpdf\Css\InlineStyleParser;
use EnvoBlocks\Vendor\Mpdf\Css\NormalizeProperties;
use EnvoBlocks\Vendor\Mpdf\Css\SelectorParser;
use EnvoBlocks\Vendor\Mpdf\Css\ShadowParser;
use EnvoBlocks\Vendor\Mpdf\File\LocalContentLoader;
use EnvoBlocks\Vendor\Mpdf\Fonts\FontCache;
use EnvoBlocks\Vendor\Mpdf\Fonts\FontFileFinder;
use EnvoBlocks\Vendor\Mpdf\Http\CurlHttpClient;
use EnvoBlocks\Vendor\Mpdf\Http\SocketHttpClient;
use EnvoBlocks\Vendor\Mpdf\Image\ImageProcessor;
use EnvoBlocks\Vendor\Mpdf\Pdf\Protection;
use EnvoBlocks\Vendor\Mpdf\Pdf\Protection\UniqidGenerator;
use EnvoBlocks\Vendor\Mpdf\Writer\BaseWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\BackgroundWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\ColorWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\BookmarkWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\FontWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\FormWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\ImageWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\JavaScriptWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\MetadataWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\OptionalContentWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\PageWriter;
use EnvoBlocks\Vendor\Mpdf\Writer\ResourceWriter;
use EnvoBlocks\Vendor\Psr\Log\LoggerInterface;

class ServiceFactory
{

	/**
	 * @var \EnvoBlocks\Vendor\Mpdf\Container\ContainerInterface|null
	 */
	private $container;

	public function __construct($container = null)
	{
		$this->container = $container;
	}

	public function getServices(
		Mpdf $mpdf,
		LoggerInterface $logger,
		$config,
		$languageToFont,
		$scriptToLanguage,
		$fontDescriptor,
		$bmp,
		$directWrite,
		$wmf
	) {
		$sizeConverter = new SizeConverter($mpdf->dpi, $mpdf->default_font_size, $mpdf, $logger);

		$colorModeConverter = new ColorModeConverter();
		$colorSpaceRestrictor = new ColorSpaceRestrictor(
			$mpdf,
			$colorModeConverter
		);
		$colorConverter = new ColorConverter($mpdf, $colorModeConverter, $colorSpaceRestrictor);

		$tableOfContents = new TableOfContents($mpdf, $sizeConverter);

		$cacheBasePath = $config['tempDir'] . '/mpdf';

		$cache = new Cache($cacheBasePath, $config['cacheCleanupInterval']);
		$fontCache = new FontCache(new Cache($cacheBasePath . '/ttfontdata', $config['cacheCleanupInterval']));

		$fontFileFinder = new FontFileFinder($config['fontDir']);

		if ($this->container && $this->container->has('httpClient')) {
			$httpClient = $this->container->get('httpClient');
		} elseif (\function_exists('curl_init')) {
			$httpClient = new CurlHttpClient($mpdf, $logger);
		} else {
			$httpClient = new SocketHttpClient($logger);
		}

		$localContentLoader = $this->container && $this->container->has('localContentLoader')
			? $this->container->get('localContentLoader')
			: new LocalContentLoader();

		$assetFetcher = $this->container && $this->container->has('assetFetcher')
			? $this->container->get('assetFetcher')
			: new AssetFetcher($mpdf, $localContentLoader, $httpClient, $logger);

		$normalizeProperties = new NormalizeProperties($mpdf, $sizeConverter, $colorConverter);
		$selectorParser = new SelectorParser($mpdf);
		$inlineStyleParser = new InlineStyleParser($normalizeProperties);
		$inlinePropertyConverter = new InlinePropertyConverter($colorConverter);
		$borderMerger = new BorderMerger();

		$cssParser = new CssParser($mpdf, $cache, $sizeConverter, $colorConverter, $assetFetcher);

		$cssMerger = new CssMerger(
			$mpdf,
			$normalizeProperties,
			$inlineStyleParser,
			$selectorParser,
			$inlinePropertyConverter,
			$colorConverter,
			$borderMerger
		);

		$cssManager = new CssManager($cssParser, $cssMerger);

		$otl = new Otl($mpdf, $fontCache);

		$protection = new Protection(new UniqidGenerator());

		$writer = new BaseWriter($mpdf, $protection);

		$gradient = new Gradient($mpdf, $sizeConverter, $colorConverter, $writer);

		$formWriter = new FormWriter($mpdf, $writer);

		$form = new Form($mpdf, $otl, $colorConverter, $writer, $formWriter);

		$hyphenator = new Hyphenator($mpdf);

		$imageProcessor = new ImageProcessor(
			$mpdf,
			$otl,
			$cssManager,
			$sizeConverter,
			$colorConverter,
			$colorModeConverter,
			$cache,
			$languageToFont,
			$scriptToLanguage,
			$assetFetcher,
			$logger
		);

		$tag = new Tag(
			$mpdf,
			$cache,
			$cssManager,
			$form,
			$otl,
			$tableOfContents,
			$sizeConverter,
			$colorConverter,
			$imageProcessor,
			$languageToFont
		);

		$fontWriter = new FontWriter($mpdf, $writer, $fontCache, $fontDescriptor);
		$metadataWriter = new MetadataWriter($mpdf, $writer, $form, $protection, $logger);
		$imageWriter = new ImageWriter($mpdf, $writer);
		$pageWriter = new PageWriter($mpdf, $form, $writer, $metadataWriter);
		$bookmarkWriter = new BookmarkWriter($mpdf, $writer);
		$optionalContentWriter = new OptionalContentWriter($mpdf, $writer);
		$colorWriter = new ColorWriter($mpdf, $writer);
		$backgroundWriter = new BackgroundWriter($mpdf, $writer);
		$javaScriptWriter = new JavaScriptWriter($mpdf, $writer);

		$resourceWriter = new ResourceWriter(
			$mpdf,
			$writer,
			$colorWriter,
			$fontWriter,
			$imageWriter,
			$formWriter,
			$optionalContentWriter,
			$backgroundWriter,
			$bookmarkWriter,
			$metadataWriter,
			$javaScriptWriter,
			$logger
		);

		return [
			'otl' => $otl,
			'bmp' => $bmp,
			'cache' => $cache,
			'cssManager' => $cssManager,
			'directWrite' => $directWrite,
			'fontCache' => $fontCache,
			'fontFileFinder' => $fontFileFinder,
			'form' => $form,
			'gradient' => $gradient,
			'tableOfContents' => $tableOfContents,
			'tag' => $tag,
			'wmf' => $wmf,
			'sizeConverter' => $sizeConverter,
			'colorConverter' => $colorConverter,
			'hyphenator' => $hyphenator,
			'localContentLoader' => $localContentLoader,
			'httpClient' => $httpClient,
			'assetFetcher' => $assetFetcher,
			'imageProcessor' => $imageProcessor,
			'protection' => $protection,

			'languageToFont' => $languageToFont,
			'scriptToLanguage' => $scriptToLanguage,

			'writer' => $writer,
			'fontWriter' => $fontWriter,
			'metadataWriter' => $metadataWriter,
			'imageWriter' => $imageWriter,
			'formWriter' => $formWriter,
			'pageWriter' => $pageWriter,
			'bookmarkWriter' => $bookmarkWriter,
			'optionalContentWriter' => $optionalContentWriter,
			'colorWriter' => $colorWriter,
			'backgroundWriter' => $backgroundWriter,
			'javaScriptWriter' => $javaScriptWriter,
			'resourceWriter' => $resourceWriter
		];
	}

	public function getServiceIds()
	{
		return [
			'otl',
			'bmp',
			'cache',
			'cssManager',
			'directWrite',
			'fontCache',
			'fontFileFinder',
			'form',
			'gradient',
			'tableOfContents',
			'tag',
			'wmf',
			'sizeConverter',
			'colorConverter',
			'hyphenator',
			'localContentLoader',
			'httpClient',
			'assetFetcher',
			'imageProcessor',
			'protection',
			'languageToFont',
			'scriptToLanguage',
			'writer',
			'fontWriter',
			'metadataWriter',
			'imageWriter',
			'formWriter',
			'pageWriter',
			'bookmarkWriter',
			'optionalContentWriter',
			'colorWriter',
			'backgroundWriter',
			'javaScriptWriter',
			'resourceWriter',
		];
	}

}
