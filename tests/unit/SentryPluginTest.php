<?php declare(strict_types = 1);

namespace Grav\Plugin\tests\unit;

use Codeception\Exception\TestRuntimeException;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Grav\Common\Config\Config;
use Grav\Common\Grav;
use Grav\Plugin\sentry;
use Sentry as SentrySdk;

class SentryPluginTest extends Unit
{
    /**
     * @var \Grav\Plugin\sentry
     */
    private $sentryPlugin;

    protected function _before(): void
    {
        $grav = Stub::make(Grav::class);
        $config = $this->getMockConfig();

        $this->sentryPlugin = new sentry('sentry', $grav, $config);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = $this->sentryPlugin::getSubscribedEvents();

        self::assertIsArray($events);
        self::assertArrayHasKey('onPluginsInitialized', $events);
    }

    public function testHandleException(): void
    {
        $this->sentryPlugin->handleException(new TestRuntimeException('test'));
    }

    public function testOnPluginsInitialized(): void
    {
        $this->sentryPlugin->onPluginsInitialized();
        //todo: assertion
    }

    /**
     * @return \Grav\Common\Config\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockConfig(): Config
    {
        return new Config([
            'plugins' => [
                'sentry' => [
                    sentry::OPTION_DNS_LINK => 'https://0000000000000000@a000000.ingest.sentry.io/0000000',
                    sentry::OPTION_ERROR_TYPES => 123,
                    sentry::OPTION_EXCLUDED_EXCEPTIONS => 'this,string,will,be,exploded',
                        sentry::OPTION_LOG_NOT_FOUND => false,
                ]
            ],
        ]);
    }
}
