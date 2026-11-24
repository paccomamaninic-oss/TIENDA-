<?php

namespace EnvoBlocks\Vendor\Mpdf\Http;

use EnvoBlocks\Vendor\Psr\Http\Message\RequestInterface;

interface ClientInterface
{

	public function sendRequest(RequestInterface $request);

}
