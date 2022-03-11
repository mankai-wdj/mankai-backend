<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(ChatmemoSeeder::class);
        $this->call(MymemoSeeder::class);
        $this->call(PostmemoSeeder::class);
        $this->call(MemoSeeder::class);
    }
}
