<?php



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