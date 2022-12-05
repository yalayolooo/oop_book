<?php

///////// MARKLOGIC

abstract class Expression
{
    private static int $keycount = 0;
    private string $key;
    abstract public function interpret(InterpreterContext $context);
    public function getKey(): string
    {
        if (!isset($this->key))
        {
            self::$keycount++;
            $this->key = (string)self::$keycount;
        }
        return $this->key;
    }
}

class LiteralExpression extends Expression
{
    private mixed $value;
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }
    public function interpret(InterpreterContext $context)
    {
        $context->replace($this, $this->value);
    }
}

class InterpreterContext
{
    private array $expressionstore = [];
    public function replace(Expression $exp, mixed $value): void
    {
        $this->expressionstore[$exp->getKey()] = $value;
    }
    public function lookup(Expression $exp): mixed
    {
        return $this->expressionstore[$exp->getKey()];
    }
}

class VariableExpression extends Expression
{
    public function __construct(private string $name,
                                private mixed $val = null)
    {

    }
    public function interpret(InterpreterContext $context): void
    {
        if (!is_null($this->val))
        {
            $context->replace($this, $this->val);
            $this->val = null;
        }
    }
    public function setValue(mixed $value): void
    {
        $this->val = $value;
    }
    public function getKey(): string
    {
        return $this->name;
    }
}

abstract class OperatorExpression extends Expression
{
    public function __construct(protected Expression $l_op, protected Expression $r_op)
    {
        
    }
    public function interpret(InterpreterContext $context): void
    {
        $this->l_op->interpret($context);
        $this->r_op->interpret($context);
        $result_l = $context->lookup($this->l_op);
        $result_r = $context->lookup($this->r_op);
        $this->doInterpret($context, $result_l, $result_r);
    }
    abstract protected function doInterpret(
        InterpreterContext $context,
        $result_l,
        $result_r
    ): void;
}

class BooleanEqualsExpression extends OperatorExpression
{
    protected function doInterpret(InterpreterContext $context, $result_l, $result_r): void
    {
        $context->replace($this, $result_l == $result_r);
    }
}

class BooleanOrExpression extends OperatorExpression
{
    protected function doInterpret(InterpreterContext $context, $result_l, $result_r): void
    {
        $context->replace($this, $result_l || $result_r);
    }
}

class BooleanAndExpression extends OperatorExpression
{
    protected function doInterpret(InterpreterContext $context, $result_l, $result_r): void
    {
        $context->replace($this, $result_l && $result_r);
    }
}


// ВИКТОРИНА

abstract class Question
{
    public function __construct(protected string $prompt, protected Marker $marker)
    {
        
    }
    public function mark(string $response): bool
    {
        return $this->marker->mark($response);
    }
}

class TextQuestion extends Question
{
    // Обработка вопроса в текстовом виде
}

class AVQuestion extends Question
{
    // Обработка вопроса в мультимедийном виде
}

abstract class Marker
{
    public function __construct(protected string $test)
    {
        
    }
    abstract public function mark(string $response): bool;
}

class MarkLogicMarker extends Marker
{
    private MarkParse $engine;
    public function __construct(string $test)
    {
        parent::__construct($test);
        $this->engine = new MarkParse($test);
    }
    public function mark(string $response): bool
    {
        return $this->engine->evaluate($response);
    }
}

class MartchMarker extends Marker
{
    public function mark(string $response): bool
    {
        return ($this->text == $response);
    }
}

class RegexpMarker extends Marker
{
    public function mark(string $response): bool
    {
        return (preg_match("$this->test", $response) === 1);
    }
}

// OBSERVER LOGIN OBSERVABLE

abstract class LoginObserver implements Observer
{
    private Login $login;
    public function __construct(Login $login)
    {
        $this->login = $login;
        $login->attach($this);
    }
    public function update(Observable $observable): void
    {
        if ($observable === $this->login)
        {
            $this->doUpdate($observable);
        }
    }
    abstract public function doUpdate(Login $login): void;
}

interface Observer
{
    public function update(Observable $observable): void;
}

interface Observable
{
    public function attach(Observer $observer): void;
    public function detach(Observer $observer): void;
    public function notify(): void;
}

class Login implements \SplSubject
{
    private array $observers = [];
    public const LOGIN_USER_UNKNOWN = 1;
    public const LOGIN_WRONG_PASS = 2;
    public const LOGIN_ACCESS = 3;
    private array $status = [];
    public function attach(Observer $observer): void
    {
        $this->observers[] = $observer;
    }
    public function detach(Observer $observer): void
    {
        $this->observers = array_filter($this->observers, function($a) use($observer)
        {
            return (!($a === $observer));
        });
    }
    public function notify(): void
    {
        foreach($this->observers as $obs)
        {
            $obs->update($this);
        }
    }
    public function handleLogin(string $user, string $pass, string $ip): bool
    {
        $isvalid = false;
        switch(rand(1, 3))
        {
            case 1:
                $this->setStatus(self::LOGIN_ACCESS, $user, $ip);
                $isvalid = true;
                break;
            case 2:
                $this->setStatus(self::LOGIN_WRONG_PASS, $user, $ip);
                $isvalid = false;
                break;
            case 3:
                $this->setStatus(self::LOGIN_USER_UNKNOWN, $user, $ip);
                break;
        }

        $this->notify();
        print_r("возврат " . (($isvalid) ? "true" : "false") . "\n");
        return $isvalid;
    }
    private function setStatus(int $status, string $user, string $ip): void
    {
        $this->status = [$status, $user, $ip];
    }
    public function getStatus(): array
    {
        return $this->status;
    }

}

class LoginAnalytics implements Observer
{
    public function update(Observable $observable): void
    {
        // Небезопасно с точки зрения типов
        $status = $observable->getStatus();
        print_r(__CLASS__ . ": Обработка информации о состоянии\n");
    }
}

class SecurityMonitor extends LoginObserver
{
    public function doUpdate(Login $login): void
    {
        $status = $login->getStatus();

        if($status[0] == Login::LOGIN_WRONG_PASS)
        {
            // Отправление письма сисадмину
            print_r(__CLASS__ . ": письмо сисадмину\n");
        }
    }
}

class GeneralLogger extends LoginObserver
{
    public function doUpdate(Login $login): void
    {
        $status = $login->getStatus();
        // Добавление данных о входе в журнал
        print_r(__CLASS__ . ": добавление данных о входе в журнал\n");
    }
}

class PartnershipTool extends LoginObserver
{
    public function doUpdate(Login $login): void
    {
        $status = $login->getStatus();
        // Проверка $ip-адреса, установка cookie при соответствии списку
        print_r(__CLASS__ . ": Установка cookie при соответствии списку\n");
    }
}

// // Реализация
// $context = new InterpreterContext();
// $myvar = new VariableExpression('input', 'четыре');
// $myvar->interpret($context);
// print_r($context->lookup($myvar) . "\n");
// // Вывод: "четыре"

// $newvar = new VariableExpression('input');
// $newvar->interpret($context);
// print_r($context->lookup($newvar) . "\n");
// // Вывод: четыре

// $myvar->setValue("пять");
// $myvar->interpret($context);
// print_r($context->lookup($myvar) . "\n");
// // output: пять
// print_r($context->lookup($newvar) . "\n");
// // output: пять

$context = new InterpreterContext();
$input = new VariableExpression('input');
$statement = new BooleanOrExpression(
    new BooleanEqualsExpression($input, new LiteralExpression('четыре')),
    new BooleanEqualsExpression($input, new LiteralExpression('4'))
);

foreach (["четыре", "4", "52"] as $val)
{
    $input->setValue($val);
    print_r("$val:\n");
    $statement->interpret($context);

    if ($context->lookup($statement))
    {
        print_r("Правильный ответ!\n\n");
    }
    else 
    {
        print_r("Вы ошиблись!\n\n");
    }
}

// Реализация маркера
$markers = [
    new RegexpMarker("/п.ть/"),
    new MatchMarker("пять"),
    new MarkLogicMarker('$input equals "пять"')
];

foreach ($markers as $marker)
{
    print_r(get_class($marker) . "\n");
    $question = new TextQuestion("Сколько лучей у путяконечной звезды", $marker);

    foreach (["пять", "четыре"] as $response)
    {
        print_r(" Ответ: $response");

        if ($question->mark($response))
        {
            print_r("Верно\n");
        }
        else 
        {
            print_r("Неверное\n");
        }
    }
}


// REALIZATION LOGIN
$login = new Login();
new SecurityMonitor($login);
new GeneralLogger($login);
new PartnershipTool($login);