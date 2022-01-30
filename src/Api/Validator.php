<?php
declare(strict_types=1);

namespace Api;

use Closure;
use HttpException\BadRequestException;
use JsonException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;
use RuntimeException;

/**
 * Class Validator de base
 * @package Api
 */
abstract class Validator
{

    /**
     * tableau de traduction
     * @var string[]
     */
    private static array $translation = [];
    /**
     * nombre de translation dans le tableau de traduction
     * @var int
     */
    private static int $nbTranslation = 0;
    protected Validatable $validator;

    /**
     * @param mixed $data
     * @throws BadRequestException
     */
    public function __invoke($data): void
    {
        if (!$this->validator->validate($data)) {
            $errors = "";
            try {
                $this->validator->assert($data);
            } catch (NestedValidationException $exception) {
                $translator = Closure::fromCallable([$this, 'translate']);
                $exception->setParam('translator', $translator);
                $errors = $exception->getFullMessage();
            }
            throw new BadRequestException("les données de la requête ne sont pas valide : " . PHP_EOL . $errors);
        }
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function test($data): bool
    {
        return (bool)$this->validator->validate($data);
    }

    /**
     * @param mixed $data
     * @return string[]
     */
    public function getErrors($data): array
    {
        try {
            $this->validator->assert($data);
        } catch (NestedValidationException $exception) {
            $translator = Closure::fromCallable([$this, 'translate']);
            $exception->setParam('translator', $translator);
            return $exception->getMessages();
        }
        return [];
    }

    /**
     * traduit un message d'erreur
     * @param string $text - message d'erreur
     * @return string
     */
    protected function translate(string $text): string
    {
        self::chargeTranslation();
        if (array_key_exists($text, self::$translation)) {
            $text = self::$translation[$text];
        } else {
            self::$translation[$text] = $text;
        }
        self::sauveTranslation();
        return $text;
    }

    /**
     * charge le fichier de traduction
     * @return void
     */
    private static function chargeTranslation(): void
    {
        if (self::$translation === []) {
            $filename = self::getFileTranslation('fr');
            if (is_file($filename)) {
                try {
                    $json = file_get_contents($filename);
                    self::$translation = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                    self::$nbTranslation = count(self::$translation);
                } catch (JsonException $e) {
                    throw new RuntimeException(
                        "Une erreur est survenue lors de la lecture de la traduction des erreurs"
                    );
                }
            }
        }
    }

    /**
     * donne le nom du fichier de traduction
     * @param string $lang
     * @return string
     * @noinspection PhpSameParameterValueInspection
     */
    private static function getFileTranslation(string $lang): string
    {
        $directory = getcwd() . "/translate/";
        if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }
        return $directory . "Validator_$lang.json";
    }

    /**
     * sauvegarde les traductions dans le fichier de traduction
     * @return void
     */
    private static function sauveTranslation(): void
    {
        $filename = self::getFileTranslation('fr');
        if (count(self::$translation) !== self::$nbTranslation) {
            try {
                $json = json_encode(self::$translation, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
                file_put_contents($filename, $json);
                self::$nbTranslation = count(self::$translation);
            } catch (JsonException $e) {
                throw new RuntimeException(
                    "Une erreur est survenue lors de l'enregistrement de la traduction des erreurs"
                );
            }
        }
    }
}
