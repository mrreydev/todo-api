<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::insert([
            [
                'name' => 'Admin 1',
                'phone' => '628xxxx',
                'email' => 'admin@simpleapi.com',
                'password' => app('hash')->make('admin123'),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Pengguna 1',
                'phone' => '628xxxx',
                'email' => 'user1@simpleapi.com',
                'password' => app('hash')->make('user1234!'),
                'role' => 'general',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Pengguna 2',
                'phone' => '628xxxx',
                'email' => 'user2@simpleapi.com',
                'password' => app('hash')->make('user1234!'),
                'role' => 'general',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
