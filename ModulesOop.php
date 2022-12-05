<?php
class Person
{
    public $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

// Листинг 5.77
interface Module
{
    public function execute(): void;
}

class FtpModule implements Module
{
    public function setHost(string $host): void
    {
        print_r("FtpModule::setHost(): $host\n");
    }
    public function setUser(string | int $user): void
    {
        print_r("FtpModule::setUser(): $user\n");
    }
    public function execute(): void
    {
        ## Какие-то действия 
    }
}

class PersonModule implements Module
{
    public function setPerson(Person $person): void
    {
        print_r("PersonModule::setPerson(): {$person->name}\n");
    }
    public function execute(): void
    {
        ## Некоторые действия
    }
}

class ModuleRunner
{
    private array $configData = array(
        PersonModule::class => ['person' => 'bob'],
        FtpModule::class => [
            'host' => 'example.com',
            'user' => 'anon'
        ]
        );
    private array $modules = [];


    public function init(): void
    {
        $interface = new \ReflectionClass(Module::class);
        foreach ($this->configData as $modulename => $params)
        {
            $module_class = new \ReflectionClass($modulename);

            if (!$module_class->isSubclassOf($interface))
            {
                throw new Exception(
                    "Неизвестный тип модуля: $modulename");
                
            }

            $module = $module_class->newInstance();

            foreach ($module_class->getMethods() as $method)
            {
                // $this->handleMethod($module, $method, $params);

            }

            array_push($this->modules, $module);
        }
    }

    public function handleMethod(Module $module,
                                \ReflectionMethod $method,
                                array $params): bool
    {
        $name = $method->getName();
        $args = $method->getParameters();

        if (count($args) != 1 || substr($name, 0, 3) != "set")
        {
            return false;
        }
        
        $property = strtolower(substr($name, 3));

        if (!isset($params[$property]))
        {
            return false;
        }

        if (!$args[0]->hasType())
        {
            $method->invoke($module, $params[$property]);
            return true;
        }

        $arg_type = $args[0]->getType();

        if (!($arg_type instanceof \ReflectionUnionType) && class_exists($arg_type->getName()))
        {
            $method->invoke(
                $module,
                (new \ReflectionClass($arg_type->getName()))->newInstance($params[$property])
            );
        }
        else
        {
            $method->invoke($module, $params[$property]);
        }

        return true;
    }
                                
}

// Листинг 5.82
$test = new ModuleRunner();
$test->init();