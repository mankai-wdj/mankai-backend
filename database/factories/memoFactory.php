<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class memoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'memo' => $this->faker->name(),
            'user_id' => 1
        ];
    }
}
