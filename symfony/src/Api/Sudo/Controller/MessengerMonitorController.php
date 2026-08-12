<?php

namespace App\Api\Sudo\Controller;

use App\Service\Sudo\SudoPermission;
use Hyvor\Internal\Bundle\Api\SudoPermissionRequired;
use Symfony\Component\Routing\Attribute\Route;
use Zenstruck\Messenger\Monitor\Controller\MessengerMonitorController as BaseMessengerMonitorController;

#[Route('/messenger')]
#[SudoPermissionRequired(SudoPermission::ACCESS_SUDO)]
class MessengerMonitorController extends BaseMessengerMonitorController {}
