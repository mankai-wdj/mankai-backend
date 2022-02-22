<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotiFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'noti_title' => $this->faker->title(),
            'noti_message' =>Str::random(20),
            'noti_link' => $this->faker->name(),
            'user_id' => 3
        ];
    }
}
