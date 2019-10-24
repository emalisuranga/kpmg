<?php
return [
	'format'           => 'A4', // See https://mpdf.github.io/paging/page-size-orientation.html
	'author'           => 'John Doe',
	'subject'          => 'This Document will explain the whole universe.',
	'keywords'         => 'PDF, Laravel, Package, Peace', // Separate values with comma
	'creator'          => 'Laravel Pdf',
	'display_mode'     => 'fullpage',
	'font_path' => base_path('public/fonts/'),
	'font_data' => [
		'iskpota' => [
			'R'  => 'iskpota.ttf',    // regular font
			'B'  => 'iskpota.ttf',       // optional: bold font
			'I'  => 'iskpota.ttf',     // optional: italic font
			'BI' => 'iskpota.ttf', // optional: bold-italic font
			'useOTL' => 0xFF,    // required for complicated langs like Persian, Arabic and Chinese
			'useKashida' => 75,  // required for complicated langs like Persian, Arabic and Chinese
        ],
        'latha' => [
			'R'  => 'latha.ttf',    // regular font
			'B'  => 'latha.ttf',       // optional: bold font
			'I'  => 'latha.ttf',     // optional: italic font
			'BI' => 'latha.ttf', // optional: bold-italic font
			'useOTL' => 0xFF,    // required for complicated langs like Persian, Arabic and Chinese
			'useKashida' => 75,  // required for complicated langs like Persian, Arabic and Chinese
		],
		'font_data' => [
			'examplefont' => [
				'R'  => 'kaputaunicode.ttf',    // regular font
				'B'  => 'kaputaunicode.ttf',       // optional: bold font
				'I'  => 'kaputaunicode.ttf',     // optional: italic font
				'BI' => 'kaputaunicode.ttf', // optional: bold-italic font
				'useOTL' => 0xFF,    // required for complicated langs like Persian, Arabic and Chinese
				'useKashida' => 75,  // required for complicated langs like Persian, Arabic and Chinese
			]
			// ...add as many as you want.
			],
			'Segoe UI' => [
				'R'  => 'Segoe UI.ttf',    // regular font
				'B'  => 'Segoe UI.ttf',       // optional: bold font
				'I'  => 'Segoe UI.ttf',     // optional: italic font
				'BI' => 'Segoe UI.ttf', // optional: bold-italic font
				'useOTL' => 0xFF,    // required for complicated langs like Persian, Arabic and Chinese
				'useKashida' => 75,  // required for complicated langs like Persian, Arabic and Chinese
			]
		
	]
	// ...
];