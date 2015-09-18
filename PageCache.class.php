<?php
class CPageCache {

	private static $CACHE_TIME = 86400; // One day cache
	const CACHE_DIR = "/cache/"; // Cache folder
	
	
	
	// Get optimized images
	public static function getImg($src, $quality = 90) {
		if(!$src) {
			throw new Exception("Empty image source");
		}
		// New picture path
		$newPath = $src;
		$fullPath = $_SERVER["DOCUMENT_ROOT"].$newPath;
		$fullPathInfo = pathinfo($fullPath);
		
		// Chech file in the cache
		$fileCachePath = self::CACHE_DIR.md5($fullPath.$quality).".".$fullPathInfo["extension"];
		$fullFileCachePath = $_SERVER["DOCUMENT_ROOT"].$fileCachePath;
		if(is_file($fullFileCachePath)) {
			return $fileCachePath;
		}
		
		// Create new cache file
		if($fullPathInfo["extension"] == "jpg" || $fullPathInfo["extension"] == "jpeg") {
			$newImg = imagecreatefromjpeg($fullPath);
			imagejpeg($newImg, $fullFileCachePath, $quality);
			return $fileCachePath;
		}
		// Return image source
		return $newPath;
	}
	
	
	
	
	// Get CSS
	public static function getCSS($arCSS, $type, $cacheTime = 86400) {
		// Cache timelife
		if($cacheTime) {
			self::$CACHE_TIME = $cacheTime;
		}
		$cachedFile = self::getCacheKey($arCSS).".css"; // Get cache file name
		if (!self::validateCache($cachedFile)) { // If cache is invalid
			// Clear old cache
			self::clearCache();
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
	public static function getJS($arJS, $type, $cacheTime = 86400) {
		// Cache timelife
		if($cacheTime) {
			self::$CACHE_TIME = $cacheTime;
		}
		$cachedFile = self::getCacheKey($arJS).".js"; // Get cache file name
		if (!self::validateCache($cachedFile)) { // If cache is valid
			// Clear old cache
			self::clearCache();
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
	
	
	
	// Clear cache
	private static function clearCache() {
		$currentTime = time();
		foreach(glob($_SERVER["DOCUMENT_ROOT"].self::CACHE_DIR."*") as $fileName) {
			// Remove file
			if($currentTime - filemtime($fileName) > self::$CACHE_TIME) {
				unlink($fileName);
			}
		}
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
			$fileModTime = (strstr($fileName, "http") === false) ? filemtime($fileName) : ''; // Modification date
			$fileKey .= md5($fileName.$fileModTime.$fileModTime);
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
		if((time() - filemtime(self::getCacheFilePath($file)) > self::$CACHE_TIME)) {
			return false;
		}
		return true;
	}
}
?>