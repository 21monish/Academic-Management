<?php

return [
    'Super Admin' => ['*'],
    'Principal' => [
        'dashboard',
        'academic.*',
        'staff.*',
        'students.*',
        'attendance.*',
        'exams.*',
        'fees.*',
        'leave.*',
        'notices.*',
    ],
    'HOD' => [
        'dashboard',
        'staff.*',
        'students.*',
        'attendance.*',
        'exams.*',
        'leave.*',
        'notices.*',
    ],
    'Teaching Staff' => [
        'dashboard',
        'attendance.*',
        'exams.*',
        'leave.*',
        'notices.*',
    ],
    'Non-Teaching Staff' => [
        'dashboard',
        'students.*',
        'fees.*',
        'leave.*',
        'notices.*',
    ],
    'Accountant' => [
        'dashboard',
        'fees.*',
        'notices.*',
    ],
    'Student' => [
        'dashboard',
        'notice.view',
    ],
];
