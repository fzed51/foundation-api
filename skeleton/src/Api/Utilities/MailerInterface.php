<?php
declare(strict_types=1);

namespace Api\Utilities;

/**
 * Interface MailerInterface
 * @package Api\Utilities
 */
interface MailerInterface
{
    public const VALID_NEW_ACCOUNT="ValideNewAccount";
    public const RECOVER_PASS="RecoverPassword";
    public const CONFIRM_RECOVER = "ConfirmRecover";
    public const UPDATE_PASSWORD = "UpdatePassword";
    public const UPDATE_ACCOUNT = "UpdateAccount";
    public const ACCESS_METIER_LINKED = "AccessMetierLinked";
    public const ACCESS_METIER_SHARED = "AccessMetierShared";
    /**
     * @param string $emailTo
     * @param string $template
     * @param array<string, mixed> $data
     * @return mixed
     */
    public function send(string $emailTo, string $template, array $data);
}
