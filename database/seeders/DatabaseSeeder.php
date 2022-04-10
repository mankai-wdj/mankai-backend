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
<<<<<<< HEAD
=======
        $this->call(ChatmemoSeeder::class);
        $this->call(MymemoSeeder::class);
        $this->call(PostmemoSeeder::class);
        $this->call(MemoSeeder::class);
>>>>>>> 3e40ff131efb987293e5baff2159ef09c38b954b
    }
}
