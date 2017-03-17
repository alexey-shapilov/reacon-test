<?php
    /**
     * Created by PhpStorm.
     * User: alexus
     * Date: 16.03.2017
     * Time: 12:52
     */

    require 'vendor/autoload.php';
    $config = require 'config.php';

    $app = new \core\Application($config);
    $app->generateProducts(true);

    $totalCondition = new \core\conditions\TotalCondition();

    $totalCondition->setConditions([
                                       'minCount' => 2500,
                                       'maxCount' => 3000,
                                       'minSum' => 2600000,
                                       'maxSum' => 3000000,
                                       'maxPrice' => 10000,
                                   ]);

    $app->setConditions($totalCondition);
    $app->generateOrder();
