<?php
declare(strict_types=1);

namespace Test;

use Api\Utilities\ApiHttpClientInterface;
use Api\Utilities\ApiParams;
use Api\Utilities\CurrentClient;
use Api\Utilities\CurrentUser;
use Api\Utilities\MailerInterface;
use Api\Utilities\PhpMailerAdapter;
use DateTime;
use InstanceResolver\ResolverClass;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use PDO;
use PHPMailer\PHPMailer\Exception;
use Psr\Log\LoggerInterface;
use ReflectionException;
use RuntimeException;
use Slim\App;
use Slim\Container as SlimContainer;

/**
 * Class AppStub
 * @package Test
 */
class AppStub extends App
{
    private SlimContainer $container;

    /**
     * AppStub constructor.
     * @param array<string, mixed> $settings
     */
    public function __construct(array $settings)
    {

        $this->container = new SlimContainer($settings);
    }

    /**
     * @return SlimContainer
     */
    public function getContainer(): SlimContainer
    {
        return $this->container;
    }
}

/**
 * Class TestCase de base pour les tests du projet
 */
class ActionTestCase extends DbTestCase
{
    private ?SlimContainer $container = null;
    private ?ResolverClass $resolver = null;
    private ?ApiParams $apiParams = null;
    private ?loggerInterface $logger = null;
    private ?PhpMailerAdapter $phpMailerAdapter = null;
    private ?ApiHttpClientStub $client =null;

    /**
     * @param string $className
     * @return mixed
     */
    protected function resolve(string $className)
    {
        if ($this->resolver === null) {
            $this->resolver = new ResolverClass($this->getContainer());
        }
        $resolver = $this->resolver;
        try {
            return $resolver($className);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return SlimContainer
     */
    protected function getContainer(): SlimContainer
    {
        if ($this->container === null) {
            $app = new AppStub([]);
            $handlDependencies = require __DIR__ . '/../src/dependencies.php';
            $handlDependencies($app);
            $this->container = $app->getContainer();
            $self = $this;
            $this->container[PDO::class] = static function () use ($self) {
                return $self->getPdo();
            };
            $this->container['ApiParams::class'] = static function () use ($self) {
                return $self->getApiParams();
            };
            $this->container[MailerInterface::class] = static function () use ($self) {
                return $self->getPhpMailerAdapter();
            };
            $this->container[LoggerInterface::class] = static function () use ($self) {
                return $self->getLogger();
            };
            $this->container["logger"] = static function () use ($self) {
                return $self->getLogger();
            };
            $this->container[ApiHttpClientInterface::class]= static function () use ($self) {
                return $self->getClient();
            };
        }
        return $this->container;
    }
    /** TODO :
    /**
     * @return ApiParams
     * /
    protected function getApiParams(): ApiParams
    {
        if ($this->apiParams === null) {
            $this->apiParams = new ApiParams(6, "http://localhost:8080", "PT24H", "PT60M", "PT60M");
        }
        return $this->apiParams;
    }
    */

    /**
     * @throws Exception
     */
    protected function getPhpMailerAdapter(): PhpMailerAdapter
    {
        if ($this->phpMailerAdapter === null) {
            $this->phpMailerAdapter= new PhpMailerAdapter(
                "smtp.mailtrap.io",
                2525,
                "6677ab3191cdcf",
                "b61b66f4bd0cea",
                "automate@mail.fr", //adresse d’envoi correspondant au login entré précédemment
                "Portail", // nom qui sera affiché
                "No-Reply", // nom qui sera affiché pour le retour
                "No-Reply@mail.fr", // email de retour qui est donnée
            );
        }
        return $this->phpMailerAdapter;
    }

    /**
     * @return DataGenerator
     */
    protected function getGenerator(): DataGenerator
    {
        return new DataGenerator($this->getPdo());
    }

    protected function getCleanner(): DataCleaner
    {
        return new DataCleaner($this->getPdo());
    }

    protected function getLogger(): loggerInterface
    {
        if ($this->logger === null) {
            $logger = new Logger("test");
            $logger->pushProcessor(new IntrospectionProcessor());
            $logger->pushHandler(new StreamHandler(__DIR__."/test.log", Logger::DEBUG));
            $this->logger=$logger;
        }
        return $this->logger;
    }

    public function getClient():ApiHttpClientStub
    {
        if ($this->client === null) {
            $this->client=new ApiHttpClientStub();
        }
        return $this->client;
    }
}
