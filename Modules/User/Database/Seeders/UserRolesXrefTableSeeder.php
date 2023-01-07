<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\UserRolesREF;

class UserRolesXrefTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserRolesREF::create([
            'id'=>'1',
            'user_id'=>'1',
            'role_id'=>'1',
        ]);
    }
}
