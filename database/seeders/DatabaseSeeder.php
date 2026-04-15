<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Card;
use App\Models\Device;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('  Seeding areas...');
        $startTime = microtime(true);

        config('database.default') === 'mongodb'
            ? $this->seedAreasForDocumentDatabase()
            : $this->seedAreasForRelationalDatabase();

        $this->command->info("  Seeding areas done in {$this->formatTime($startTime, microtime(true))} seconds!");
        $this->command->info('  Seeding cards...');
        $startTime = microtime(true);

        Card::factory()
            ->count(200)
            ->standard()
            ->create();

        Card::factory()
            ->count(200)
            ->superior()
            ->create();

        Card::factory()
            ->count(200)
            ->deluxe()
            ->create();

        Card::factory()
            ->count(50)
            ->suite()
            ->create();

        Card::factory()
            ->count(2)
            ->ambassador()
            ->create();

        $this->command->info("  Seeding cards done in {$this->formatTime($startTime, microtime(true))} seconds!");
    }

    private function seedAreasForRelationalDatabase(): void
    {
        Area::factory()
            ->count(2)
            ->has(
                Area::factory()
                    ->count(10)
                    ->has(
                        Area::factory()
                            ->count(10)
                            ->has(
                                Area::factory()
                                    ->count(10)
                                    ->has(
                                        Device::factory()
                                              ->count(2),
                                        relationship: 'devices'
                                    ),
                                relationship: 'children'
                            )
                            ->has(
                                Device::factory()
                                      ->count(4),
                                relationship: 'devices'
                            ),
                        relationship: 'children'
                    )
                    ->has(
                        Device::factory()
                              ->count(6),
                        relationship: 'devices'
                    ),
                relationship: 'children'
            )
            ->has(
                Device::factory()
                      ->count(10),
                relationship: 'devices'
            )
            ->create();
    }

    private function seedAreasForDocumentDatabase(): void
    {
        $level0 = Area::factory()
                      ->count(2)
                      ->create();

        foreach ($level0 as $area0) {
            $level1 = Area::factory()
                          ->count(10)
                          ->create([
                              'parent_id' => $area0->id,
                          ]);

            foreach ($level1 as $area1) {
                $level2 = Area::factory()
                              ->count(10)
                              ->create([
                                  'parent_id' => $area1->id,
                              ]);

                foreach ($level2 as $area2) {
                    $level3 = Area::factory()
                                  ->count(10)
                                  ->create([
                                      'parent_id' => $area1->id,
                                  ]);

                    foreach ($level3 as $area3) {
                        Device::factory()
                              ->count(2)
                              ->create([
                                  'area_id' => $area3->id,
                              ]);
                    }

                    Device::factory()
                          ->count(4)
                          ->create([
                              'area_id' => $area2->id,
                          ]);
                }

                Device::factory()
                      ->count(6)
                      ->create([
                          'area_id' => $area1->id,
                      ]);
            }

            Device::factory()
                  ->count(10)
                  ->create([
                      'area_id' => $area0->id,
                  ]);
        }
    }

    private function formatTime(float $startTime, float $endTime): string
    {
        $totalTime = $endTime - $startTime;

        return number_format($totalTime, 2);
    }
}
