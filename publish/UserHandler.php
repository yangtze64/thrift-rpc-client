<?php
declare(strict_types=1);

namespace App\Services;

use App\Services\User\UserIf;

class UserHandler implements UserIf
{
    public function ping()
    {
        return 'pong';
    }
}
