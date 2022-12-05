<?php

// БАЗЫ ДАННЫХ Листинг 13.1

use IdentityObject as GlobalIdentityObject;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Cast\Object_;

abstract class Mapper
{
    
    public function __construct(protected \PDO $pdo)
    {
       
    }
    public function find(int $id): ? DomainObject
    {
        $old = $this->getFromMap($id);
        if (!is_null($old))
        {
            return $old;
        }
        return $object;
    }
    abstract protected functino targetClass(): string;
    private function getFromMap($id) : ? DomainObject
    {
        return ObjectWatcher::exists($this->targetClass(), $id);
    }
    private function addToMap(DomainObject $obj): void
    {
        
    }
    public function createObject(array $raw): DomainObject
    {
        $obj = $this->doCreateObject($raw);
        return $obj;
    }
    public function insert(DomainObject $obj): void
    {
        $this->doInsert($obj);
    }
    abstract public function update(DomainObject $obj): void;
    abstract protected function doCreateObject(array $raw): DomainObject;
    abstract protected function dolnsert(DomainObject $object): void;
    abstract protected function selectStmt(): \PDOStatement;
    abstract protected function targetClass(): string;
}

class VenueMapper extends Mapper
{
    private \PDOStatement $selectStmt;
    private \PDOStatement $updateStmt;
    private \PDOStatement $insertStmt;
    public function __construct()
    {
        parent::__construct();
        $this->selectStmt = $this->pdo->prepare("SELECT * FROM venue WHERE id=?");
        $this->updateStmt = $this->pdo->prepare("UPDATE venue SET name=?, id=? WHERE id=?");
        $this->insertStmt = $this->pdo->prepare("INSERT INTO venue ( name ) VALUES( ? )");
    }
    protected function targetClass(): string
    {
        return Venue::class;
    }
    public function getCollection(array $raw): VenueCollection
    {
        return new VenueCollection($raw, $this);
    }
    protected function doCreateObject(array $raw): Venue
    {
        $obj = new Venue((int)$raw['id'], $raw['name']);
        return $obj;
    }
    protected function doInsert(DomainObject $obj): void
    {
        $values = [$obj->getName()];
        $this->insertSstmt->execute($values);
        $id = $this->pdo->lastINsertId();
        $obj->setId((int)$id);
    }
    public function update(DomainObject $obj): void
    {
        $values = [$obj->getName, $obj->getId(), $obj->getId()];
        $this->updateStmt->execute($values);
    }
    public function selectStmt(): \PDOStatement
    {
        return $this->selectStmt;
    }
}

abstract class GenCollection
{
    protected int $total = 0;
    private array $objects = [];
    public function __construct(protected array $raw = [], protected ? Mapper $mapper = null)
    {
        $this->total = count($raw);

        if (count($raw) && is_null($mapper))
        {
            throw new AppException("Для генерации объекта нужен Mapper");
        }

    }
    public function add(DomainObject $object): void
    {
        $class = $this->targetClass();

        if(!($object instanceof $class))
        {
            throw new AppException("Это коллекция {$class}");
        }
        $this->notifyAccess();
        $this->objects[$this->total] = $object;
        $this->total++;
    }
    public function getGenerator(): \Generator
    {
        for($x = 0; $x < $this->total; $x++)
        {
            yield $this->getRow($x);
        }
    }
    abstract public function targetClass(): string;
    protected function notifyAccess(): void
    {
        // Пока пустое
    }
    private function getRow(int $num): ? DomainObject
    {
        $this->notifyAccess();
        if($num>=$this->total || $num <0)
        {
            return null;
        }
        if (isset($this->objects[$num]))
        {
            return $this->objects[$num];
        }
        if (isset($this->raw[$num]))
        {
            $this->objects[$num] = $this->mapper->createObject($this->raw[$num]);
            return $this->objects[$num];
        }
        return null;
    }
}

class SpaceMapper 
{
    protected function targetClass(): string
    {
        return Space::class;
    }
    public function doCreateObject(array $raw): Space
    {
        $obj = new Space((int)$raw['id'], $raw['name']);
        $venmapper = new VenueMapper();
        $venue = $venmapper->find((int)$raw['venue']);
        $obj->setVenue($venue);
        $eventmapper = new EventMapper();
        $eventcollection = $eventmapper->findBySpaceId((int)$raw['id']);
        $obj->setEvents($eventcollection);
        return $obj;
    }
}

abstract class Collection implements \Iterator
{
    protected int $ottal = 0;
    protected array $raw = [];
    private int $pointer = 0;
    private array $objects = [];
    // Collection
    public function __construct(array $raw = [], protected ? DomainObjectFactory $dofact = null)
    {
        if (count($raw) && !is_null($dofact))
        {
            $this->raw = $raw;
            $this->total = count($raw);
        }
    }

    
}

// Листинг 13.37
abstract class IdentityObject
{
    private ? string $name = null;
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getName(): string
    {
        return $this->name;
    }
}

// Листинг 13.38

// Листинг 13.39
class Field
{
    protected array $comps = [];
    protected bool $incomplete = false;
    // Установка имени поля (например, age)
    public function __construct(protected string $name)
    {
        
    }
    // Добавление оператора и значения для тестирования (например, больше 40), а также свойство $comps
    public function addTest(string $operator, $value): void
    {
        $this->comps[] = [
            'name' => $this->name,
            'operator' => $operator,
            'value' => $value
        ];
    }
    // $comps - это массив, поэтому одно поле можно проверить не одним, а несколькими способами
    public function getComps(): array
    {
        return $this->comps;
    }
    // Если массив $comps не содержит элементов, значит, данные сравнения с полем и само поле не готовы для применения в запросе
    public function isIncomplete(): bool
    {
        return empty($this->comps);
    }
}

// Листинг 13.40
class IdentityObject
{
    protected ? Field $currentfield = null;
    protected array $fields = [];
    private array $enforce = [];
    // Объект идентичности может быть создан пустым или же с отдельным полем
    public function __construct(? string $field = null, ? array $enforce = null)
    {
        if (!is_null($enforce))
        {
            $this->enforce = $enforce;
        }
        if (!is_null($field))
        {
            $this->field($field);
        }
    }
    // Имена полей, на которые наложено данное ограничение
    public function getObjectFields(): array
    {
        return $this->enforce;
    }
    // Добавляет новое поле.
    // Генерирует ошибку, если текущее поле неполное (т.е. age, а не age > 40).
    // Этот метод возвращает ссылку на текущий объект и тем самым разрешает текучий синтаксис
    public function field(string $fieldname): self
    {
        if (!$this->isVoid() && $this->currentfield->isIncomplete())
        {
            throw new \Exception("Неполное поле");
        }

        $this->enforceField($fieldname);
        if (isset($this->fields[$fieldname]))
        {
            $this->currentfield = $this->fields[$fieldname];
        }
        else
        {
            $this->currentfield = new Field($fieldname);
            $this->fields[$fieldname] = $this->currentfield;
        }
        return $this;
    }
    // Имеются ли уже какие-нибудь поля у объекта идентичности?
    public function isVoid(): boolval
    {
        return empty($this->fields);
    }
    // Допустимо ли заданное имя поле?
    public function enforceField(string $fieldname): void
    {
        if (!in_array($fieldname, $this->enforce) && !empty($this->enforce))
        {
            $forcelist = implode(', ', $this->enforce);
            throw new \Exception(
                "{$fieldname} не является корректным полем ($forcelist)"
            );
        }
    }
    // Добавляет оператор равенства в текущее поле, т.е. 'age' превращается в 'age=40'.
    // Возвращает ссылку на текущий объект через operator()
    public function eq($value): self
    {
        return $this->operator("=", $value);
    }
    // Меньше
    public function lt($value): self
    {
     return $this->operator("<", $value);
    }
    // Больше
    public function gt($value): self
    {
        return $this->operator(">", $value);
    }
    // Выполняет работу, чтобы методы операторов получали текущее поле, и добавляет оператор и проверяемое значение
    private function operator(string $symbol, $value): self
    {
        if ($this->isVoid())
        {
            throw new \Exception("Поле объекта не определено");
        }
        $this->currentfield->addTest($symbol, $value);
        return $this;
    }
    // Возвращает все полученные до сих пор результаты сравнения из ассоциативного массива
    public function getComps(): array
    {
        $ret = [];
        foreach ($this->fields as $field)
        {
            $ret = array_merge($ret, $field->getComps());
        }
        return $ret;
    }
}

// Листинг 13.42
class EventIdentityObject extends IdentityObject
{
    public function __construct(string $field = null)
    {
        parent::construct(
            $field,
            ['name', 'id', 'start', 'duration', 'space']
        );
    }
}

// Лиситнг 13.44
abstract class Updatefactory
{
    abstract public function newUpdate(DomainObject $obj): array;
    protected function buildStatement(string $table, array $fields, ? array $conditions = null): array
    {
        $terms = array();
        if (!is_null($conditions))
        {
            $query = "UPDATE ${table} SET ";
            $query .= implode(" = ?,", array_keys($fields)) . " = ?";
            $terms = array_values($fields);
            $cond = [];
            $query .= " WHERE ";
            foreach ($conditions as $key => $val)
            {
                $cond[] = "$key = ?";
                $terms[] = $val;
            }
            $query .= implode(" AND ", $cond);
        }
        else
        {
            $qs = [];
            $query = "INSERT INTO {$table} (";
            $query .= implode(",", array_key($fields));
            $query .= ") VALUES (";
            foreach ($fields as $name => $value)
            {
                $qs = [];
                $query = "INSERT INTO {$table} (";
                $query .= implode(",", array_keys($fields));
                $query .= ") VALUES (";
                foreach ($fields as $name => $value)
                {
                    $terms[] = $value;
                    $qs[] = '?';
                }
            }
            $query .= implode(",", $qs);
            $query .= ")";
        }
        return [$query, $terms];
    }
}

// Листинг 13.45
class VenueUpdateFactory extends Updatefactory
{
    public function newUpdate(DomainObject $obj): array
    {
        // Обратите внимание на удаленную проверку типа
        $id = $obj->getId();
        $cond = null;
        $values['name'] = $obj->getName();

        if ($id > 0)
        {
            $cond['id'] = $id;
        }

        return $this->buildStatement("venue", $values, $cond);
    }
}

// Должна быть реаализация UpdateFactory

// #SELECTION FACTORY Листинг 13.48
abstract class SelectionFactory
{
    abstract public function newSelection(IdentityObject $obj): array;
    public function buildWhere(IdentityObject $obj): array
    {
        if ($obj->isVoid())
        {
            return ["", []];
        }
        $compstrings = [];
        $values = [];
        foreach ($obj->getComps() as $comp)
        {
            $compstrings[] = "{$comp['name']} {$comp['operator']} ?";
            $values[] = $comp['values'];
        }
        $where = "WHERE " . implode(" AND ", $compstrings);
        return [$where, $values];
    }
}

// Листинг 13.49
class VenueSelectionFactory extends SelectionFactory
{
    public function newSelection(IdentityObject $obj): array
    {
        $fields = implode(',' $obj->getObjectFields());
        $core = "SELECT $fields FROM venue";
        list($where, $values) = $this->buildWhere($obj);
        return [$core . " " . $where, $values];
    }
}

// Листинг 13.51
class DomainObjectAssembler
{
    protected \PDO $pdo;
    public function __construct(private PersistenceFactory $factory)
    {
        $reg = Registry::instance();
        $this->pdo = $reg->getPdo();
    }
    public function getStatement(string $str): \PDOStatement
    {
        if (!isset($this->statements[$str]))
        {
            $this->statements[$str] = $this->pdo->prepare($str);
        }

        return $this->statements[$str];
    }
    public function findOne(IdentityObject $idobj): DomainObject
    {
        $collection = $this->find($idobj);
        return $collection->next();
    }
    public function find(IdentityObject $idobj): Collection
    {
        $selfact = $this->factory->getSelectionFactory();
        list($selection, $values) = $selfact->newSelection($idobj);
        $stmt = $this->getStatement($selection);
        $stmt->execute($values);
        $raw = $stmt->fetchAll();
        return $this->factory->getCollection($raw);
    }
    public function insert(DomainObject $obj): void
    {
        $upfact = $this->factory->getUpdateFactory();
        list($update, $values) = $upfact->newUpdate($obj);
        $stmt = $this->getStatement($update);
        $stmt->execute($values);

        if ($obj->getId() < 0)
        {
            $obj->setId((int)$this->pdo->lastInsertId());
        }

        $obj->markClean();
    }
}

// Листинг 13.52
// $factory = PersistanceFactory::getFactory(Venue::class);
// $finder = new DomainObjectAssembler($factory);

class ObjectWatcher
{
    private array $all = [];
    private array $dirty = [];
    private array $new = [];
    private array $delete = [];
    private static ? ObjectWatcher $instance = null;
    private function __construct()
    {
        
    }
    public static function instance(): self
    {
        if (is_null(self::$instance))
        {
            self::$instance = new ObjectWatcher();
        }
        return self::$instance;
    }
    public function globalKey(DomainObject $obj): string
    {
        return get_class($obj) . "." . $obj->getId();
    }
    public static function add(DomainObject $obj): void
    {
        $inst = self::instance();
        $inst->all[$inst->globalKey($obj)] = $obj;
    }
    public static function exists(string $classname, int $id): ? DomainObject
    {
        $inst = self::instance();
        $key = "{$classname} . {$id}";
        if (isset($inst->all[$key]))
        {
            return $inst->all[$key];
        }
        return null;
    }
    public function find(int $id): ? DomainObject
    {
        $old = $this->getFromMap($id);
        if (!is_null($old))
        {
            return $old;
        }
        // Работа с базой данных
        return $object;
    }
    abstract protected function targetClass(): string;
    private function getFromMap($id): ? DomainObject
    {
        return ObjectWatcher::exists(
            $this->targetClass(),
            $id
        );
    }
    private function addToMap(DomainObject $obj): void
    {
        ObjectWatcher::add($obj);
    }
    public function createObject($raw): ? DomainObject
    {
        $old = $this->getFromMap((int) $raw['id']);
        if (!is_null($old))
        {
            return $old;
        }
        $obj = $this->doCreateObject($raw);
        $this->addToMap($obj);
        return $obj;
    }
    public function insert(DomainObject $obj): void
    {
        $this->doInsert($obj);
        $this->addToMap($obj);
    }
    public static function addDelete(DomainObject $obj): void
    {
        $inst = self::instance();
        $inst->delete[$inst->globalKey($obj)] = $obj;
    }
    public static function addDirty(DomainObject $obj): void
    {
        $inst = self::instance();
        if (!in_array($obj, $inst->new, true))
        {
            $inst->dirty[$inst->globalKey($obj)] = $obj;
        }
    }
    public static function addNew(DomainObject $obj): void
    {
        $inst = self::instance();
        // Пока нет Id
        $inst->new[] == $obj;
    }
    public static function addClean(DomainObject $obj): void
    {
        $inst = self::instance();
        unset($inst->delete[$inst->globalKey($obj)]);
        unset($inst->dirty[$inst->globalKey($obj)]);
        $inst->new = array_filter(
            $inst->new,
            function($a) use($obj)
            {
                return !($a===$obj);
            }
        );
    }
    public function performOperations(): void
    {
        foreach ($this->dirty as $key=>$obj)
        {
            $obj->getFinder()->update($obj);
        }
        foreach ($this->new as $key=>$obj)
        {
            $obj->getFinder()->insert($obj);
            print_r("Вставка" . $obj->getName() . "\n");
        }
        $this->dirty = [];
        $this->new = [];
    }
}

// Реализация
$mapper = new VenueMapper();
$venue = $mapper->find(2);
print_r($venue);