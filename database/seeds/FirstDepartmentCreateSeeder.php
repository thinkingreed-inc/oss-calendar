<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;

class FirstDepartmentCreateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('departments')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Model::reguard();

        $department = new Department();
        $department->name = '管理部';
        $department->parent_id = 0;
        $department->rank = 1;
        $department->save();

        $department = new Department();
        $department->name = '営業部';
        $department->parent_id = 0;
        $department->rank = 2;
        $department->save();

        $department = new Department();
        $department->name = '開発部';
        $department->parent_id = 0;
        $department->rank = 3;
        $department->save();

    }
}
