<?php
class CPageCache {

	const CACHE_TIME = 600;
	const CACHE_DIR = "/cache/";
	
	public static function getCss($arCss, $isInternal) {
		$cachedFilePath = self::getCacheKey($arCss);
		if (self::checkCache($cachedFilePath)) {
			return '<style type="text/css">' . file_get_contents($_SERVER["DOCUMENT_ROOT"] . self::CACHE_DIR . $cachedFilePath) . '</style>';
		} else {
			$file = '';
			foreach ($arCss as $cssFile) {
				$file .= file_get_contents($cssFile);
			}
			$file = self::minify($file);
			file_put_contents($_SERVER["DOCUMENT_ROOT"] . self::CACHE_DIR . $cachedFilePath, $file);

			return '<style type="text/css">' . $file . '</style>';
		}

	}
	
	public static function getJs($arJs, $isInternal) {
		$cachedFilePath = self::getCacheKey($arJs);
		if (self::checkCache($cachedFilePath)) {
			return '<script>' . file_get_contents($_SERVER["DOCUMENT_ROOT"] . self::CACHE_DIR . $cachedFilePath) . '</script>';
		} else {
			$file = '';
			foreach ($arJs as $jsFile) {
				$file .= file_get_contents($jsFile);
			}
			$file = self::minify($file);
			file_put_contents($_SERVER["DOCUMENT_ROOT"] . self::CACHE_DIR . $cachedFilePath, $file);

			return '<script>' . $file . '</script>';
		}

	}

	private static function minify($css) {
		$css = preg_replace('#\s+#', ' ', $css);
		$css = preg_replace('#/\*.*?\*/#s', '', $css);
		$css = str_replace('; ', ';', $css);
		$css = str_replace(': ', ':', $css);
		$css = str_replace(' {', '{', $css);
		$css = str_replace('{ ', '{', $css);
		$css = str_replace(', ', ',', $css);
		$css = str_replace('} ', '}', $css);
		$css = str_replace(';}', '}', $css);

		return trim($css);
	}

	private static function getCacheKey($arFiles) {
		$filePath = '';
		foreach ($arFiles as $file) {
			$filePath .= md5($file);
		}
		return $filePath;
	}

	// Checks iscache validity of the cache
	private static function checkCache($file) {
		if (is_file($_SERVER["DOCUMENT_ROOT"] . self::CACHE_DIR . $file)) {
			return true;
		} else {
			return false;
		}
	}

}
?>