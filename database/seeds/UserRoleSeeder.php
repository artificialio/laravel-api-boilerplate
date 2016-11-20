<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        Role::truncate();

        $admin = factory(Role::class)->create(['name' => 'admin']);
        factory(\App\User::class)->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role_id' => $admin->id
        ]);
    }
}
