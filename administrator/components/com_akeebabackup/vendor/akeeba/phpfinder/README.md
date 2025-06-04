# PHPFinder

Locate the PHP CLI binary on the server

## What's this?

This library locates the PHP CLI binary from the web (Apache mod_php, CGI, or FastCGI) execution context.

The environments currently supported are:

* cPanel with EasyPHP
* cPanel on CloudLinux
* Plesk on Linux, and Windows
* XAMPP on Linux, Windows, or macOS
* MAMP on Windows, and macOS
* HomeBrew on macOS
* WAMPServer on Windows
* Generic Linux server (works with Debian, Red Hat, Arch Linux, and their derivative distributions)
* Generic BSD server
* Generic Windows server, including PHP installations in C:\PHP
* Generic macOS server

You can try to locate a specific version of PHP (by major.minor or manjor.minor.patch version specification), or just 
any available PHP version.

## Use case

We have mass-distributed web applications which are meant to be installed and operated by non-technical end users. 

Oftentimes we provide CLI scripts, or integrations with such (e.g. integration with Joomla! CLI, or WP-CLI). The user needs very precise instructions to execute these CLI scripts, directly or in a CRON jobs. 

This requires two things: the path to the PHP CLI executable, and the path to the script they need to execute. The latter is easy to determine from the web application. The former is not. This is what this library does. It tries to locate the PHP CLI binary on the server.

## Basic usage

At the very minimum you can do:

```php
$cliPath = \Akeeba\PHPFinder\PHPFinder::make()->getBestPath(PHP_VERSION);
```

This will try to locate the PHP CLI path for the current PHP version. If it fails, it will return NULL.

## Advanced usage

Create and set up a `Configuration` object:

```php
// Check the source comments of the Configuration class to understand what each setting does.
$configuration = \Akeeba\PHPFinder\Configuration::make()
    ->setExtendedBinaryNameSearch(false)
    ->setSoftwareSpecific(false);
```

Create a `PHPFinder` object using the configuration object:

```php
$finder = \Akeeba\PHPFinder\PHPFinder::make($configuration);
```

Search for any available PHP version, or a specific one:

```php
$anyPHP = $finder->getBestPath();
$php83  = $finder->getBestPath('8.3');
```

You can also get all auto-detected PHP folders for a given PHP version:

```php
$anyPHPArray = $finder->getPossiblePaths();
$php83Array  = $finder->getPossiblePaths('8.3');
```

## Caveats

Searching for a specific PHP CLI can be slow in some use cases. We strongly recommend caching the results after the 
first run.

This library is trying to strike a balance between speed and accuracy. This may result in a perceived "wrong" PHP path
to be returned. We know of the following use cases where this might happen:

* Searching for a PHP version by major and minor (but no patch) version number MAY return an older patch version of PHP
  if multiple PHP versions with the same major and minor version number, but different patch version number, are
  installed at the same time. For example, if you have PHP 8.3.1 and PHP 8.3.4 installed at the same time, and you
  search for PHP 8.3, there is no guarantee that PHP 8.3.4 will be returned. 
* If you have multiple installations of the same PHP version across the system there is no guarantee that the PHP binary
  you will be returned will be the one belonging in the same installation. For example, you may have a Linux server with
  PHP installed globally, but also as part of XAMPP. If you run a web script under XAMPP and use this library you MAY
  be returned the globally installed PHP CLI version of the same version as your XAMPP PHP version.
* Likewise, if you have multiple PHP installations, and you don't specify a PHP version, there is no guarantee that the
  path returned will be the latest PHP version, or any particular PHP version. It will be _a_ PHP version found on the
  server.

Searching for a PHP version just by major version number is NOT supported for practical reasons. If you want to simulate
this very inefficient search you can look for PHP x.6, x.5, x.4, x.3, x.2, x.1, and x.0 in this order, returning
whichever is found first. Note that this can be very slow!

Most servers have a _separate_ configuration for PHP CLI than the one used for the web SAPI (mod_php, CGI, or FastCGI).
This is a non-obvious caveat which needs to be communicated to your users. There is no good, fool-proof way to automate
using the same configuration across PHP and the web in those environments.
