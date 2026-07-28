<?php

namespace Tests;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Retrieve a seeded demo account by email.
     */
    protected function demoUser(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /**
     * Open an active shift for the given user so POS routes guarded by
     * the ensure.shift.open middleware are reachable.
     */
    protected function openShiftFor(User $user): Shift
    {
        return Shift::create([
            'app_user_id' => $user->id,
            'start_time' => now(),
            'starting_cash' => 100000,
            'status' => 'open',
        ]);
    }
}
