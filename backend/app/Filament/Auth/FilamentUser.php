<?php

namespace App\Filament\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class FilamentUser extends Model implements Authenticatable
{

    public function getAuthIdentifierName()
    {
        return '';
    }

    public function getAuthIdentifier()
    {

    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($value)
    {

    }

    public function getRememberTokenName()
    {
        return '';
    }
}