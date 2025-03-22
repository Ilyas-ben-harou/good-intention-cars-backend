<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Assurance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssuranceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Assurance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $endDate = (clone $startDate)->modify('+1 year');
        
        return [
            'car_id' => Car::factory(),
            'company_name' => $this->faker->company(),
            'policy_number' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'cost' => $this->faker->randomFloat(2, 300, 2000),
            'status' => $this->faker->randomElement(['active', 'expired']),
        ];
    }

    /**
     * Indicate that the assurance is active.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the assurance is expired.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function expired(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
        ]);
    }
}