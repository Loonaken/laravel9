<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('owners')->insert([
            [
                'name'=>'test1',
                'email'=>'test1@test.com',
                'password'=>Hash::make('password1123'),
                'created_at'=>'2022/12/28 12:18:11'
            ],

            [
                'name'=>'test2',
                'email'=>'test2@test.com',
                'password'=>Hash::make('password1223'),
                'created_at'=>'2022/12/28 12:18:11'
            ],

            [
                'name'=>'test3',
                'email'=>'test3@test.com',
                'password'=>Hash::make('password1233'),
                'created_at'=>'2022/12/28 12:18:11'
            ]

            ]);
    }
}
