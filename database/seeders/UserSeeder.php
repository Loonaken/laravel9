<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name'=>'test',
                'email'=>'test1@test.com',
                'password'=>Hash::make('password123'),
                'created_at'=>'2022/12/28 12:18:11'
            ],
            [
                'name'=>'test',
                'email'=>'test2@test.com',
                'password'=>Hash::make('password123'),
                'created_at'=>'2022/12/28 12:18:11'
            ],

        ]);
    }
}
