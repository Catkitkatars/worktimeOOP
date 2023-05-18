<?php
$date = new DateTime("2023-05-15 19:00:00", new DateTimeZone('Europe/Minsk'));
$workTime = [
    'start' => 10,
    'finish' =>19
];
$timer = 24;


$orderDate = new OrderCounter(
    $date,
    $workTime,
    $timer
);

var_dump($orderDate->getTime());


class OrderCounter {

    public $orderDate;
    public $workTime;
    public $timer;

    private $workTimeHours;

    public function __construct($orderDate, $workTime, $timer)
    {
        $this->orderDate = $orderDate;
        $this->workTime = $workTime;
        $this->timer = $timer;
        $this->workTimeHours = $this->workTime['finish'] - $this->workTime['start'];
    }

    private function skipDays() {
        $skip = 1;
        $daysInWeek = 7;

        if($this->orderDate->format('N') == 6) {
            $skip += ($daysInWeek - $this->orderDate->format('N'));
        } 

        if($this->orderDate->format('N') == 5 && ($this->orderDate->format('H') >= $this->workTime['finish'] || $this->timer < $this->workTimeHours)) {
            $skip += ($daysInWeek - $this->orderDate->format('N'));
        }

        return $this->orderDate->add(new DateInterval('P'. $skip .'D'));
    }

    public function getTime() 
    {   
        if($this->orderDate->format('N') >= 6) {
            $this->skipDays();
            $this->orderDate->setTime($this->workTime['start'], 0, 0);
        }
 
        if($this->orderDate->format('H') >= $this->workTime['start'] && $this->orderDate->format('H') < $this->workTime['finish']) {
            $this->timer -= ($this->workTime['finish'] - $this->orderDate->format('H'));
            $this->skipDays();
        }

        if($this->orderDate->format('H') < $this->workTime['start']) {
            $this->orderDate->setTime($this->workTime['start'], 0, 0);

        }elseif($this->orderDate->format('H') >= $this->workTime['finish']) {
            $this->skipDays();
            $this->orderDate->setTime($this->workTime['start'], 0, 0);
        }
        
        while($this->timer >= $this->workTimeHours) {
            $this->timer -= $this->workTimeHours;
            $this->skipDays();
        }

        $this->orderDate->setTime(
            ($this->workTime['start'] + $this->timer),
            intval($this->orderDate->format('i')),
            intval($this->orderDate->format('s'))
        );

        return $this->orderDate;
    }
}


$cases = [
    'последний день февраля' => [['2023.02.28 01:23:28', [10, 19]], '2023.03.02 16:00:00'], // done
    'чуть-чуть не хватило' => [['2023.05.03 13:00:01', [10, 19]], '2023.05.08 10:00:01'], // done 
    'заказ в выходной' => [['2023.05.06 13:00:01', [10, 19]], '2023.05.10 16:00:00'], // done
    'ранняя пташка' => [['2023.05.15 09:00:00', [10, 19]], '2023.05.17 16:00:00'], // done
    'супер пунктуальность' => [['2023.05.15 10:00:00', [10, 19]], '2023.05.17 16:00:00'], // done
    'ошибка на единицу' => [['2023.05.15 19:00:00', [10, 19]], '2023.05.18 16:00:00'], // done
];
