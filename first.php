<?php

// ТРЕЙТЫ

trait PriceUtilities
{
    // private $taxrate = 20;
    public function calculateTax(float $price): float
    {
        return (($this->taxrate / 100) * $price);
    }
    // abstract public function getTaxRate(): float;
}

trait IdentityTrait
{
    public function generateId(): string
    {
        return uniqid();
    }
}

// ИНТЕРФЕЙСЫ

interface Chargeable
{
    public function getPrice(): float;
}

interface  IdentityObject
{
    public function generateId(): string;
}

// КЛАССЫ
class ShopProduct implements IdentityObject { // означает, что объекты типа ShopProduct можно
                                              //передавать тем методам и функциям, в описании аргументов которых указывается тип интерфейса IdentityObject:
    
    use PriceUtilities;
    use IdentityTrait;
    const AVAILABLE = 0;
    const OUT_OF_STOCK = 1;
    // Тельцо
    private int|float $discount = 0;
    private int $taxrate = 20;
    private int $id = 0;
    
    public function __construct(
        private string $title,
        private string $producerMainName,
        private string $producerFirstName,
        protected int|float $price
    )
    {
    }

    public function setID(int $id): void
    {
        $this->id = $id;
    }
    public function getProducerFirstName(): string
    {
        return $this->producerFirstName;
    }
    public function getProducerMainName(): string
    {
        return $this->producerMainName;
    }
    public function setDiscount(int |float $num): void
    {
        $this->discount = $num;
    }
    public function getDiscount(): int
    {
        return $this->discount;
    }
    public function getTitle(): string
    {
        return $this->title;
    }


    public function getProducer(): string
    {
        return $this->producerFirstName . " "
             . $this->producerMainName;
    }
    public function getSummaryLine(): string
    {
        $base  = "{$this->title} ( {$this->producerMainName}, ";
        $base .= "{$this->producerFirstName} )";
        return $base;
    }
    public function getPrice(): int|float
    {
        return ($this->price - $this->discount);
    }
    public static function getInstance(int $id, \PDO $pdo): ShopProduct
    {
        $stmt = $pdo->prepare("select * from products where id=?");
        $result = $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (empty($row))
        {
            return null;
        }

        if ($row['type'] == "book")
        {
            $product = new BookProduct(
                $row['title'],
                $row['firstname'],
                $row['mainname'],
                (float) $row['price'],
                (int) $row['numpages']
            );
        }
        
        elseif($row['type'] == "cd")
        {
            $product = new CDProduct(
                $row['title'],
                $row['firstname'],
                $row['mainname'],
                (float) $row['price'],
                (int) $row['playlength']
            );
        }
        else 
        {
            $firstname = (is_null($row['firstname'])) ? "" :
            $row['firstname'];
            $product = new ShopProduct(
                $row['title'],
                $firstname,
                $row['mainname'],
                (float) $row['price']
            );
        }
        
        $product->setID((int) $row['id']);
        $product->setDiscount((int) $row['discount']);
        return $product;
    }
    public function calculateTax(float $price): float
    {
        return (($this->taxrate / 100) * $price);
    }
}

class CDProduct extends ShopProduct
{
    public function __construct(string $title, string $firstName,
                                string $mainName, int|float $price,
                                private int $playLength)
    {
        parent::__construct($title, $firstName, $mainName, $price);
    }
    public function getPlayLength(): int
    {
        return $this->playLength;
    }
    public function getSummaryLine(): string
    {
        $base  = "{$this->title} ( {$this->producerMainName}, ";
        $base .= "{$this->producerFirstName} )";
        $base .= ": Время звучания - {$this->playLength}";
        return $base;
    }
    // public function cdInfo(CDProduct $prod): int
    // {
    //     $length = $prod->getPlayLength();
    // }
}

class BookProduct extends ShopProduct
{
    public function __construct(string $title, string $firstName,
                                string $mainName, int|float $price,
                                private int $numPages)
    {
        parent::__construct($title,$firstName,$mainName,$price);
    }
    public function getNumberOfPage(): int
    {
        return $this->numPages;
    }
    public function getSummaryLine(): string
    {
        $base  = parent::getSummaryLine();
        $base .= ": {$this->numPages} стр.";
        return $base;
    }
    public function getPrice(): int|float
    {
        return $this->price;
    }
    public static function storeIdentityObject(IdentityObject $idobj)
    {
        // do something
    }
}


abstract class ShopProductWriter
{
    protected array $products = [];
    public function addProduct(ShopProduct $shopProduct): void
    {
        $this->products[] = $shopProduct;
    }
    abstract public function write(): void;
    // {
    //     $str = "";
    //     foreach ($this->products as $shopProduct)
    //     {
    //         $str .= "{$shopProduct->title}: ";
    //         $str .= $shopProduct->getProducer();
    //         $str .= " ({$shopProduct->getPrice()})\n";
    //     }
    //     print_r($str);
    // }
}


// Сложный вывод (наследование Writera) --- OUTPUT XML DOCUMENT
class XmlProductWriter extends ShopProductWriter
{
    public function write(): void
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement("Товары");
        foreach ($this->products as $shopProduct) {
            $writer->startElement("Товар");
            $writer->writeAttribute("Наименование", $shopProduct->getTitle());
            $writer->startElement("Резюме");
            $writer->text($shopProduct->getSummaryLine());
            $writer->endElement();
            $writer->endElement();
        }
        $writer->endElement();
        $writer->endElement();
        print_r($writer->flush());
    }
}


// Легкий вывод (наследование Writera) --- OUTPUT STRING FORMAR(PRINTR)
class TextProductWriter extends ShopProductWriter
{
    public function write(): void
    {
        $str = "ТОВАРЫ:\n";
        foreach ($this->products as $shopProduct) {
            $str .= $shopProduct->getSummaryLine() . "\n";
        }
        print_r($str);
    }
}


// Листинг 4.20
class Shipping implements Chargeable
{
    public function __construct(private float $price)
    {
    }
    public function getPrice(): float
    {
        return $this->price;
    }
}


// ------------------------ ВЫЗОВ ----------------------------

// $product2 = new ShopProductWriter();
// $product2->products = [];

// ---------------------- ВЫВОД -----------------------------

// $product2->write();

// PDO - база данных

// $dsn = 'mysql:dbname=oop;host=localhost';
// $pdo = new \PDO($dsn, 'root', null);
// $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
// $obj = ShopProduct::getInstance(1, $pdo);

// var_dump($obj);

// /PDO - база данных

// $reflection = new \ReflectionClass(ShopProduct::class);
// print_r($reflection);

print_r(phpinfo());