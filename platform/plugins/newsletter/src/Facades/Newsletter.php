<?php

namespace Botble\Newsletter\Facades;

use Botble\Newsletter\Contracts\Factory;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \Botble\Newsletter\NewsletterManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Illuminate\Contracts\Container\Container getContainer()
 * @method static \Botble\Newsletter\NewsletterManager setContainer(\Illuminate\Contracts\Container\Container $container)
 * @method static \Botble\Newsletter\NewsletterManager forgetDrivers()
 *
 * @see \Botble\Newsletter\NewsletterManager
 */
class Newsletter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Factory::class;
    }
}
