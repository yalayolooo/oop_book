<?php
// Листинг 3.28

class ShopProductWriter2
{
    public function write($shopProduct)
    {
        $str = $shopProduct->title . ": " . $shopProduct->getProducer() . " (" . $shopProduct->price . ")\n";
        print_r($str);
    }
}