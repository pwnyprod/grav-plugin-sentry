<?php

namespace Grav\Plugin;


use Exception;
use Grav\Common\Plugin;
use Sentry;
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
     * The set of php sdk options that this plugin allows to customize. See Sentry PHP sdk documentation for details.
     */
    const OPTION_ERROR_TYPES = 'error_types';
    const OPTION_EXCLUDED_EXCEPTIONS = 'excluded_exceptions';

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
        $config = $this->grav['config'];

        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        $this->initLoader();

        if (!$this->initClient()) {
            return;
        }

        $this->registerErrorHandlers();

        if ($config->get('plugins.sentry.log_not_found', false)) {
            $this->enable([
                'onPageNotFound' => ['onPageNotFound', 1],
            ]);
        }
    }

    /**
     *  if page not found found saves data
     *
     */
    public function onPageNotFound()
    {
        $time         = date("Y-m-d h:i:sa");
        $uri          = $this->grav['uri'];
        $url          = $uri->url;

        // Just add context data and unique per url fingerprint before throwing Exception
        Sentry\configureScope(function (Sentry\State\Scope $scope) use($url, $time) {
            $scope->setExtra('url', $url);
            $scope->setExtra('time', $time);
            $scope->setExtra('referer', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

            $scope->setFingerprint(['{{ default }}', $url]);
        });

        throw new \RuntimeException('Page not found: '. $url, 404);
    }

    /**
     * Initialize the Composer Autoloader
     */
    private function initLoader()
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the Sentry Client
     */
    private function initClient()
    {
        $dsn = $this->getConfig();
        $optionals = $this->getOptionalConfigs();

        // Don't initialize if mandatory dsn config not set
        if (false !== $dsn) {
            $opts = array_merge(
                ['dsn' => $dsn],
                $optionals
            );

            Sentry\init($opts);

            return true;
        }

        return false;
    }

    /**
     * Grep Config from sentry.yaml
     * @return bool|string
     */
    private function getConfig()
    {

        try {
            return $this->grav['config']->get('plugins.sentry.dns_link');
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * Get optional configs if they can be found.
     * @return array
     */
    private function getOptionalConfigs()
    {
        $configs = [];
        try {

            $configs[self::OPTION_ERROR_TYPES] = $this->grav['config']->get('plugins.sentry.' . self::OPTION_ERROR_TYPES);
            $excludedExceptions = $this->grav['config']->get('plugins.sentry.' . self::OPTION_EXCLUDED_EXCEPTIONS);
            if ($excludedExceptions) {
                $configs[self::OPTION_EXCLUDED_EXCEPTIONS] = explode(',', $excludedExceptions);
            }

        } catch (Exception $exception) {
            // do nothing if optional config not found continue returning $configs[] array
        }

        return $configs;
    }

    /**
     * Register the ErrorHandler in the System
     */
    private function registerErrorHandlers()
    {
        set_exception_handler([$this, 'handleException']);
    }

    public function handleException($exception) {
        Sentry\captureException($exception);
    }
}
