<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\UserRoles;

class UserRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserRoles::create([
            'id'=>'1',
            'name'=>'Admin',
            'slug'=>'admin',
            'description'=>'control all system. ',
            'type'=>'A'
        ]);

        UserRoles::create([
            'id'=>'2',
            'name'=>'User',
            'slug'=>'user',
            'description'=>'Registered users',
            'type'=>'U'
        ]);
    }
}
