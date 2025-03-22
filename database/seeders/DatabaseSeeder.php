<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Assurance;
use App\Models\Car;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        Admin::factory(1)->create();
        Car::factory()->count(10)->create();
        Assurance::factory()->count(5)->create();
    }
}
