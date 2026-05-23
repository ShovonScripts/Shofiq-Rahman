<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_basic_assertion(): void
    {
        $this->assertSame(2, 1 + 1);
    }
}
