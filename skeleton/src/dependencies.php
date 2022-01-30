<?php /** @noinspection OnlyWritesOnParameterInspection */
declare(strict_types=1);

use Api\Utilities\MailerInterface;
use InstanceResolver\ResolverClass;
use Monolog\Processor\IntrospectionProcessor;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Container;

return static function (App $app) {
    $container = $app->getContainer();

    $container['ipClient'] = static function () {
        $info = new InfoClient\InfoClient();
        return $info->getIp();
    };

    $container['logger'] = static function (Container $container) {
        $settings = $container->get('settings');
        $loggerParams = $settings['logger'];
        $ipClient = $container->get('ipClient');
        $logger = new Monolog\Logger($loggerParams['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(static function (array $record) use ($ipClient): array {
            $record['extra']['info'] = [
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
                'IP' => $ipClient,
                'USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];
            return $record;
        });
        $logger->pushHandler(new Monolog\Handler\StreamHandler($loggerParams['path'], Monolog\Logger::DEBUG));
        return $logger;
    };
    $container[LoggerInterface::class] = static function (Container $container) {
        return $container->get('logger');
    };

    $container[ResolverClass::class] = static function (Container $container) {
        return new ResolverClass($container);
    };

    $container[PDO::class] = static function () {
        /** TODO : initialiser PDO */
    };

    $container['ApiParams::class'] = static function (Container $container) {
        $settings = $container->get("settings");
        $apiParams=$settings["apiParams"];
        /** TODO : initialiser un une class ApiParams (wrapper des paramètres de l'API) */
        // return new ApiParams($apiParams);
    };

    $container[MailerInterface::class] = static function (Container $container) {
        $settings = $container->get("settings");
        $mailer = $settings["mailer"];
        /** TODO : initialiser une class MailerAdapter */
        /*
        return new PhpMailerAdapter(
            $mailer["Host"],
            $mailer["Port"],
            $mailer["Username"],
            $mailer["Password"],
            $mailer["From"],
            $mailer["FromName"],
            $mailer["replyToName"],
            $mailer["replyToEmail"]
        );
        */
    };
};
