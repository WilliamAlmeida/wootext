<?php

namespace Database\Seeders;

use App\Models\Funnel;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if(User::where('email', 'williamkillerca@hotmail.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'williamkillerca@hotmail.com',
                'password' => bcrypt('admin1994')
            ]);

            $this->command->info('Admin user created successfully!');
        }

        // Get the Chatwoot account ID from the configuration
        $account_id = config('services.chatwoot.account_id');

        if(!$account_id) return;

        // Create Funnel and Stages about Seller
        DB::beginTransaction();

        try {
            $funnel_with_stages = [
                'account_id' => $account_id,
                'name' => 'Funil de Vendas',
                'color' => '#3490dc',
                'is_public' => true,
                'order' => 1,
            ];

            $funnel = Funnel::create($funnel_with_stages);

            $stages = [
                ['name' => 'Lead Capturado', 'order' => 1, 'color' => '#3490dc'],
                ['name' => 'Contactado', 'order' => 2, 'color' => '#ffed4a'],
                ['name' => 'Negociação', 'order' => 3, 'color' => '#f66d9b'],
                ['name' => 'Fechado', 'order' => 4, 'color' => '#38c172'],
            ];

            foreach ($stages as $stage_data) {
                $funnel->stages()->create($stage_data);
            }

            DB::commit();

            $this->command->info('Funnel with stages seeded successfully!');

        } catch (\Throwable $th) {
            DB::rollBack();

            $this->command->error('Error seeding funnel with stages: ' . $th->getMessage());
        }
    }
}
