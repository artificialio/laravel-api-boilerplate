<?php

use App\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name'         => 'admin',
            'display_name' => 'Admin',
        ]);

        Role::create([
            'name'         => 'user',
            'display_name' => 'User',
        ]);
    }
}
