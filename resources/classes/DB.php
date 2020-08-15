<?php

namespace Core\DB {

    /**
     * Интерфейс для легкой работы с разными СУБД (вроде??)
     */
    interface IDB {

        public function __construct(string $host, string $db, string $user, string $pass);
        public function Query(string $queryString): array;
        public function Queries(array $queryStrings): array;
        public function GetErrorList(): array;
        public function GetTablesInfo(): array;
        public function GetTableColumnInfo(string $tableName): array;
    }

    /**
     * Класс для работы с субд MySQL
     * 
     * @todo todos: 
     * - Реализовать транзакиции
     */
    class DBMYSQL implements IDB
    {
        private string      $_host;
        private string      $_db;
        private string      $_user;
        private string      $_pass;
        private ?\mysqli    $_mysqli;
        private array       $_errorList = [];
        private bool        $_preserveErrorLog = false;


        /**
         * @param string $host  Адрес хоста
         * @param string $db    Название БД
         * @param string $user  Имя пользователя
         * @param string $pass  Пароль пользователя
         */
        public function __construct(string $host, string $db, string $user, string $pass)
        {
            $this->_host =  $host;
            $this->_db =    $db;
            $this->_user =  $user;
            $this->_pass =  $pass;
        }

        public function __destruct()
        {
            $this->_CloseConnection();
        }


        /**
         * Выполняет SQL-запрос. 
         *  
         * @param string    $queryString    Строка с запросом
         * @param bool      $prepare        Использовать метод prepare() (true|false) (да|нет)
         * 
         * @return array
         */
        public function Query(string $queryString): array
        {
            
            $conn = $this->_OpenConnection();

            if (! DBMYSQL::_IsConnected($conn)) 
            {
                return [];
            }

            $result = [];

            $result = $this->_PrepareExecuteQuery($queryString);

            $this->_CacheErrors();
            $this->_CloseConnection();

            return $result;
        }


        /**
         * Выполняет несколько SQL-запросов
         * 
         * Если какой-либо запрос не был выполнен, то следующие запросы всё равно выполнятся.
         * Возвращаяет массив с результатами запросов
         * 
         * @param array $queryStrings Массив строк-запросов
         * 
         * @return array
         */
        public function Queries(array $queryStrings): array
        {
            $conn = $this->_OpenConnection();
            
            if (! DBMYSQL::_IsConnected($conn)) 
            {
                return [];
            }

            $result = [];

            $this->_preserveErrorLog = true;

            foreach ($queryStrings as $i => &$queryString)
            {
                $tmp = $this->_PrepareExecuteQuery($queryString);
                if (empty($tmp)) continue;

                $result[$i] = $tmp;
            }

            $this->_CacheErrors();
            $this->_CloseConnection();

            $this->_preserveErrorLog = false;

            return $result;
        }

        /**
         * Выдает массив ошибок
         * 
         * @return array
         */
        public function GetErrorList(): array
        {
            return $this->_errorList;
        }


        /**
         * Выдает информацию о таблицах
         * 
         * @return array
         */
        public function GetTablesInfo(): array
        {
            $db =& $this->_db;

            return $this->Query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE '$db';");
        }


        /**
         * Выдает информацию о строках таблицы
         * 
         * @param string $tableName Имя таблицы
         * 
         * @return array
         */
        public function GetTableColumnInfo(string $tableName): array
        {
            return $this->Query("SHOW FULL COLUMNS FROM $tableName");
        }


        /**
         * Подгатавливает и выполняет запрос
         * 
         * Возвращает массив
         * 
         * @param string $queryString
         * 
         * @return array
         */
        private function _PrepareExecuteQuery(string &$queryString): array
        {
            $result = [];

            $query = $this->_mysqli->prepare($queryString);
            @$tmp = $query->execute();

            @$tmp = $query->get_result();

            if ($tmp) 
            {
                $result = $tmp->fetch_all(MYSQLI_ASSOC);
            }

            if ($query->errno) {
                $this->_CacheErrors($query->error_list);
            }

            return $result;
        }

        /**
         * Открывает соединение и выдает его
         * 
         * @return ?mysqli
         */
        private function _OpenConnection(): ?\mysqli
        {
            @$conn = new \mysqli(
                $this->_host,
                $this->_user,
                $this->_pass,
                $this->_db
            );

            $this->_mysqli = $conn;

            if ($this->_preserveErrorLog === false) 
            {
                $this->_errorList = [];
            }

            return $this->_mysqli;
        }


        /**
         * Закрывает соединение
         * 
         * @return void
         */
        private function _CloseConnection(): void
        {
            if (
                DBMYSQL::_IsConnected($this->_mysqli)
                && !($this->_mysqli->connect_errno)
            ) {
                $this->_mysqli->close();
                unset($this->_mysqli);
            }
        }


        /**
         * Кэширует ошибки
         * 
         * @param array $miscErrors Массив доп. ошибок
         * 
         * @return void
         */
        private function _CacheErrors(array $miscErrors = null): void
        {
            $conn = $this->_mysqli;

            if ($conn->connect_errno) 
            {
                $this->_errorList[] =  [
                    'errno' => $conn->connect_errno,
                    'error' => $conn->connect_error
                ];
                return;
            }

            foreach ($conn->error_list as $i => $error) 
            {
                $this->_errorList[] = $error;
            }

            if ($miscErrors) 
            {
                foreach ($miscErrors as $i => $error) 
                {
                    $this->_errorList[] = $error;
                }
            }
        }


        /**
         * Проверяет, существует ли подключение к БД
         * 
         * @return bool
         */
        private static function _IsConnected(?\mysqli &$mysqli): bool
        {
            return isset($mysqli);
        }
    }

}
