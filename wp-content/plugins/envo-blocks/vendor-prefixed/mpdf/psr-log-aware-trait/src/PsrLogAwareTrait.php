<?php

namespace EnvoBlocks\Vendor\Mpdf\PsrLogAwareTrait;

use EnvoBlocks\Vendor\Psr\Log\LoggerInterface;

trait PsrLogAwareTrait 
{

	/**
	 * @var \EnvoBlocks\Vendor\Psr\Log\LoggerInterface
	 */
	protected $logger;

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
	
}
