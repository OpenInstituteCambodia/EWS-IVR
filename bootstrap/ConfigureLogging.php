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

    protected function configureSingleHandler(Application $app, Writer $log)
    {
        // Stream Handler
        $logPath = '/var/log/app.log';
        $logLevel = Monolog::DEBUG;
        $logStreamHandler = new StreamHandler($logPath, $logLevel);

        // Formatting
        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $logFormat = "%datetime% [%level_name%] (%channel%): %message% %context% %extra%\n";
        $formatter = new LineFormatter($logFormat);
        $logStreamHandler->setFormatter($formatter);

        // Push handler
        $logger = $log->getMonolog();
        $logger->pushHandler($logStreamHandler);
    }

    protected function configureSyslogHandler(Application $app, Writer $log)
    {
        parent::configureSyslogHandler($app, $log);
        // Stream Handler
        $logPath = '/var/log/applications/someng-ews/app.log';
        $logLevel = Monolog::DEBUG;
        $logStreamHandler = new StreamHandler($logPath, $logLevel);

        // Formatting
        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $logFormat = "%datetime% [%level_name%] (%channel%): %message% %context% %extra%\n";
        $formatter = new LineFormatter($logFormat);
        $logStreamHandler->setFormatter($formatter);

        // Push handler
        $logger = $log->getMonolog();
        $logger->pushHandler($logStreamHandler);
    }
}