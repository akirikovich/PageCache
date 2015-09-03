<?php
class CPageCache {

	const CACHE_TIME = 86400; // One day cache
	const CACHE_DIR = "/cache/"; // Cache folder
	
	// Get CSS
	public static function getCSS($arCSS, $type) {
		$cachedFile = self::getCacheKey($arCSS).".css"; // Get cache file name
		if (!self::validateCache($cachedFile)) { // If cache is valid
			// Create new cache file
			self::createCacheFile($cachedFile, $arCSS);
		}
		// Return file
		switch($type) {
			case "internal":
				return '<style type="text/css">'.self::getFileSource(self::getCacheFilePath($cachedFile)).'</style>';
			break;
			case "external":
				return '<link href="'.self::getCacheFilePath($cachedFile, false).'" rel="stylesheet" type="text/css">';
			break;
		}

	}

	// Get JS
	public static function getJS($arJS, $type) {
		$cachedFile = self::getCacheKey($arJS).".js"; // Get cache file name
		if (!self::validateCache($cachedFile)) { // If cache is valid
			// Create new cache file
			self::createCacheFile($cachedFile, $arJS);
		}

		// Return file
		switch($type) {
			case "internal":
				return '<script>'.self::getFileSource(self::getCacheFilePath($cachedFile)).'</script>';
			break;
			case "external":
				return '<script src="'.self::getCacheFilePath($cachedFile, false).'"></script>';
			break;
		}
	}
	
	// Create cache file
	private static function createCacheFile($filePath, $arFiles) {
		if(!$arFiles || !is_array($arFiles)) {
			throw new Exception("Bad files for cache file creation");
		}
		// Combine files
		$cacheFile = '';
		foreach ($arFiles as $fileName) {
			$cacheFile .= file_get_contents($fileName);
		}
		// minification file
		$cacheFile = self::minifyFile($cacheFile);
		
		// Save file
		file_put_contents(self::getCacheFilePath($filePath), $cacheFile);
	}

	// Get file contents
	private static function getFileSource($filePath) {
		if(!is_file($filePath)) {
			throw new Exception("Empty file");
		}
		return file_get_contents($filePath);
	}
	
	// File minification
	private static function minifyFile($fileStr) {
		if(!$fileStr) {
			throw new Exception("Empty string for minification");
		}
		$fileStr = preg_replace("#\s+#", " ", $fileStr); // Remove all block comments
		$fileStr = preg_replace("#/\*.*?\*/#s", "", $fileStr); // Remove all inline comments
		$fileStr = str_replace(
			Array(
				"; ", // Remove whitespace after ";"
				": ", // Remove whitespace after ":"
				" ;", // Remove whitespace before ";"
				" :", // Remove whitespace before ":"
				" {", // Remove whitespace before "{"
				"{ ", // Remove whitespace after "{"
				" }", // Remove whitespace before "}"
				"} ", // Remove whitespace after "}"
				", ", // Remove whitespace after ","
				" ,", // Remove whitespace before ","
			),
			Array(
				";",
				":",
				";",
				":",
				"{",
				"{",
				"}",
				"}",
				",",
				",",
			),
			$fileStr
		);
		$fileStr = trim($fileStr); // Trailing whitespaces

		return $fileStr;
	}
	
	// Cache file name generation
	private static function getCacheKey($arFiles) {
		if(!$arFiles || !is_array($arFiles)) {
			throw new Exception("Bad files for key generation");
		}
		$fileKey = '';
		foreach ($arFiles as $fileName) {
			$fileKey .= md5($fileName);
		}
		return $fileKey;
	}
	
	// Get full path to the cache file
	private static function getCacheFilePath($file, $isFull = true) {
		return ($isFull ? $_SERVER["DOCUMENT_ROOT"] : '').self::CACHE_DIR.$file;
	}

	// Cache validation
	private static function validateCache($file) {
		if(!$file) {
			throw new Exception("Empty file name");
		}
		// File existing
		if (!is_file(self::getCacheFilePath($file))) {
			return false;
		}
		// File modification time
		if((time() - filemtime(self::getCacheFilePath($file)) > self::CACHE_TIME)) {
			return false;
		}
		return true;
	}

}
?>