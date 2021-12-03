<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RandomIpSubscriber implements EventSubscriberInterface
{
    const RANDOM_IP = true;
    const IP_TO_USE = '123.456.789';

    public function onKernelController(ControllerEvent $event)
    {
        $randomIp = self::IP_TO_USE;

        if (self::RANDOM_IP)
        {
            $randomIp = mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255);
        }

        if (empty($randomIp) && !filter_var($randomIp, FILTER_VALIDATE_IP))
        {
            throw new \Exception('Fake Ip has an incorrect format');
        }

        $event->getRequest()->server->set('REMOTE_ADDR', $randomIp);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}