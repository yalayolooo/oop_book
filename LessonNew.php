<?php

// Листинг 8.5

use Lesson as GlobalLesson;

abstract class Lesson
{
    public function __construct(private int $duration,
                                private CostStrategy $costStrategy)
    {

    }
    public function cost(): int
    {
        return $this->costStrategy->cost($this);
    }
    public function chargeType(): string
    {
        return $this->costStrategy->chargeType();
    }
    public function getDuration(): int
    {
        return $this->duration;
    }

    // Другие методы
}

// Листинг 8.9
abstract class CostStrategy
{
    abstract public function cost(Lesson $lesson): int;
    abstract public function chargeType(): string;
}

class TimedCostStrategy extends CostStrategy
{
    public function cost(Lesson $lesson): int
    {
        return ($lesson->getDuration());
    }
    public function chargeType(): string
    {
        return "Почасовая оплата";
    }
}

class FixedCostStrategy extends CostStrategy
{
    public function cost(Lesson $lesson): int
    {
        return 30;
    }
    public function chargeType(): string
    {
        return "Фиксированная ставка";
    }
}

class Lecture extends Lesson
{
    // Реализация класса
}

class Seminar extends Lesson
{
    // Реализация класса
}

// $lessons[] = new Seminar(4, new TimedCostStrategy());
// $lessons[] = new Lecture(4, new FixedCostStrategy());

// foreach ($lessons as $lesson) {
//     print_r("Оплата за занятие {$lesson->cost()}. ");
//     print_r(" Тип оплаты: {$lesson->chargeType()}\n");
// }

// Листинг 8.13
class RegistrationMgr
{
    public function register(Lesson $lesson): void
    {
        // Некоторые действия с Lesson и отправка кому-нибудь сообщения
        $notifier = Notifier::getNotifier();
        $notifier->inform("new lesson: cost ({$lesson->cost()})");
    }
}

abstract class Notifier
{
    public static function getNotifier(): Notifier
    {
        // Получить конкретный класс в соответствии с кофигурацией или иной логикой
        if (rand(1, 2) === 1)
        {
            return new MailNotifier();
        }
        else
        {
            return new TextNotifier();
        }
    }
    abstract public function inform($message): void;
}

class MailNotifier extends Notifier
{
    public function inform($message): void
    {
        print_r("Уведомление почтой: {$message}\n");
    }
}

class TextNotifier extends Notifier
{
    public function inform($message): void
    {
        print_r("Уведомление текстом: {$message}\n");
    }
}

// Листинг 8.17
$lessons1 = new Seminar(4, new TimedCostStrategy());
$lessons2 = new Lecture(4, new FixedCostStrategy());
$mgr = new RegistrationMgr();
$mgr->register($lessons1);
$mgr->register($lessons2);