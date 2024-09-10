<?php

use Hyvor\Internal\Auth\Providers\Fake\FakeProvider;

function hyvorUser($fill = [])
{
    return FakeProvider::fakeLoginUser($fill);
}