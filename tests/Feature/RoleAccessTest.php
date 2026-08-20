<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_based_accounts_are_available(): void
    {
        User::factory()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@matlev.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'User Demo',
            'email' => 'user@matlev.test',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        User::factory()->create([
            'name' => 'Atasan Demo',
            'email' => 'atasan@matlev.test',
            'password' => Hash::make('password123'),
            'role' => 'atasan',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'admin@matlev.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'user@matlev.test', 'role' => 'user']);
        $this->assertDatabaseHas('users', ['email' => 'atasan@matlev.test', 'role' => 'atasan']);
    }
}
