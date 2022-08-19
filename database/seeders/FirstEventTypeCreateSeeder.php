<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\EventType;
use DB;

class FirstEventTypeCreateSeeder extends Seeder
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

        DB::table('event_types')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Model::reguard();

        $model = new EventType();
        $model->name = '外出';
        $model->color = '#9F9F9FFF';
        $model->rank = '1';
        $model->save();

        $model = new EventType();
        $model->name = '会議';
        $model->color = '#FFB400FF';
        $model->rank = '2';
        $model->save();

        $model = new EventType();
        $model->name = '電話';
        $model->color = '#0000FFFF';
        $model->rank = '3';
        $model->save();
    }
}
