<?php

namespace Database\Seeders;

use Carbon\Carbon;
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
                'name' => 'ユーザー',
                'email' => 'user@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => '西 伶奈',
                'email' => 'reina.n@coachtech.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => '山田 太郎',
                'email' => 'taro.y@coachtech.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => '増田 一世',
                'email' => 'issei.m@coachtech.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => '山本 敬吉',
                'email' => 'keikichi.y@coachtech.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => '秋田 朋美',
                'email' => 'tomomi.a@coachtech.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => '中西 教夫',
                'email' => 'norio.n@coachtech.com',
                'password' => bcrypt('password'),
            ],
        ];

        $seedDate = Carbon::today()->subMonth(2)->startOfMonth();

        $addTimestamps = function ($user) use ($seedDate) {
            return array_merge($user, [
                'created_at' => $seedDate,
                'updated_at' => $seedDate,
            ]);
        };

        $users = array_map($addTimestamps, $users);

        DB::table('users')->insert($users);
    }
}
