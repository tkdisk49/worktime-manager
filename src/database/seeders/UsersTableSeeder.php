<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => '管理者',
                'email' => 'admin@example.com',
                'password' => bcrypt('adminpassword'),
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => '西 伶奈',
                'email' => 'reina.n@coachtech.com',
                'password' => bcrypt('nishireina'),
                'role' => User::ROLE_EMPLOYEE,
            ],
            [
                'name' => '山田 太郎',
                'email' => 'taro.y@coachtech.com',
                'password' => bcrypt('yamadataro'),
                'role' => User::ROLE_EMPLOYEE,
            ],
            [
                'name' => '増田 一世',
                'email' => 'issei.m@coachtech.com',
                'password' => bcrypt('masudaissei'),
                'role' => User::ROLE_EMPLOYEE,
            ],
            [
                'name' => '山本 敬吉',
                'email' => 'keikichi.y@coachtech.com',
                'password' => bcrypt('yamamotokeikichi'),
                'role' => User::ROLE_EMPLOYEE,
            ],
            [
                'name' => '秋田 朋美',
                'email' => 'tomomi.a@coachtech.com',
                'password' => bcrypt('akitatomomi'),
                'role' => User::ROLE_EMPLOYEE,
            ],
            [
                'name' => '中西 教夫',
                'email' => 'norio.n@coachtech.com',
                'password' => bcrypt('nakanishinorio'),
                'role' => User::ROLE_EMPLOYEE,
            ],
        ];

        $now = now();

        $addTimestamps = function ($user) use ($now) {
            return array_merge($user, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        };

        $users = array_map($addTimestamps, $users);

        DB::table('users')->insert($users);
    }
}
