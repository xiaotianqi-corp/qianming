<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'user', 'description' => 'Regular user with basic access'],
            ['name' => 'support', 'description' => 'Support staff with ticket management'],
            ['name' => 'compliance', 'description' => 'Compliance officer with identity verification access'],
            ['name' => 'admin', 'description' => 'System administrator with full access'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
