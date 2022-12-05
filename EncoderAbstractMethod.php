<?php

abstract class CommsManager
{
    abstract public function getHeaderText(): string;
    abstract public function getApptEncoder(): ApptEncoder;
    abstract public function getTtdEncoder(): TtdEncoder;
    abstract public function getContactEncoder(): ContactEncoder;
    abstract public function getFooterText(): string;
}

class BloggsCommsManager extends CommsManager
{
    public function getHeaderText(): string
    {
        return "BloggsCal header\n";
    }
    public function getApptEncoder(): ApptEncoder
    {
        return new BloggsApptEncoder();
    }
    public function getTtdEncoder(): TtdEncoder
    {
        return new BloggsTtdEncoder();
    }
    public function getContactEncoder(): ConstactEncoder
    {
        return new BloggsContactEncoder();
    }
    public function getFooterText(): string
    {
        return "Bloggs Cal footer\n";
    }
}