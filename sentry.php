<?php declare(strict_types = 1);

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Sentry as SentrySdk;
use Sentry\State\Scope;

/**
 * Class SentryPlugin
 *
 * @package Grav\Plugin
 */
class sentry extends Plugin
{
    /**
     * The set of php sdk options that this plugin allows to customize. See Sentry PHP sdk documentation for details.
     */
    public const CONFIG_PREFIX = 'plugins.sentry.';
    public const OPTION_DNS_LINK = 'dns_link';
    public const OPTION_LOG_NOT_FOUND = 'log_not_found';
    public const OPTION_ERROR_TYPES = 'error_types';
    public const OPTION_EXCLUDED_EXCEPTIONS = 'excluded_exceptions';

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
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {
        // Don't proceed if we are in the admin plugin
        if($this->isAdmin()) {
            return;
        }

        $this->initLoader();

        if(!$this->initClient()) {
            return;
        }

        $this->registerErrorHandlers();

        if($this->config->get(static::CONFIG_PREFIX . static::OPTION_LOG_NOT_FOUND, false)) {
            $this->enable([
                'onPageNotFound' => ['onPageNotFound', 1],
            ]);
        }
    }

    /**
     *  if page not found found saves data
     */
    public function onPageNotFound(): void
    {
        $time = date("Y-m-d h:i:sa");
        $uri  = $this->grav['uri'];
        $url  = $uri->url;

        // Just add context data and unique per url fingerprint before throwing Exception
        SentrySdk\configureScope(function(Scope $scope) use ($url, $time) {
            $scope->setExtra('url', $url);
            $scope->setExtra('time', $time);
            $scope->setExtra('referer', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

            $scope->setFingerprint(['{{ default }}', $url]);
        });
//        throw new \RuntimeException('Page not found: '.$url, 404);
    }

    /**
     * Initialize the Composer Autoloader
     */
    private function initLoader(): void
    {
        require_once __DIR__.'/vendor/autoload.php';
    }

    /**
     * Initialize the Sentry Client
     *
     * @return bool
     */
    private function initClient(): bool
    {
        $dsn = $this->getConfig();
        $optionals = $this->getOptionalConfigs();

        // Don't initialize if mandatory dsn config not set
        if(empty($dsn)) {
            return false;
        }

        $opts = array_merge(
            $dsn,
            $optionals
        );

        SentrySdk\init($opts);

        return true;

    }

    /**
     * Grep Config from sentry.yaml
     *
     * @return array
     */
    private function getConfig(): array
    {
        $configs = [];
        $dnsLink = $this->config->get(static::CONFIG_PREFIX . static::OPTION_DNS_LINK);
        if(null !== $dnsLink) {
            $configs['dsn'] = $dnsLink;
        }

        return $configs;
    }

    /**
     * Get optional configs if they can be found.
     *
     * @return array
     */
    private function getOptionalConfigs(): array
    {
        $configs = [];
        $errorTypes = $this->config->get(static::CONFIG_PREFIX . static::OPTION_ERROR_TYPES);
        $excludedExceptions = $this->config->get(static::CONFIG_PREFIX . static::OPTION_EXCLUDED_EXCEPTIONS);

        if(null !== $errorTypes) {
            $configs[static::OPTION_ERROR_TYPES] = $errorTypes;
        }

        if(null !== $excludedExceptions) {
            $configs[static::OPTION_EXCLUDED_EXCEPTIONS] = explode(',', $excludedExceptions);
        }

        return $configs;
    }

    /**
     * Register the ErrorHandler in the System
     */
    private function registerErrorHandlers(): void
    {
        set_exception_handler([$this, 'handleException']);
    }

    /**
     * @param \Exception|\Throwable $exception
     */
    public function handleException($exception): void
    {
        SentrySdk\captureException($exception);
    }
}
