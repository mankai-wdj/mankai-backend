<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mymemo;

class MymemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Mymemo::factory(20)->create();
    }
}
