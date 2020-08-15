<?php

namespace Test\DB;

include_once '../config.php';
include_once \Config\CORE_FILE_PATH;

use \Core\DB\DBMYSQL;

final class TestDB implements \Test\ITest
{

    public static function Perform()
    {
        $db = new DBMYSQL('localhost', 'nag_test', 'root', 'r0O7m!m8tS');

        // показать информацию о таблицах
        // $tables = $db->GetTablesInfo();
        // foreach ($tables as $i => &$table) {
        //     echo '<h1>'.$table['TABLE_NAME'].'</h1>';
        //     print_r($db->GetTableColumnInfo($table['TABLE_NAME']));
        // }


        // удалить клиентов
        // print_r($db->Query("DELETE FROM clients"));

        // вставить новых клиентов
        // $sqlReadyNames = array_reduce(
        //     makeRandomNames(12),
        //     function ($accumulator, $item) {
        //         $item = trim($item);
        //         return "$accumulator,('$item')";
        //     }
        // );
        // $sqlReadyNames = substr($sqlReadyNames, 1);
        // print_r($db->Query("INSERT INTO clients (`NAME`) VALUES $sqlReadyNames;"));


        // создать платежи
        // $result = $db->Queries([
        //     'clients' => 'SELECT ID FROM clients LIMIT 100;',
        //     'services' => 'SELECT ID, `NAME` FROM services;',
        //     'payment_types' =>'SELECT ID, `NAME` FROM payment_types;',
        // ]);
        // $clientsTotal = count($result['clients']);
        // $servicesTotal = count($result['services']);
        // $payTypesTotal = count($result['payment_types']);
        // $paymentsSql = '';
        // for ($i = 0; $i < 90; $i++)
        // {
        //     $rnd = random_int(0, $clientsTotal - 1);
        //     $rndID = $result['clients'][$rnd]['ID'];

        //     // случ. сумма платежа от -5000 до 10000 +-копейки
        //     $summ = random_int(-5000, 10000) + 10 / random_int(1, 14);
            
        //     // случайная дата платежа от "год назад" до "сегодня"
        //     $date = date('Y-m-d H:i:s', time() - random_int(0, 60*60*24*365));
            
        //     // случайная услуга
        //     $rnd = random_int(0, $servicesTotal - 1);
        //     $serviceID = $result['services'][$rnd]['ID'];

        //     // случайный тип платежа
        //     $rnd = random_int(0, $payTypesTotal - 1);
        //     $payTypeID = $result['payment_types'][$rnd]['ID'];

        //     $paymentsSql .= "($rndID,$summ,'$date',$serviceID,$payTypeID),";
        // }
        // $paymentsSql = substr($paymentsSql, 0, -1);
        // $paymentsSql = "INSERT INTO `payments` (`CLIENT_ID`,`SUMA`,`DATA`,`ACNT_ID`,`PAY_ID`) VALUES $paymentsSql;";
        // $db->Query($paymentsSql);

        echo '<h1>Errors</h1>';
        print_r($db->GetErrorList());
    }
}

$names = [
    'f' => [
        'Анастасия',
        'Дарья',
        'Валерия',
    ],
    'm' => [
        'Влад',
        'Роман',
        'Константин',
        'Джон',
        'Фёдор',
    ]
];

$patronics = [
    'm' => [
        'Петрович',
        'Алексеевич',
        'Иванов',
        'Джонов',
        'Валериевич',
        'Георгиевич',
    ],
    'f' => [
        'Петровна',
        'Алексеевна',
        'Иванова',
        'Джонова',
        'Влерьевна',
        'Георгиевна',
    ],
];

$surnames = [
    'mf' => [
        'Дядько',
        'Жмых',
        'Смит',
    ],
    'm' => [
        'Златоустов',
        'Наметов',
        'Каменцев',
        'Долинер',
        'Апронов',
    ],
    'f' => [
        'Печерина',
        'Расколкина',
        'Печатова',
        'Семенкина',
        'Уфремова',
        'Ефремова',
        'Вивальдива',
        'Чанова',
    ],
];

$genders = ['f', 'm'];


/**
 * Создает цепочку из случайных слов
 * 
 * @param string $sepatator Разделитель слов
 * @param mixed ...$lists Массивы слов
 * 
 * @return string
 */
function makeRandomWordSequence(string $sepatator, &...$lists): string
{
    $str = '';
    foreach ($lists as $i => $list)
    {
        $n = count($list) - 1;
        $rndIndex = random_int(0, $n);

        $str .= $list[$rndIndex] . $sepatator;
    }

    return $str;
}

/**
 * 
 * Создает случайное ФИО
 * 
 * @return string
 */
function makeRandomName(): string
{
    global $genders, $names, $surnames, $patronics;

    $rndGenderIdx = random_int(0, count($genders) - 1);
    $rndGender = $genders[$rndGenderIdx];

    $namesGender = array_filter(
        $names,
        function ($key) use (&$rndGender)
        {
            return (strpos($key, $rndGender) !== FALSE);
        },
        ARRAY_FILTER_USE_KEY
    );

    $surnamesGender = array_filter(
        $surnames,
        function ($key) use (&$rndGender)
        {
            return (strpos($key, $rndGender) !== FALSE);
        },
        ARRAY_FILTER_USE_KEY
    );

    $patronicsGender = array_filter(
        $patronics,
        function ($key) use (&$rndGender)
        {
            return (strpos($key, $rndGender) !== FALSE);
        },
        ARRAY_FILTER_USE_KEY
    );

    return makeRandomWordSequence(' ', $surnamesGender[$rndGender], $namesGender[$rndGender], $patronicsGender[$rndGender]);
}

/**
 * Создает массив случайных имен
 * 
 * @param $n Число имен, которые ужно создать
 * 
 * @return array[string]
 */
function makeRandomNames(int $n = 0): array
{
    $result = [];

    for ($i = 0; $i < $n; $i++)
    {
        $result[$i] = makeRandomName();
    }

    return $result;
}

TestDB::Perform();
