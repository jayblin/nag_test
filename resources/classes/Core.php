<?php

namespace Core;

require_once 'DB.php';

class Core
{
    private \Core\DB\IDB $_dbInstance;
    private string $_contentPath;

    /**
     * @param array $dbInit Массив с информацией для инициализации объекта, работающего с БД
     * @param string $contentPath Путь в папке с HTML-контентом сайта
     */
    public function __construct(array $dbInit, $contentPath)
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

    public function HandleRequest()
    {
        $request = (isset($_GET['req']) && !empty($_GET['req'])) 
            ? $_GET['req'] 
            : 'index.html';

        $filePath = \Config\CONTENT_PATH . $request;

        if (file_exists($filePath))
        {
            //render page
            include $contentPath;
        }
        else
        {
            //404
        }
    }
}
