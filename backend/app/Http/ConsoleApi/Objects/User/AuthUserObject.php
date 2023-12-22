<?php declare(strict_types=1);

namespace App\Http\ConsoleApi\Objects\User;

use Hyvor\Helper\Auth\AuthUser;

class AuthUserObject
{

    public int $id;
    public string $name;
    public ?string $picture_url;
    public ?string $username;

    public function __construct(AuthUser $user)
    {
        $this->id = $user->id;
        $this->name = $user->name;
        $this->picture_url = $user->picture_url;
        $this->username = $user->username;
    }

}