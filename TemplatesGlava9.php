<?php

// Листинг 9.1
abstract class Employee
{
    private static $types = ['Minion', 'CluedUp', 'WellConnected'];
    public static function recruit(string $name): Employee
    {
        $num = rand(1, count(self::$types)) - 1;
        $class = __NAMESPACE__ . "\\" . self::$types[$num];
        return new $class($name);
    }
    public function __construct(protected string $name)
    {
        
    }
    abstract public function fire(): void;
}

class Minion extends Employee
{
    public function fire(): void
    {
        print_r("{$this->name}: я уберу со стола\n");
    }
}

class NastyBoss
{
    private array $employees = [];
    public function addEmployee(Employee $employee): void
    {
        $this->employees[] = $employee;
    }
    public function projectFails(): void
    {
        if (count($this->employees) > 0)
        {
            $emp = array_pop($this->employees);
            $emp->fire();
        }
    }
}

class CluedUp extends Employee
{
    public function fire(): void
    {
        print_r("{$this->name}: я вызову адвоката\n");
    }
}

class WellConnected extends Employee
{
    public function fire(): void
    {
        print_r("{$this->name}: я позвоню папе\n");
    }
}

// Реализация

// $boss = new NastyBoss();
// $boss->addEmployee(new Minion("Игорь"));
// $boss->addEmployee(new CluedUp("Владимир"));
// $boss->addEmployee(new Minion("Мария"));
// $boss->projectFails();
// $boss->projectFails();
// $boss->projectFails();

$boss = new NastyBoss();
$boss->addEmployee(Employee::recruit("Игорь"));
$boss->addEmployee(Employee::recruit("Владимир"));
$boss->addEmployee(Employee::recruit("Мария"));
