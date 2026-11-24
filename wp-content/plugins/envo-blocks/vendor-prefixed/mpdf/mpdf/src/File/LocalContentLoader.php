<?php

namespace EnvoBlocks\Vendor\Mpdf\File;

class LocalContentLoader implements \EnvoBlocks\Vendor\Mpdf\File\LocalContentLoaderInterface
{

	public function load($path)
	{
		return file_get_contents($path);
	}

}
