<?php
abstract class ApptEncoder
 {
    abstract public function encode(): string;
 }

class BloggsApptEncoder extends ApptEncoder
 {
    public function encode(): string
    {
        return "Данные о встрече в формате BloggsCall\n";
    }
 }

interface Encoder
{
    public function encode(): string;
}

abstract class CommsManager
{
    abstract public function getHeaderText(): string;
    abstract public function getApptEncoder(): ApptEncoder;
    abstract public function getFooterText(): string;
}

class BloggsCommsManager extends CommsManager
{
    public function getHeaderText(): string
    {
        return "Верхний колонтитул BloggsCal\n";
    }
    public function getApptEncoder(): ApptEncoder
    {
        return new BloggsApptEncoder();
    }
    public function getFooterText(): string
    {
        return "Нижний колонтитул BloggsCal\n";
    }
}

class Settings
{
    public static string $COMMSTYPE = 'Mega';
}

class AppConfig
{
    private static ? AppConfig $instance = null;
    private CommsManager $commsManager;
    private function __construct()
    {
        $this->init();
    }
    private function init(): void
    {
        switch (Settings::$COMMSTYPE)
        {
            case 'Mega':
                $this->commsManager = new MegaCommsManager();
                break;
            
            default:
                $this->commsManager = new BloggsCommsManager();
        }
    }
    public static function getInstance(): AppConfig
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }
    public function getCommsManager(): CommsManager
    {
        return $this->commsManager;
    }
}

// Реализация

$mgr = new BloggsCommsManager();
print_r($mgr->getHeaderText());
print_r($mgr->getApptEncoder()->encode());
print_r($mgr->getFooterText());