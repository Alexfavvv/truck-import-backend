<?php

return [
    'show_warnings'   => false,
    'orientation'     => 'portrait',
    'defines'         => [
        // Путь к шрифтам DomPDF
        'DOMPDF_FONT_DIR'   => storage_path('fonts/'),
        'DOMPDF_FONT_CACHE' => storage_path('fonts/'),
        'DOMPDF_TEMP_DIR'   => sys_get_temp_dir(),

        // Поддержка Unicode (кириллица)
        'DOMPDF_UNICODE_ENABLED'  => true,
        'DOMPDF_ENABLE_FONTSUBSETTING' => false,

        // Разрешить загрузку внешних ресурсов (CSS, изображения)
        'DOMPDF_ENABLE_REMOTE'    => true,

        // Качество PDF
        'DOMPDF_DPI'              => 150,
        'DOMPDF_PDF_BACKEND'      => 'CPDF',

        // Шрифт по умолчанию
        'DOMPDF_DEFAULT_FONT'     => 'DejaVu Sans',

        // Медиа-тип
        'DOMPDF_DEFAULT_MEDIA_TYPE' => 'print',

        // Максимальный размер страницы
        'DOMPDF_DEFAULT_PAPER_SIZE' => 'a4',

        'DOMPDF_ENABLE_CSS_FLOAT'   => true,
        'DOMPDF_ENABLE_HTML5PARSER' => true,
    ],
];
