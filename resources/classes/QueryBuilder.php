<?php

namespace Core\DB {

    interface IQueryBuilder
    {
        public function Aliases(array $aliases): void;
        public function Select(array $fields): void;
        public function From(string $tableName): void;
        public function Join(string $tableName, string $condition): void;
        public function Where(array $conditions): void;
        public function Group(string $columnName): void;
        public function Sort(string $columnName, string $direction): void;
        public function Build(): string;
    }

    /**
     * Класс для создания запросов
     */
    final class MYSQLQueryBuilder implements IQueryBuilder
    {
        private array   $_aliases = [];
        private string  $_selectStmt = "";
        private string  $_fromStmt = "";
        private string  $_joinStmt = "";
        private string  $_whereStmt = "";
        private string  $_sortStmt = "";
        private string  $_groupStmt = "";


        public function __construct()
        {
        }


        /**
         * Устанавливает псевдонимы для таблиц
         * 
         * @param array $aliases Ассоциативный массив "название_таблицы" => "псевдоним"
         */
        public function Aliases(array $aliases): void
        {
            $this->_aliases =& $aliases;
        }


        /**
         * Создает SELECT-выражение запроса
         * 
         * @param array $fields Ассоциативный массив "псевдоним_столбца" => "выражение_выборки"
         */
        public function Select(array $fields): void
        {
            $this->_selectStmt = "SELECT ";
            $i = 0;
            $n = count($fields);
            $sep = ',';
            foreach ($fields as $name => &$field)
            {
                if (++$i == $n) {
                    $sep = '';
                }
                $this->_selectStmt .= "$field as `$name`$sep ";
            }
        }


        /**
         * Создает FROM-выражение запроса
         * 
         * @param string $tableName Название таблицы 
         */
        public function From(string $tableName): void
        {
            $alias = $tableName;
            if (isset($this->_aliases[$tableName]))
            {
                $alias = $this->_aliases[$tableName];
            }
            $this->_fromStmt = "FROM `$tableName` as `$alias` ";
        }


        /**
         * Создает JOIN-выражение запроса
         * 
         * @param string $tableName Название таблицы 
         * @param string $condition Условие склейки
         */
        public function Join(string $tableName, string $condition): void
        {
            $alias = $tableName;
            if (isset($this->_aliases[$tableName]))
            {
                $alias = $this->_aliases[$tableName];
            }
            $this->_joinStmt .= "JOIN `$tableName` AS `$alias` ON $condition ";
        }


        /**
         * Создает WHERE-выражение запроса
         * 
         * ['cond_1', 'cond_2'] == WHERE cond_1 AND cond_2
         * ['cond_1', 'OR' => 'cond_2'] == WHERE cond_1 OR cond_2
         * @param array $conditions Массив условий, 
         */
        public function Where(array $conditions): void
        {
            foreach ($conditions as $operand => &$condition)
            {
                if (empty($this->_whereStmt))
                {
                    $this->_whereStmt = "WHERE $condition ";
                    continue;
                }

                if (!is_string($operand)) 
                {
                    $this->_whereStmt .= "AND $condition ";
                    continue;
                }

                $this->_whereStmt .= "$operand $condition ";
            }
        }


        /**
         * Создает GROUP BY-выражение запроса
         * 
         * @param string $columnName Газвание столбца, по которому группировать  
         */
        public function Group(string $columnName): void
        {
            $this->_groupStmt = "GROUP BY $columnName ";
        }


        /**
         * Создает SORT BY - выражение запроса
         * 
         * @param string $columnName Название столбца
         * @param string $direction Направление сортировки
         */
        public function Sort(string $columnName, string $direction): void
        {
            if (! $direction) {
                $direction = 'ASC';
            }
            $this->_groupStmt = "SORT BY $columnName $direction ";
        }


        /**
         * Строит и выдает SQL-запрос
         */
        public function Build(): string
        {
            return $this->_selectStmt . 
                $this->_fromStmt . 
                $this->_joinStmt . 
                $this->_whereStmt .
                $this->_sortStmt .
                $this->_groupStmt . ";";
        }
    }
}
