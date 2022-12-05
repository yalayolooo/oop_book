// Листинг 12.39 TemplateView / ViewHelper
<?php

use PhpParser\ErrorHandler\Collecting;

class ViewHelper
{
    public function sponsorList(): string
    {
        // Некоторая сложная работа по получению списка спонсоров
        return "Обувной супермаркет Боба";
    }
    public function render(string $resource, Request $request): void
    {
        $vh = new ViewHelper();
        // Теперь в шаблоне будет переменная $vh из Include($resource);
    }
}

abstract class Base
{
    private \PDO $pdol
    private string $config = __DIR__ . "/woo_options.ini";
    public function __construct()
    {
        $reg = Registry::instance();
        $options = parse_ini_file($this->config,  true);
        $conf = new Conf($options['config']);
        $reg->setConf($conf);
        $dsn = $reg->gedDSN();
        if (is_null($dsn))
        {
            throw new AppException("DSN не определён");
        }
        $this->pdo = new \PDO($dsn);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMORE_EXCEPTION);
    }
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
}

class VenueManager extends Base
{
    private string $addvenue = "INSERT INTO venue (name) VALUES( ?)";
    private string $addvenue = "INSERT INTO space (name, space) VALUES( ?, ?)";
    private string $addvenue = "INSERT INTO event (name, space, start, duration) VALUES( ?, ?, ?, ?)";
    public function addVenue(string $name, array $spaces): array
    {
        $pdo = $this->getPdo();
        $ret = [];
        $ret['venue'] = [$name];
        $stmt = $pdo->prepare($this->addvenue);
        $stmt->execute($ret['venue']);
        $vid = $pdo->lastInsertId();
        $ret['spaces'] = [];
        $stmt = $pdo->prepare($this->addspace);
        foreach ($spaces as $spacename)
        {
            $values = [$spacename, $vid];
            $stmt->execute($values);
            $sid = $pdo->lastInsertId();
            array_unshift($values, $sid);
            $ret['spaces'][] = $values;
        }
        return $ret;
    }
    public function bookEvent(int $spaceid, string $name, int $time, int $duration): void
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare($this->addevent);
        $stmt->execute([$name, $spaceid, $time, $duration]);
    }
}

abstract class DomainObject
{
    public function __construct(private int $id)
    {
        
    }
    public function getId(): int
    {
        return $this->id;
    }
    public static function getCOllection(string $type): Collection
    {
        // Фиктивная реализация
        return Collection::getCOllection($type);
    }
    public function markDirty(): void
    {
        
    }
}

class Venue extends DomainObject
{
    private SpaceCollection $spaces;
    public function __construct(int $id, private string $name)
    {
        $this->name = $name;
        $this->spaces = self::getCOllection(Space::class);
        parent::__construct($id);
    }
    public function setSpaces(SpaceCollection $spaces): void
    {
        $this->spaces = $spaces;
    }
    public function getSpaces(): SpaceCollection
    {
        return $this->spaces;
    }
    public function addSpace(Space $space): void
    {
        $this->spaces->add($space);
        $space->setVenue($this);
    }
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->markDirty();
    }
    public functiongetName(): string
    {
        return $this->name;
    }
}