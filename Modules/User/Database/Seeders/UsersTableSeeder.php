<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'id'=>'1',
            'email'=>'admin@heroesofdigital.com',
            'first_name'=>'System',
            'last_name'=>'Admin',
            'password' => bcrypt('Hod123!@'),
            'remember_token' => '',
        ]);
    }
}
