<?php

namespace Database\Seeders;
use App\Models\User;
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
        $this->call(FirstUserCreateSeeder::class);
        $this->call(FirstDepartmentCreateSeeder::class);
        $this->call(FirstEventTypeCreateSeeder::class);
        $this->call(FirstBelongDepartmentCreateSeeder::class);
    }
}
