<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostmemoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'postmemo' => $this->faker->name(),
            'user_id' => 1,
            'post_id' =>2,
            //게시글 제목
            'post_content' => $this->faker->name(),
            
        ];
    }
}
