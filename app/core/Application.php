<?php
    /**
     * Created by PhpStorm.
     * User: alexus
     * Date: 16.03.2017
     * Time: 15:14
     */

    namespace core;


    use core\conditions\BaseCondition;
    use Sokil\Mongo\Client;

    class Application {

        private $_client;
        private $_conditions;

        public function __construct($config) {
            foreach ($config as $name => $value) {
                $this->$name = $value;
            }
        }

        public function generateProducts($db = null, $collection = 'products', $clearCollection = false) {
            if (is_bool($db)) {
                $clearCollection = $db;
                $db = null;
            }

            $defaultConfig = [
                'count' => 10000,
                'minPrice' => 1,
                'maxPrice' => 10000,
            ];
            if (isset($this->generateProducts)) {
                $config = array_merge($defaultConfig, $this->generateProducts);
            } else {
                $config = $defaultConfig;
            }
            $client = $this->getClient();
            if (isset($db)) {
                $database = $client->useDatabase($db);
            } else {
                $database = $client->getDatabase();
            }
            $products = $database->getCollection($collection);
            if ($clearCollection) {
                $products->delete();
            }
            $persistence = $client->createPersistence();

            for ($i = 1; $i <= $config['count']; $i++) {
                $persistence->persist($products->createDocument([
                    'price' => mt_rand($config['minPrice'], $config['maxPrice'])
                ]));
            }
            $persistence->flush();
            $products->createIndex(['price' => -1]);
        }

        public function saveOrder($products) {
            $orders = $this->getClient()->getDatabase()->getCollection('orders');
            $totals = 0;
            $quantity = 0;
            foreach ($products as $item) {
                $totals += $item['price'] * $item['quantity'];
                $quantity += $item['quantity'];
            }

            return $orders->createDocument([
                'totals' => $totals,
                'quantity' => $quantity,
                'products' => $products,
            ])->save();
        }

        public function generateOrder($collection = 'products') {
            $orderCollection = $this->getClient()->getDatabase()->getCollection($collection);
            $order = [];
            /** @var BaseCondition $condition */
            foreach ($this->_conditions as $condition) {
                $condition->setCollection($orderCollection);
                $order = $condition->execute();
            }

            if (!empty($order)) {
                return $this->saveOrder($order);
            }
            return null;
        }

        public function getClient() {
            if (!isset($this->_client)) {
                $dsn = isset($this->db['dsn']) ? $this->db['dsn'] : null;
                $this->_client = new Client($dsn);
                if (isset($this->db['map'])) {
                    $this->_client->map($this->db['map']);
                }
                if (isset($this->db['defaultDatabase'])) {
                    $this->_client->useDatabase($this->db['defaultDatabase']);
                }
            }
            return $this->_client;
        }

        /**
         * @param mixed $conditions
         */
        public function setConditions(BaseCondition $conditions) {
            $this->_conditions[] = $conditions;
        }
    }