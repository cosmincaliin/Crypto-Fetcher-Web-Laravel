<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;

class CryptocurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Verificar si la tabla existe
        if (!Schema::hasTable('cryptocurrencies')) {
            // Si no existe, crear la tabla
            Schema::create('cryptocurrencies', function ($table) {
                // Definir la estructura de la tabla aquí, por ejemplo:
                $table->id();
                $table->string('external_id');
                $table->string('name');
                // ... Agregar más columnas según sea necesario
                $table->timestamps();
            });
        }

        $apiKey = config('services.coinmarketcap.api_key');
        $endpoint = config('services.coinmarketcap.base_uri') . 'cryptocurrency/listings/latest';

        $response = Http::withHeaders([
            'X-CMC_PRO_API_KEY' => $apiKey,
        ])->get($endpoint);

        if ($response->successful()) {
            $cryptocurrencies = $response->json('data');

            foreach ($cryptocurrencies as $crypto) {
                DB::table('cryptocurrencies')->updateOrInsert(
                    ['external_id' => $crypto['id']],
                    [
                        'name' => $crypto['name'],
                        'symbol' => $crypto['symbol'],
                        'slug' => $crypto['slug'],
                        'description' => $crypto['description'] ?? null,
                        'logo' => $crypto['logo'],
                        'urls' => json_encode($crypto['urls']),
                        'date_added' => date('Y-m-d', strtotime($crypto['date_added'])),
                        'date_launched' => date('Y-m-d', strtotime($crypto['date_launched'])),
                        'tags' => json_encode($crypto['tags']),
                        'platform' => $crypto['platform'],
                        'category' => $crypto['category'],
                        'infinite_supply' => isset($crypto['infinite_supply']) && $crypto['infinite_supply'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
