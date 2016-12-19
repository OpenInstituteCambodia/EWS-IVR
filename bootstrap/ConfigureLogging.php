<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/11/16
 * Time: 2:37 PM
 */

namespace Bootstrap;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\ConfigureLogging as BaseConfigureLogging;
use Illuminate\Log\Writer;

class ConfigureLogging extends BaseConfigureLogging
{

}