<?php

namespace Database\Seeders;
use App\Models\DefaultReminder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Calendarlist;
use DB;
use Hash;
use Str;

class FirstUserCreateSeeder extends Seeder
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

        DB::table('schedules')->truncate();
        DB::table('calendarlists')->truncate();
        DB::table('users')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Model::reguard();

        $user = new User();
        $user->username = 'admin';
        $user->password = Hash::make('admin');
        $user->remember_token = Str::random(10);
        $user->email = 'admin@example.com';
        $user->lastname = '管理者';
        $user->firstname = '管理者';
        $user->home_page_id = 1;
        $user->calendarlist_id = 1;
        $user->setting_id = 1;
        $user->role_id = 1;
        $user->save();

        $calendarlist = new Calendarlist();
        $calendarlist->user_id = $user->id;
        $calendarlist->save();

        $remindar = new DefaultReminder();
        $remindar->calendarlist_id = 1;
        $remindar->default_reminders_method_id = 1;
        $remindar->overrides_minutes = 10;
        $remindar->save();
    }
}
