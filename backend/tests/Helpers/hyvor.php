<?php

function hyvorUser($fill = [])
{
    return \Hyvor\Internal\Auth\AuthFake::generateUser($fill);
}
