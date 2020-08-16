<?php

namespace Core\API
{

    use \Core\Core;
    use \Core\DB\IDB;

    final class API
    {
        private Core $_core;

        /**
         * @param $coreInstance Объект класса Core
         */
        public function __construct(Core $coreInstance)
        {
            $this->_core = $coreInstance;
            // $this->_db = $coreInstance->_dbInstance;

        }

        public function CallMethod(string $methodName)
        {
            if (!$this->_core)
            {
                API::EchoError('Core is not instantiated');
                return;
            }

            if (! method_exists($this, $methodName))
            {
                API::EchoError("No such method \"$methodName\"");
                return;
            }

            $this->$methodName();
        }

        /**
         * Выдает дату самого первого платежа
         */
        public function GetFirstPaymentDate(): void
        {
            /**
             * @var IDB $db
             */
            $db = $this->_core->_dbInstance;

            if (!$db)
            {
                API::EchoError("No DB Instance");
                return;
            }

            $minDate = $db->Query('SELECT min(p.`DATA` ) as `min` FROM payments as p;');

            API::Echo($minDate[0]['min']);
        }


        /**
         * Выдает виды клиентов
         */
        public function GetClientTypes(): void
        {
            /**
             * @var IDB $db
             */
            $db = $this->_core->_dbInstance;

            if (!$db)
            {
                API::EchoError("No DB Instance");
                return;
            }

            API::Echo([
                1 => 'Физ. лицо',
                2 => 'Юр. лицо',
            ]);
        }

        
        /**
         * Выдает сводный отчет, который будет характеризовать движение денежных средств предприятия
         * 
         * Запрос типа-POST
         * Параметры:
         * int|string   time        - временная метка PHP (до секунд)
         * int|string   ?clientType - ID типа клиента (необязательный)
         * 
         */
        public function GetReport(): void
        {
            /**
             * @var IDB $db
             */
            $db = $this->_core->_dbInstance;

            if (! $db)
            {
                API::EchoError("No DB Instance");
                return;
            }

            $rest_json = file_get_contents("php://input");
            $_POST = json_decode($rest_json, true);

            $time = intval($_POST['date']);
            $timeNext = strtotime('first day of next month', $time);

            $date = date('Y.m.d', $time);
            $dateNext = date('Y.m.d', $timeNext);

            if (isset($_POST['clientType']) && ! empty($_POST['clientType']))
            {
                $clientType = intval($_POST['clientType']);
            }

            $builder = $db->GetQueryBuilder();

            $builder->Aliases([
                'payments' => 'p',
                'clients' => 'c',
                'services' => 's',
            ]);
            $builder->Select([
                'serviceName' => 's.`Name`',
                'startBalance' => "round(sum(case when p.`DATA` < '$date' then p.SUMA else 0 end), 2)",
                'gain' => "round(sum(case when (p.SUMA >= 0 and p.`DATA` >= '$date') then p.SUMA else 0 end), 2)",
                'loss' => "abs(round(sum(case when (p.SUMA < 0 and p.`DATA` >= '$date') then p.SUMA else 0 end), 2))",
                'recalc' => "round(sum(case when (p.PAY_ID = 4 and p.`DATA` >= '$date') then p.SUMA else 0 end), 2)",
                'totalBalance' => "round(sum(p.SUMA), 2)",
            ]);
            $builder->From('payments');
            $builder->Join('services', 's.ID = p.ACNT_ID');
            $builder->Where(["p.`DATA` < '$dateNext'"]);
            if (isset($clientType))
            {
                $builder->Join('clients', 'c.ID = p.CLIENT_ID');
                $builder->Where(["c.TYPE = $clientType"]);
            }
            $builder->Sort('s.`Name`', 'ASC');
            $builder->Group('s.`NAME`');

            $report = $db->Query($builder->Build());

            $errors = $db->GetErrorList();

            if (count($errors) > 0) 
            {
                API::EchoError('Internal DB error');
            }

            API::Echo($report);
        }


        /**
         * Выводит результат запроса в формате json
         * 
         * @param mixed $result Результат запроса
         */
        public static function Echo($result): void
        {
            echo json_encode([
                'error' => false,
                'result' => $result
            ]);
        }

        /**
         * Выводит оштбку в формате json
         * 
         * @param mixed $errorMessage
         * @param mixed $errorCode
         */
        public static function EchoError($errorMessage, int $errorCode = -1): void
        {
            echo json_encode([
                'error' => true,
                'message' => $errorMessage,
                'code' => $errorCode,
            ]);
        }
    }
}
