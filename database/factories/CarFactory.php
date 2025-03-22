<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    public function definition(): array
    {
        $brandsAndModels = [
            'Toyota' => ['Corolla', 'Yaris', 'Rav4', 'Camry', 'Hilux'],
            'Renault' => ['Clio', 'Megane', 'Kadjar', 'Captur', 'Duster'],
            'Peugeot' => ['208', '308', '2008', '3008', '5008'],
            'Volkswagen' => ['Golf', 'Polo', 'Tiguan', 'Passat', 'Touareg'],
            'Mercedes-Benz' => ['A-Class', 'C-Class', 'E-Class', 'GLA', 'GLE'],
            'BMW' => ['Series 1', 'Series 3', 'Series 5', 'X1', 'X3'],
            'Hyundai' => ['i10', 'i20', 'Tucson', 'Santa Fe', 'Elantra'],
            'Dacia' => ['Logan', 'Sandero', 'Duster', 'Lodgy', 'Dokker'],
        ];

        $brand = $this->faker->randomElement(array_keys($brandsAndModels));
        $model = $this->faker->randomElement($brandsAndModels[$brand]);

        return [
            'marque' => $brand,
            'modele' => $model,
            'dors' => $this->faker->randomElement([3, 4, 5]), // Number of doors
            'engine_capacity' => $this->faker->randomFloat(1, 1.0, 4.0), // Engine capacity in liters
            'fuel_type' => $this->faker->randomElement(['essence', 'diesel']),
            'type' => $this->faker->randomElement(['automatic', 'manual']),
            'passengers' => $this->faker->numberBetween(2, 7), // Passenger capacity
            'photos' => json_encode([
                $this->faker->imageUrl(640, 480, 'cars', true, $brand . ' ' . $model),
                $this->faker->imageUrl(640, 480, 'cars', true, $brand . ' ' . $model),
            ]), // JSON array of random car images
            'prixByDay' => $this->faker->randomFloat(2, 100, 1000), // Price per day
            'Disponibilite' => $this->faker->boolean(80), // 80% chance of being available
            'description' => $this->faker->sentence(10), // Short description
            'immatriculation' => $this->generateMoroccanImmatriculation(),
        ];
    }
    private function generateMoroccanImmatriculation(): string
    {
        $number = rand(1000, 99999); // Registration number (1 to 5 digits)
        $arabicLetters = ['أ', 'ب', 'ج', 'د', 'ه', 'و', 'ز', 'ح', 'ط', 'ي']; // Common Arabic letters
        $letter = $arabicLetters[array_rand($arabicLetters)]; // Random letter
        $region = rand(1, 99); // Moroccan region code

        return "{$number}-{$letter}-{$region}";
    }
}
