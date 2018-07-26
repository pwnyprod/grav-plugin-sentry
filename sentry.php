<?php

namespace Grav\Plugin;


use Exception;
use Grav\Common\Plugin;
use Raven_Autoloader;
use Raven_Client;
use Raven_ErrorHandler;

/**
 * Class SentryPlugin
 * @package Grav\Plugin
 */
class SentryPlugin extends Plugin
{

    /**
     * @var Raven_Client
     */
    private $client = null;

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        $this->initLoader();
        $this->initClient();

        if (null === $this->client){
            return;
        }

        $this->registerErrorHandler();
    }

    /**
     * Initialize the Composer Autoloader
     */
    private function initLoader()
    {
        require_once __DIR__ . '/vendor/autoload.php';
        Raven_Autoloader::register();
    }

    /**
     * Initialize the Sentry Client
     */
    private function initClient()
    {
        $dns = $this->getConfig();
        if (false !== $dns)
        {
            $this->client = new Raven_Client($dns);
        }
    }

    /**
     * Grep Config from sentry.yaml
     * @return bool|string
     */
    private function getConfig()
    {
        try {
            return $this->grav['config']->get('plugins.sentry.dns_link');
        } catch (Exception $exception)
        {
            return false;
        }
    }

    /**
     * Register the ErrorHandler in the System
     */
    private function registerErrorHandler()
    {
        $error_handler = new Raven_ErrorHandler($this->client);
        $error_handler->registerExceptionHandler();
        $error_handler->registerErrorHandler();
        $error_handler->registerShutdownFunction();
    }
}
