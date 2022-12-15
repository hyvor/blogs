<?php

use Hyvor\HyvorConnecter\HyvorUser;

function hyvorUser($fill = [])
{
    return HyvorUser::dummy($fill);
}