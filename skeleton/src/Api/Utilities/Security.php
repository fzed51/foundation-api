<?php
declare(strict_types=1);

namespace Api\Utilities;

use Helper\SpicyMash;
use RuntimeException;

/**
 * Class Security
 * @package Api\Utilities
 */
class Security extends SpicyMash
{
    /**
     * @var string
     */
    private string $algoPass = PASSWORD_BCRYPT;

    /**
     * @param string $mdp
     * @return string
     */

    public function passHash(string $mdp): string
    {
        $hash = password_hash($mdp, $this->algoPass);
        if (is_string($hash)) {
            return $hash;
        }
        throw new RuntimeException("Impossible de masquer le mot de passe");
    }

    /**
     * @param string $mdp
     * @param string $hash
     * @return bool
     */
    public function passVerify(string $mdp, string $hash): bool
    {
        return password_verify($mdp, $hash);
    }

    /**
     * @param int $length
     * @return string
     */
    public function newUid(int $length = 128): string
    {
        return substr($this->randomBytes($length, false), 0, $length);
    }

}
