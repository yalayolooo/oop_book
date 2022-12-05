<?php

use ApplicationHelper as GlobalApplicationHelper;
use Monolog\Registry;
use Symfony\Component\Console\Exception\CommandNotFoundException;

abstract class Command
{
    abstract public function execute(CommandContext $context): bool;

}

class LoginCommand extends Command
{
    public function execute(CommandContext $context): bool
    {
        $manager = Registry::getAccessManager();
        $user = $context->get('username');
        $pass = $context->get('pass');
        $user_obj = $manager->login($user, $pass);

        if (is_null($user_obj))
        {
            $context->setError($manager->getError());
        }
        $context->addParam("user", $user_obj);
        return true;
    }
}

class CommandContext
{
    private array $params = [];
    private string $error = "";
    public function __construct()
    {
        $this->params = $_REQUEST;
    }
    public function addParam(string $key, $val): void
    {
        $this->params[$key] = $val;
    }
    public function get(string $key): string
    {
        if (isset($this->params[$key]))
        {
            return  $this->params[$key];
        }

        return null;
    }
    public function setError($error): string
    {
        $this->error = $error;
    }
    public function getError(): string
    {
        return $this->error;
    }
}

class CommandFactory
{
    private static string $dir = 'commands';
    public static function getCommand(string $action = 'Default'): Command
    {
        if (preg_match('/\W/', $action))
        {
            throw new \Exception("Неверные символы в команде");
        }

        $class = __NAMESPACE__ . "\\commands\\" . ucfirst(strtolower($action)) . "Command";

        if (!class_exists($class))
        {
            throw new CommandNotFoundException("Класс '$class' не обнаружен");
        }

        $cmd = new $class();
        return $cmd;
    }
}

// Листинг 12.23
class ViewComponentCompiler
{
    private static $defaultcmd = DefaultCommand::class;
    public function parseFile(string $file): Conf
    {
        $options = \simplexml_load_file($file);
        return $this->parse($options);
    }
    public function parse(\SimpleXMLElement $options): Conf
    {
        $conf = new Conf();
        foreach ($options->control->command as $command)
        {
            $path = (string) $command['path'];
            $cmdstr = (string) $command['class'];
            $path = (empty($path)) ? "/" : $path;
            $cmdstr = new ComponentDescriptor($path, $cmdstr);
            $this->processView($pathobj, 0, $command);
            if (isset($command->status) && isset($command->status['value']))
            {
                foreach ($command->status as $statusel)
                {
                    $status = (string)$statusel['value'];
                    $statusel = constant(Command::class . "::" . $status);

                    if (is_null($statusval))
                    {
                        throw new AppException(" Неизвестное состояние: {$status}");
                    }
                    $this->processView($pathobj, $statusval, $statusel);
                }
            }
            $conf->set($path, $pathobj);
        }
        return $conf;
    }
    public function processView(ComponentDescriptor $pathobj,int $statusval, \SimpleXMLElement $el): void
    {
        if (isset($el->view) && isset($el->view['name']))
        {
            $pathobj->setView($statusval, new TemplateViewComponent((string) $el->view['name']));
        }

        if (isset($el->forward) && isset($el->forward['path']))
        {
            $pathobj->setView($statusval, new ForwardViewComponent((string)$el->forward['path']));
        }
    }
}

// Листинг 12.24
class ComponentDescriptor
{
    private array $views = [];
    public function __construct(private string $path, private string $cmdstr)
    {
        
    }
    public function getCommand(): Command
    {
        $class = $this->cmdstr;
        if (is_null($class))
        {
            throw new AppException("Неизвестный класс '$class'");
        }
        if (!class_exists($class))
        {
            throw new AppException("Класс '$class' не найден");
        }
        $refclass = new \ReflectionClass($class);
        if (!$refclass->isSubclassOf(Command::class))
        {
            throw new AppException("'$class' не является Command");
        }
        return $refclass->newInstance();
    }
    public function setView(int $status, ViewComponent $view): void
    {
        $this->views[$status] = $view;
    }
    public function getView(Request $request): ViewComponent
    {
        $status = $request->getCmdStatus();
        $status = (is_null($status)) ? 0 : $status;
        if (isset($this->views[$status]))
        {
            return $this->views[$status];
        }
        if (isset($this->views[0]))
        {
            return $this->views[0];
        }
        throw new AppException("no view found");
    }
}

class AppController
{
    private static string $defaultcmd = DefaultCommand::class;
    private static string $defaultview = "fallback";
    public function getCommand(Request $request): Command
    {
        try
        {
            $descriptor = $this->getDescriptor($request);
            $cmd = $descriptor->getCommand();
        }
        catch (AppException)
        {
            $request->addFeedback($e->getMessage());
            return new self::$defaultcmd();
        }
        return $cmd;
    }
    public function getView(Request $request): ViewComponent
    {
        try
        {
            $descriptor = $this->getDescriptor($request);
            $view = $descriptor->getView($request);
        }
        catch (AppException)
        {
            return new TemplateViewComponent(self::$defaultview);
        }
        return $view;
    }
    private function getDescriptor(Request $request): ComponentDescriptor 
    {
        $reg = Registry::instance();
        $commands = $reg->getCommands();
        $path = $request->getPath();
        $descriptor = $commands->get($path);

        if (is_null($descriptor))
        {
            throw new AppException("Нет дескриптора для {$path}", 404);
        }

        return $descriptor;
    }
}

interface ViewComponent
{
    public function render(Request $request): void;
}

// Продолжение, Листинг 12.27

class TemplateViewComponent implements ViewComponent
{
    public function __construct(private string $name)
    {
        
    }
    public function render(Request $request): void
    {
        $reg = Registry::instance();
        $conf = $reg->getConf();
        $path = $conf->get("templatepath");

        if (is_null($path))
        {
            throw new AppException("Не найден каталог шаблонов");
        }
        $fullpath = "{$path}/{$this->name}.php";
        if (!file_exists($fullpath))
        {
            throw new AppException("Нет шаблона в {$fullpath}");
        }
        include($fullpath);
    }
}

// Продолжение 12.28
class ForwardViewComponent implements ViewComponent
{
    public function __construct(private ? string $path)
    {
        
    }
    public function render(Request $request): void
    {
        $request->forward($this->path);
    }
    
}



class Controller
{
    public const CMD_DEFAULT = 0;
    public const CMD_OK = 1;
    public const CMD_ERROR = 2;
    public const CMD_INSUFFICIENT_DATA = 3;
    // КОНСТАНТЫ ИЗ ЛИСТИНГА 12.20
    private Registry $reg;
    private CommandContext $context;
    private function __construct()
    {
        $this->reg = Registry::instance();
    }
    private function handleRequest(): void
    {
        $request = $this->reg->getRequest();
        $controller = new AppController();
        $cmd = $controller->getCommand($request);
        $cmd->execute($request);
        $view = $controller->getView($request);
        $view->render($request);
    } 
    public static function run(): void
    {
        $instance = new self();
        $instance->init();
        $instance->handleRequest();
    }
    private function init(): void
    {
        $this->reg->getApplicationHelper()->init();
    }
    public function getContext(): CommandContext
    {
        return $this->context;
    }
    public function process(): void
    {
        $action = $this->context->get('action');
        $action = (is_null($action)) ? "default" : $action;
        $cmd = CommandFactory::getCommand($action);

        if (!$cmd->execute($this->context))
        {
            // Обработка сбоя
        }
        else
        {
            // Удачный исход операции
        }
    }
}

class AddVenue extends Command
{
    protected function doExecute(Request $request): int
    {
        $name = $request->getProperty("venue_name");
        if (is_null($name))
        {
            $request->addFeedback("Имя не предоставлено");
            return self::CMD_INSUFFICIENT_DATA;
        }
        else 
        {
            // Некоторые действия
            $request->addFeedback("'{$name}' added");
            return self::CMD_OK;
        }
        return self::CMD_DEFAULT;
    }
}

class ApplicationHelper
{
    private string $config = __DIR__ . "/data/woo_options.ini";
    private Registry $reg;

    public function __construct()
    {
        $this->reg = Registry::instance();
    }
    public function init(): void
    {
        $this->setupOptions();

        if(defined('STDIN'))
        {
            $request = new CliRequest();
        }
        else
        {
            $request = new HttpRequest();
        }

        $this->reg->setRequest($request);
    }
    private function setupOptions(): void
    {
        if (!file_exists($this->config))
        {
            throw new AppException("Файл не найден");
        }

        $options = parse_ini_file($this->config, true);
        $this->reg->setConf(new Conf($options['config']));
        $this->reg->setCommands(new Conf($options['commands']));
    }
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
    public function getRequest(): Request
    {
        if(is_null($this->request))
        {
            throw new \Exception("Request не установлен");
        }

        return $this->request;
    }
    public function getApplicationHelper(): ApplicationHelper
    {
        if (is_null($this->applicationHelper))
        {
            $this->applicationHelper = new ApplicationHelper();
        }

        return $this->applicationHelper;
    }
    public function setConf(Conf $conf): void
    {
        $this->conf = $conf;
    }
    public function getConf(): Conf
    {
        if (is_null($this->conf))
        {
            $this->conf = new Conf();
        }
        return $this->conf;
    }
    public function setCommands(Conf $commands): void
    {
        $this->commands = $commands;
    }
    public function getCommands(): Conf
    {
        if (is_null($this->commands))
        {
            $this->commands = new Conf();
        }

        return $this->commands;
    }
}

class FeedbackCommand extends Command
{
    public function execute(CommandContext $context): bool
    {
        $msgSystem = Registry::getMessageSystem();
        $email = $context->get('email');
        $msg = $context->get('msg');
        $topic = $context->get('topic');
        $result = $msgSystem->send($email, $msg, $topic);
        if(!$result)
        {
            $context->setError($msgSystem->getError());
            return false;
        }
        return true;
    }
}

class CommandResolver
{
    private static ? \ReflectionClass $refcmd = null;
    private static string $defaultcmd = DeafultCommand::class;
    public function __construct()
    {
        // Этот объект можно сделать конфигурируемым
        self::$refcmd = new \ReflectionClass(Command::class);
    }
    public function getCommand(Request $request): Command
    {
        $reg = Registry::instance();
        $commands = $reg->getCommands();
        $path = $request->getPath();
        $class = $commands->get($path);
        if (is_null($class))
        {
            $request->addFeedback("Путь '$path' не годится");
            return new self::$defaultcmd();
        }
        $refclass = new \ReflectionClass($class);
        if (!$refclass->isSubclassOf(self::$refcmd))
        {
            $request->addFeedback(
                "Команда '$refclass' не является Command"
            );
            return new self::$defaultcmd();
        }
        return $refclass->newInstance();
    }
}

// abstract class Command
// {
//     final public function __construct()
//     {
        
//     }
//     public function execute(Request $request): void
//     {
//         $this->doExecute($request);
//     }
//     abstract protected function doExecute(Request $request): void;
// }

// Листинг 11.53 Реализация (можно потом удалить)
$controller = new Controller();
$context = $controller->getContext();

$context->addParam('action', 'login');
$context->addParam('username', 'Иван');
$context->addParam('pass', 'tiddles');
$controller->process();

print_r($context->getError());