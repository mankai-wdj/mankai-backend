<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Memo;

class memoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Memo::factory(20)->create();
    }
}
