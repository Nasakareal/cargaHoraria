<?php

/* Definir los horarios disponibles por turno y día */
$horarios_disponibles = [
    'MATUTINO' => [
        'Lunes' => ['start' => '07:00:00', 'end' => '15:00:00'],
        'Martes' => ['start' => '07:00:00', 'end' => '15:00:00'],
        'Miércoles' => ['start' => '07:00:00', 'end' => '15:00:00'],
        'Jueves' => ['start' => '07:00:00', 'end' => '15:00:00'],
        'Viernes' => ['start' => '07:00:00', 'end' => '15:00:00'],
    ],
    'VESPERTINO' => [
        'Lunes' => ['start' => '12:00:00', 'end' => '20:00:00'],
        'Martes' => ['start' => '12:00:00', 'end' => '20:00:00'],
        'Miércoles' => ['start' => '12:00:00', 'end' => '20:00:00'],
        'Jueves' => ['start' => '12:00:00', 'end' => '20:00:00'],
        'Viernes' => ['start' => '12:00:00', 'end' => '20:00:00'],
    ],
    'MIXTO' => [
        'Viernes' => ['start' => '16:00:00', 'end' => '20:00:00'],
        'Sábado' => ['start' => '07:00:00', 'end' => '18:00:00']
    ],
    'ZINAPÉCUARO' => [
        'Viernes' => ['start' => '16:00:00', 'end' => '20:00:00'],
        'Sábado' => ['start' => '07:00:00', 'end' => '18:00:00']
    ]
];

$dias_semana = [
    'MATUTINO' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
    'VESPERTINO' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
    'MIXTO' => ['Viernes', 'Sábado'],
    'ZINAPÉCUARO' => ['Viernes', 'Sábado']
];