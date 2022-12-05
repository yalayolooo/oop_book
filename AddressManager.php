<?php
// Листинг 3.23
declare(strict_types=1);
class AddressManager
{
    private $addresses = ["209.131.36.159", "216.58.213.174"];
    public function outputAddresses($resolve)
    {
        foreach ($this->addresses as $address) {
            print_r($address);
            if ($resolve) {
                print_r(" (".gethostbyaddr($address).")");
            }
            print_r("\n");
        }
    }
}

$manager = new AddressManager();

$manager->outputAddresses("false");