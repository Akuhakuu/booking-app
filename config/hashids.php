<?php

return [

    'default' => 'main',

    'connections' => [
        'main' => [
            'salt' => env('HASHIDS_SALT', 'default-salt'),
            'length' => 8, // Panjang minimal hash (sesuai kebutuhan)
        ],





        'alternative' => [
            'salt' => 'your-salt-string',
            'length' => 'your-length-integer',
            // 'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        ],

    ],

];
