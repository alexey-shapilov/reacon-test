<?php
    /**
     * Created by PhpStorm.
     * User: alexus
     * Date: 17.03.2017
     * Time: 13:08
     */

    namespace core\conditions;


    class TotalCondition extends BaseCondition {

        public function execute() {
            $conditions = $this->getConditions();

            $products = $this->getCollection();
            $avg = $products->aggregate([
                                            ['$sort' => ['price' => 1]],
                                            ['$limit' => $conditions['minCount']],
                                            [
                                                '$group' => [
                                                    '_id' => null,
                                                    'sum' => ['$sum' => '$price'],
                                                    'count' => ['$sum' => 1],
                                                    'first' => ['$first' => '$price'],
                                                ]
                                            ],
                                        ]);
            if ($avg[0]['sum'] > $conditions['maxSum']) {
                $sum = $avg[0]['sum'];
                $lastSum = $avg[0]['first'];
                $decrement = 0;
                while ($sum + $lastSum * $decrement > $conditions['maxSum']) {
                    $avg = $products->aggregate([
                                                    ['$sort' => ['price' => 1]],
                                                    ['$limit' => $conditions['minCount'] - $decrement],
                                                    [
                                                        '$group' => [
                                                            '_id' => null,
                                                            'sum' => ['$sum' => '$price'],
                                                            'count' => ['$sum' => 1],
                                                        ]
                                                    ],
                                                ]);

                    $sum = $avg[0]['sum'];
                    $is = $sum + $lastSum * $decrement;
                    echo "count: {$avg[0]['count']}, sum: {$avg[0]['sum']}, is: {$is}              \r";
                    $decrement++;
                }
                echo "\n";
                $order = $products->aggregate([
                                                  ['$project' => ['_id' => 1, 'price' => 1, 'quantity' =>['$add' => 1]]],
                                                  ['$sort' => ['price' => 1]],
                                                  ['$limit' => $conditions['minCount'] - $decrement],
                                              ]);
                $lastQuantity = $order[count($order) - 1]['quantity'];
                $order[count($order) - 1]['quantity'] = $lastQuantity + $decrement;
            } else {
                $limitCount = $conditions['maxCount'];
                $maxSum = $conditions['maxPrice'];
                $prevMaxSum = $maxSum;
                $skip = 1;
                while ($limitCount > $conditions['minCount']) {
                    $prevMaxSum = $maxSum;
                    $avg = $products->aggregate([
                                                    ['$match' => ['price' => ['$lt' => $maxSum]]],
                                                    ['$sort' => ['price' => -1]],
                                                    ['$limit' => $conditions['maxCount']],
                                                    [
                                                        '$group' => [
                                                            '_id' => null,
                                                            'sum' => ['$sum' => '$price'],
                                                            'count' => ['$sum' => 1],
                                                            'first' => ['$first' => '$price'],
                                                        ],
                                                    ],
                                                ]);
                    echo "$skip: first: {$avg[0]['first']}, sum: {$avg[0]['sum']}  -  {$limitCount}               \r";
                    $limitCount = $avg[0]['count'];
                    $maxSum = $avg[0]['first'];
                    $skip++;
                    if ($avg[0]['sum'] < $conditions['maxSum']) {
                        break;
                    }
                }
                $order = $products->aggregate([
                                                  ['$project' => ['_id' => 1, 'price' => 1, 'quantity' =>['$add' => 1]]],
                                                  ['$match' => ['price' => ['$lt' => $prevMaxSum]]],
                                                  ['$sort' => ['price' => -1]],
                                                  ['$limit' => $conditions['maxCount']],
                                              ]);
            }
            echo "\n";
            return $order;
        }
    }