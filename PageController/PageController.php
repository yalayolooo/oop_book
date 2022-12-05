<?php

// Листинг 12.35
abstract class PageController
{
    private Registry $reg;
    abstract public function process(): void;
    public function __construct()
    {
        $this->reg = Registry::instance();
    }
    public function init(): void
    {
        if (isset($_SERVER['REQUEST_METHOD']))
        {
            $request = new HttpRequest();
        }
        else
        {
            $request = new CliRequest();
        }
        $this->reg->setRequest($request);
    }
    public function forward(string $resource): void
    {
        $request = $this->getRequest();
        $request->forward($resource);
    }
    public function render(string $resource, Request $request): void
    {
        include($resource);
    }
    public function getRequest(): Request
    {
        return $this->reg->getRequest();
    }
}

// Листинг 12.36
class AddVenueController extends PageController
{
    public function process(): void
    {
        $request = $this->getRequest();
        try
        {
            $name = $request->getProperty('venue_name');
            if (is_null($request->getProperty('submitted')))
            {
                $request->addFeedback("Укажите название заведения");
                $this->render(__DIR__ . '/view/add_venue.php', $request);
            }
            elseif(is_null($name))
            {
                $request->addFeedback("Название - обязательное поле");
                $this->render(__DIR__ . '/view/add_venue.php', $request);
                return;
            }
            else
            {
                // Добавление в базу данных
                $this->forward('listvenues.php');
            }
        }
        catch (Exception)
        {
            $this->render(__DIR__ . '/view/error.php', $request);
        }
    }
}





// Реализация Листинг 12.37
$addvenue - new AddVenueController();
$addvenue->init();
$addvenue->process();