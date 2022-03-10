<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MymemoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'mymemo' => $this->faker->name(),
            'mymemotitle' => $this->faker->name(),
            'user_id' => 1,
            'url' => $this->faker->image(),
        ];
    }
}
