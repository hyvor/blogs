<?php

namespace App\Tests\Service\Sudo;

use App\Service\Sudo\SudoPermission;
use App\Service\Sudo\SudoRole;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SudoRole::class)]
class SudoRoleTest extends TestCase
{

    public function test_sudo_role_has_all_permissions(): void
    {
        $this->assertSame(SudoPermission::cases(), SudoRole::SUDO->getPermissions());
    }

}
