<?php

// Листинг 8.1

abstract class Lesson
{
    public const FIXED = 1;
    public const TIMED = 2;
    public function __construct(protected int $duration,
                                private int $costtype = 1)
    {
    
    }
    public function cost(): int
    {
        switch ($this->costtype)
        {
            case self::TIMED:
                return (5 * $this->duration);
                break;
            case self::FIXED:
                return 30;
                break;
            default:
                $this->costtype = self::FIXED;
                return 30;
        }
    }
    public function chargeType(): string
    {
        switch ($this->costtype)
        {
            case self::TIMED:
                return "Почасовая оплата";
                break;
            case self::FIXED:
                return "Фиксированная ставка";
                break;
            default: 
                $this->costtype = self::FIXED;
                return "Фиксированная ставка";
        }
    }

    // Другие методы класса
}

// Листинг 8.2
class Lecture extends Lesson
{
    // Реализации, специфичные для класса Lecture...
}

// Листинг 8.3
class Seminar extends Lesson
{
    // Реализации, специфичные для класса Seminar...
}

// Реализация
$lecture = new Lecture(5, Lesson::FIXED);
print_r("{$lecture->cost()} ({$lecture->chargeType()})\n");
$seminar = new Seminar(3, Lesson::TIMED);
print_r("{$seminar->cost()} ({$seminar->chargeType()})\n");