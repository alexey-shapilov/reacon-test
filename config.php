<?php
    /**
     * Created by PhpStorm.
     * User: alexus
     * Date: 16.03.2017
     * Time: 12:54
     */

    return [
        'db' => [
            'dsn' => 'mongodb://127.0.0.1',
            'defaultDatabase' => 'test',
            'map' => [
                'test' => [
                    'products' => [
                        'documentClass' => '\documents\ProductDocument',
                    ]
                ]
            ]
        ],
        'generateProducts' => [
            'count' => 10000,
            'minPrice' => 1,
            'maxPrice' => 10000,
        ]
    ];