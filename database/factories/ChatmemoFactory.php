<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ChatmemoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'chatmemo' => $this->faker->name(),
            'user_id' => 1,
            'chat_id' => 2,
            'chatwriter_id' => 3,
            
        ];
    }
}
