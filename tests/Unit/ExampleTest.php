<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;


class Test {

    public string $name;
    public int $age;

    public function __construct($age) {
        $this->age = $age;
    }
}

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {

        $test = new Test(1);
        dd(json_encode($test));

        $this->assertTrue(true);
    }
}
