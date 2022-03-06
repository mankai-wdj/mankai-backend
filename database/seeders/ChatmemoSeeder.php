<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chatmemo;

class ChatmemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Chatmemo::factory(10)->create();
    }
}
