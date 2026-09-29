<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Backend HTTP assertions do not require compiled browser assets.
        $this->withoutVite();
    }

    protected function adminUser(array $attributes = []): User
    {
        return User::factory()->admin()->create($attributes);
    }

    protected function authorUser(array $attributes = []): User
    {
        return User::factory()->author()->create($attributes);
    }

    protected function readerUser(array $attributes = []): User
    {
        return User::factory()->reader()->create($attributes);
    }
}
