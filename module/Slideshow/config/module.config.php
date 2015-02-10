<?php

return [
    'controllers' => [
        'invokables' => [
            'slideshow-administration' => 'Slideshow\Controller\SlideshowAdministrationController'
        ]
    ],
    'router' => [
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'getText',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
                'text_domain'  => 'default'
            ]
        ]
    ],
    'view_helpers' => [
        'invokables' => [
        ]
    ]
];