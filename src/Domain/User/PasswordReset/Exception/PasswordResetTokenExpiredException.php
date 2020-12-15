<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset\Exception;

use Exception;

class PasswordResetTokenExpiredException extends Exception
{
}
