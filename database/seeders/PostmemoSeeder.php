<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Postmemo;

class PostmemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Postmemo::factory(20)->create();
    }
}
