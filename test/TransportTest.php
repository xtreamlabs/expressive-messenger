<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;
use Xtreamwayz\PsrContainerMessenger\Container\TransportFactory;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyMessage;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use function array_replace_recursive;

class TransportTest extends TestCase
{
    /** @var array */
    private $config;

    public function setUp() : void
    {
        $this->config = array_replace_recursive((new ConfigProvider())(), require 'example/basic-config.php');
    }

    private function getContainer() : ServiceManager
    {
        $container = new ServiceManager();
        (new Config($this->config['dependencies']))->configureServiceManager($container);
        $container->setService('config', $this->config);

        return $container;
    }

    public function testItCanSendAndReceiveMessages() : void
    {
        $this->config['dependencies']['factories']['in-memory-transport'] = [TransportFactory::class, 'in-memory:///'];

        /** @var TransportInterface $transport */
        $transport = $this->getContainer()->get('in-memory-transport');

        $message  = new DummyMessage('Hello');
        $envelope = new Envelope($message);
        $result   = $transport->send($envelope);

        self::assertEquals($result, $envelope);
        self::assertSame([$envelope], $transport->get());
    }
}
