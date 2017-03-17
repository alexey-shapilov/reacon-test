<?php
    /**
     * Created by PhpStorm.
     * User: alexus
     * Date: 17.03.2017
     * Time: 13:07
     */

    namespace core\conditions;


    use Sokil\Mongo\Collection;

    abstract class BaseCondition implements InterfaceCondition {
        private $_conditions;
        private $_collection;

        /**
         * @return mixed
         */
        public function getConditions() {
            return $this->_conditions;
        }

        /**
         * @param mixed $conditions
         */
        public function setConditions($conditions) {
            $this->_conditions = $conditions;
        }

        /**
         * @return mixed
         */
        public function getCollection() {
            return $this->_collection;
        }

        /**
         * @param mixed $collection
         */
        public function setCollection(Collection $collection) {
            $this->_collection = $collection;
        }
    }