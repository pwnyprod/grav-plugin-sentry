# sentry Plugin

![PHP Composer](https://github.com/pwnyprod/grav-plugin-sentry/workflows/PHP%20Composer/badge.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pwnyprod/grav-plugin-sentry/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pwnyprod/grav-plugin-sentry/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/pwnyprod/grav-plugin-sentry/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/pwnyprod/grav-plugin-sentry/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/pwnyprod/grav-plugin-sentry/badges/build.png?b=master)](https://scrutinizer-ci.com/g/pwnyprod/grav-plugin-sentry/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/pwnyprod/grav-plugin-sentry/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)


The **sentry** Plugin is for [Grav CMS](http://github.com/getgrav/grav) with an option to log 404 Not Found events.

## Installation

Installing the sentry plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install sentry

This will install the sentry plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/sentry`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `sentry`. You can find these files on [GitHub](https://github.com/slajnflas/grav-plugin-sentry) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/sentry
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

### Admin Plugin

If you use the admin plugin, you can install directly through the admin plugin by browsing the `Plugins` tab and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/sentry/sentry.yaml` to `user/config/plugins/sentry.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
    enabled: false
    dns_link: 
    log_not_found: true
    error_types: 6143
    excluded_exceptions: ''
```
* `dns_link` **(required)**: Your Sentry DSN Link to the Project.
* `log_not_found` **(required)**: Set to true if you wish sentry to log invalid url access.
* `error_types` **(optional)**: The integer value of the php error reporting level that sentry will use to log errors. 
    Default value is 6143(`E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_USER_DEPRECATED`)
    If this value is not set the SDK will fallback to 32767(`E_ALL`)
* `excluded_exceptions` **(optional)**: Comma-separated exception class names that sentry will ignore(whitelist). All exceptions are logged by default.
    Sample value: `'Grav\Common\Data\ValidationException,Grav\Framework\RequestHandler\Exception\NotFoundException'`    


Note that if you use the admin plugin, a file with your configuration, and named sentry.yaml will be saved in the `user/config/plugins/` folder once the configuration is saved in the admin.

## Usage

- Install the Plugin
- Insert your DNS Link from Sentry Project
- Activate Plugin
- Profit

## Credits

- [Sentry Bugtracking](https://sentry.io)

## To Do

- Testing Plans
- Bugfixing
