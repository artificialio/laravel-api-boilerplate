<?php

use App\Organisation;
use Illuminate\Database\Seeder;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Organisation::create([
            'name' => 'ACME Corp',
        ]);

        Organisation::create([
            'name' => 'ACME Corp 2',
        ]);
    }
}
