<?php
// Листинг 4.1

class StaticExample
{
    public static int $aNum = 0;
    public static function sayHello(): void
    {
        print_r("Здравствуйте, бродяги!");
    }
}

class StaticExample2
{
    public static int $aNum = 0;
    public static function sayHello(): void
    {
        self::$aNum++;
        print_r("Привет! (" . self::$aNum . ")\n");
    }
}

print_r(StaticExample::$aNum . "\n");
StaticExample::sayHello();