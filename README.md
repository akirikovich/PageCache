# PHP класс для объединения и минификации CSS и JS файлов

## Возможности

* Объединение файлов
* Минификация файлов
* Хранение файлов в кэше
* Очистка кэша
* Непосредственная вставка CSS или JS кода на страницу

## Использование

#### Вставка CSS
```
		<?php
			echo CPageCache::getCSS(
				Array(
					$_SERVER["DOCUMENT_ROOT"]."/css/font-awesome.min.css",
					"http://fonts.googleapis.com/css?family=Lobster&subset=latin,cyrillic",
					"https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.3/normalize.min.css",
					"https://cdnjs.cloudflare.com/ajax/libs/hover.css/2.0.2/css/hover-min.css",
					$_SERVER["DOCUMENT_ROOT"]."/css/akirikovich.css"
				),
				"internal", // или "external", если требуется вставить как ссылку
				86400 // Время жизни кэша
			);
		?>
```

#### Вставка JS
```
		<?php
			echo CPageCache::getJS(
				Array(
					"http://code.jquery.com/jquery-1.11.3.min.js",
					$_SERVER["DOCUMENT_ROOT"]."/js/akirikovich.js"
				),
				"internal", // или "external", если требуется вставить как ссылку
				86400 // Время жизни кэша
			);
		?>
```
