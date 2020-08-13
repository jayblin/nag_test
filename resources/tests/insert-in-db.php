<?php

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


$sqlReadyNames = array_reduce(
    makeRandomNames(12),
    function($accumulator, $item) 
    {
        $item = trim($item);
        return "$accumulator,('$item')";
    }
);


$sqlReadyNames = substr($sqlReadyNames, 1);

$conn = new mysqli('localhost', 'root', 'r0O7m!m8tS', 'nag_test');

$query = "INSERT INTO `clients` (`NAME`) VALUES $sqlReadyNames;";

// if (! $result = $conn->query($query)) {
//     echo $conn->error;
// }
// else {
//     echo 'yah';
// }

$conn->close();