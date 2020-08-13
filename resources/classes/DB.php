<?php


class DB
{
    private string  $_host;
    private string  $_db;
    private string  $_user;
    private string  $_pass;
    private mysqli  $_connection;
    private bool    $_error = false;
    private string  $errorMessage;
    
    public function __construct(string $host, string $db, string $user, string $pass)
    {
        $this->_host =  $host;
        $this->_db =    $db;
        $this->_user =  $user;
        $this->_pass =  $pass;

        
    }

    public function __destruct()
    {
        
    }

    public function OpenConnection() {
        $conn = new mysqli(
            $this->_host,
            $this->_user,
            $this->_pass,
            $this->_db
        );

    }

    public function CloseConnection() {
        
    }

    /**
     * Выдает имена таблиц базы данных
     * 
     * @return string[]
     */
    public function GetTableNames(): array
    {
        $result = [];

        return $result;
    }

    /**
     * Выдает Информацию о строках таблицы
     * 
     * @return string[]
     */
    public function GetTableColumns(): array
    {
        $result = [];

        return $result;
    }
}