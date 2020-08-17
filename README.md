# Описание
Назначение папок:
* public - хранит css и js
* resources - хранит php-скрипты, и html-содержимое

Приложение начинает работу со скрипта init.php в котором вызывается метод HandleRequest() класса Core. Этот метода определяет какую страницу открыть или какой метод API выполнить.

Взаимодействие с БД реализованно с помошью класса в файле __resources/classes/DB.php__. 

Для того, чтобы клиент мог получать информацию из БД, был создан класс _API_ с выделенными методами, необходимыми для получения такой информации как:
* типы клиентов
* дата самого первого платежа 
* сводный отчет


# Стрктура БД

```sql
CREATE DATABASE `nag_test`;

/* КЛИЕНТЫ */
CREATE TABLE `clients` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `TYPE` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* ТИПЫ ПЛАТЕЖЕЙ */
CREATE TABLE `payment_types` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* ПЛАТЕЖТ */
CREATE TABLE `payments` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CLIENT_ID` int NOT NULL,
  `SUMA` float NOT NULL,
  `DATA` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ACNT_ID` int NOT NULL,
  `PAY_ID` int NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `CLIENT_ID` (`CLIENT_ID`),
  KEY `PAY_ID` (`PAY_ID`) /*!80000 INVISIBLE */,
  KEY `payments_ibfk_2_idx` (`ACNT_ID`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`CLIENT_ID`) REFERENCES `clients` (`ID`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`ACNT_ID`) REFERENCES `services` (`ID`),
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`PAY_ID`) REFERENCES `payment_types` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* УСЛУГИ */
CREATE TABLE `services` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```


# Сводный отчет

Отчет формируется по примеру запроса ниже:
```sql
SELECT 
    s.`NAME` AS `Услуга`,
    ROUND(SUM(CASE
                WHEN p.`DATA` < '2020.06.01' THEN p.SUMA
                ELSE 0
            END),
            2) AS `Баланс на начало периода`,
    ROUND(SUM(CASE
                WHEN (p.SUMA >= 0 AND p.`DATA` >= '2020.06.01') THEN p.SUMA
                ELSE 0
            END),
            2) AS `Приход`,
    ABS(ROUND(SUM(CASE
                        WHEN (p.SUMA < 0 AND p.`DATA` >= '2020.06.01') THEN p.SUMA
                        ELSE 0
                    END),
                    2)) AS `Расход`,
    ROUND(SUM(CASE
                WHEN
                    (p.PAY_ID = 4
                        AND p.`DATA` >= '2020.06.01')
                THEN
                    p.SUMA
                ELSE 0
            END),
            2) AS `Перерасчет`,
    ROUND(SUM(p.SUMA), 2) AS `Итого`
FROM
    payments AS p
        JOIN
    services AS s ON p.ACNT_ID = s.ID
        JOIN
    clients AS c ON p.CLIENT_ID = c.ID
WHERE
    p.`DATA` < '2020.07.1' AND c.TYPE = 1
GROUP BY s.`NAME`
ORDER BY s.`NAME` ASC;
```


# Индексация таблицы 'payments'

У таблицы имеется 3 FOREIGN-ключа, которые связывают ее с таблицами clients, services и payment_types. Установка других индексов никак не оптимизировала запрос для формирования сводного отчета. В т.ч. индексация поля `payments.DATA`.