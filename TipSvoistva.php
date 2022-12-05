<?php

// Листинг  3.71

class Point
{
    private $x = 7;
    private $y = 18;

    public function setVals(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
    
    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }
}

$pare = new Point();
echo $pare->getX() . "\n";
echo $pare->getY() . "\n";
$pare->setVals(44, 12);
echo "Теперь новая пара, это: x => {$pare->getX()}, y => {$pare->getY()}. Объект PARE";