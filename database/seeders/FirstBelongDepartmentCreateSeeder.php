<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\BelongDepartment;
use DB;

class FirstBelongDepartmentCreateSeeder extends Seeder
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

        DB::table('belong_departments')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Model::reguard();

        $belong_department = new BelongDepartment();
        $belong_department->user_id = 1;
        $belong_department->department_id = 1;
        $belong_department->rank = 1;
        $belong_department->save();

        $belong_department = new BelongDepartment();
        $belong_department->user_id = 1;
        $belong_department->department_id = 2;
        $belong_department->rank = 1;
        $belong_department->save();

        $belong_department = new BelongDepartment();
        $belong_department->user_id = 1;
        $belong_department->department_id = 3;
        $belong_department->rank = 1;
        $belong_department->save();

    }
}
