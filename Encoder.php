<?php
// abstract class ApptEncoder
//  {
//     abstract public function encode(): string;
//  }

// class BloggsApptEncoder extends ApptEncoder
//  {
//     public function encode(): string
//     {
//         return "Данные о встрече в формате BloggsCall\n";
//     }
//  }

// class CommsManager
// {
//     public const BLOGGS = 1;
//     public const MEGA = 2;
//     public function __construct(private int $mode)
//     {
        
//     }
//     public function getApptEncoder(): ApptEncoder
//     {
//         switch ($this->mode)
//         {
//             case (self::MEGA):
//                 return new MegaApptEncoder();
//             default:
//                 return new BloggsApptEncoder();
//         }
//     }
//     public function getHeaderText(): string
//     {
//         switch ($this->mode)
//         {
//             case (self::MEGA):
//                 return "MegaCal header\n";
//             default:
//                 return "BloggsCal header\n";
//         }
//     }
// }

// class MegaApptEncoder extends ApptEncoder
// {
//     public function encode(): string
//     {
//         return "Данные о встрече в формате MegaCall\n";
//     }
// }

// // Реализация

// $man = new CommsManager(CommsManager::MEGA);
// print_r(get_class($man->getApptEncoder()) . "\n");
// $man = new CommsManager(CommsManager::BLOGGS);
// print_r(get_class($man->getApptEncoder()) . "\n");
