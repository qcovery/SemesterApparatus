<?php

return [
    'css' => [
        'semester-apparatus.css'
    ],
    'helpers' => [
        'factories' => [
            'SemesterApparatus\View\Helper\SemesterApparatus\SemesterApparatus' => 'SemesterApparatus\View\Helper\SemesterApparatus\SemesterApparatusFactory',
        ],
        'aliases' => [
            'SemesterApparatus' => 'SemesterApparatus\View\Helper\SemesterApparatus\SemesterApparatus',
        ]
    ]
];
