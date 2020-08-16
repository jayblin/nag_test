<?php

namespace Core;

require_once 'DB.php';
require_once 'API.php';

use Core\API\API;

final class Core
{

    private static Core     $_instance;
    private \Core\DB\IDB    $_dbInstance;
    private string          $_contentPath;
    /**
     * Массив классов-друзей, которым можно давать приватные свойства
     */
    private array           $_friends = [
        'Core\API\API'
    ];

    private function __construct(array $dbInit, string $contentPath)
    {
        $dbClassName = $dbInit['class'];

        $this->_dbInstance = new $dbClassName(
            $dbInit['host'],
            $dbInit['db'],
            $dbInit['user'],
            $dbInit['pass']
        );

        $this->_contentPath = $contentPath;
    }

    public function __get($key)
    {
        $backtrace = debug_backtrace();

        if (
            isset($backtrace[1]['class']) &&
            in_array($backtrace[1]['class'], $this->_friends)
        )
        {
            return $this->$key;
        }
    }

    /**
     * Создает и возвращает единственно возможный экземпляр класса Core
     *  
     * @param array $dbInit Массив с информацией для инициализации объекта, работающего с БД
     * @param string $contentPath Путь в папке с HTML-контентом сайта
     * 
     * @return Core
     */
    public static function CreateInstance(array $dbInit, string $contentPath): Core
    {
        if (! isset(Core::$_instance)) {
            Core::$_instance = new Core($dbInit, $contentPath);
        }

        return Core::$_instance;
    }

    /**
     * Возвращает единственно возможный экземпляр класса Core
     * 
     * @return Core
     */
    public static function GetInstance(): Core
    {
        return Core::$_instance;
    }

    /**
     * Обрабатывает запросы к контенту и к API
     */
    public function HandleRequest(): void
    {
        if (isset($_GET['req']))
        {
            $this->_HandleContentRequest();
            return;
        }

        if (isset($_GET['api']) && !empty($_GET['api']))
        {
            $this->_HandleAPIRequest();
            return;
        }
    }

    /**
     * Обрабатывает запрос к контенту
     */
    private function _HandleContentRequest(): void
    {
        $request = $_GET['req'] ? $_GET['req'] : 'index.html';

        $filePath = $this->_contentPath . $request;

        if (file_exists($filePath))
        {
            // global $core;
            // print_r($core);
            //render page
            include $filePath;
        }
        else
        {
            //404
        }
    }

    /**
     * Обрабатывает запрос к API
     */
    private function _HandleAPIRequest(): void
    {
        $method = $_GET['api'];

        $api = new API($this);

        $api->CallMethod($_GET['api']);
    }
}
