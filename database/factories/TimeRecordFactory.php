<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TimeRecord;

class TimeRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TimeRecord::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'start_datetime' => fake()->dateTime(),
            'end_datetime' => fake()->dateTime(),
            'project_name' => fake()->word(),
            'task_name' => fake()->word(),
        ];
    }
}
