<?php

namespace Core\DB;

/*
    Пример ошибки

 Array ( 
    [0] => Array ( 
        [errno] => 1064 
        [sqlstate] => 42000 
        [error] => You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '' at line 1 
    ) 
 )
*/

/**
 * @todo todos: 
 */
class DBSQL
{
    private string      $_host;
    private string      $_db;
    private string      $_user;
    private string      $_pass;
    private ?\mysqli    $_mysqli;
    private array       $_errorList = [];
    // private bool        $_error = false;
    // private string      $errorMessage;

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
     * Выполняет SQL запрос. 
     * 
     * Вернет false если запрос не удался, и true если удался
     * 
     * @param string    $queryString    Строка с запросом
     * @param bool      $prepare        Использовать метод prepare() (true|false) (да|нет)
     * 
     * @return \mysqli_result|bool
     */
    public function Query(string $queryString, bool $prepare = true)
    {
        $conn = $this->_OpenConnection();

        $result = null;

        if (DBSQL::_IsConnected($conn)) {

            $_query = $queryString;
            if ($prepare) {
                $_query = $conn->prepare($queryString);
            }

            @$result = $conn->query($_query);

            $this->_CacheErrors();

            $this->_CloseConnection();
        }

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
        $result = [];
        $db =& $this->_db;

        if (@$tmp = $this->Query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE '$db';", false)) {
            $result = $tmp->fetch_all(MYSQLI_ASSOC);
        }

        return $result;
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
        $result = [];
        // $db =& $this->_db;

        if (@$tmp = $this->Query("SHOW FULL COLUMNS FROM $tableName", false)) {
            $result = $tmp->fetch_all(MYSQLI_ASSOC);
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

        // if (DBSQL::_IsConnected($conn) && ! $conn->connect_errno) 
        // {
        $this->_mysqli = $conn;
        // }

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
            DBSQL::_IsConnected($this->_mysqli)
            && !($this->_mysqli->connect_errno)
        ) {
            $this->_mysqli->close();
            unset($this->_mysqli);
        }
    }


    /**
     * 
     * @return void
     */
    private function _CacheErrors(): void
    {
        if (!DBSQL::_IsConnected($this->_mysqli)) {
            $this->_errorList = [];
            return;
        }

        $conn = $this->_mysqli;

        if ($conn->connect_errno) {
            $this->_errorList[] =  [
                'errno' => $conn->connect_errno,
                'error' => $conn->connect_error
            ];
            return;
        }

        foreach ($conn->error_list as $i => $error) {
            $this->_errorList[] = $error;
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
