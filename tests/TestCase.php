<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(User::class)) {
            $user = User::factory()->create([
                'role' => UserRole::SUPER_ROLE,
            ]);
            $this->actingAs($user);
        }
    }
}
