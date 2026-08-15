<?php

namespace App\Services;

use DateTimeZone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * Dado lat/lng, devuelve ['country' => 'Argentina', 'country_code' => 'AR', 'timezone' => 'America/Argentina/Buenos_Aires']
     * Usa Nominatim (OpenStreetMap), gratuito y sin API key, respetando su política de uso (User-Agent obligatorio).
     * Si falla, devuelve null y el sistema simplemente no muestra la hora del "otro país".
     */
    public function resolve(float $lat, float $lng): ?array
    {
        try {
            $response = Http::withHeaders([
                    'User-Agent' => 'EncuestasApp/1.0 (contacto@tu-dominio.pe)',
                ])
                ->timeout(5)
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng,
                    'zoom' => 3, // nivel país, no necesitamos más detalle
                ]);

            if (!$response->ok()) {
                return null;
            }

            $data = $response->json();
            $countryCode = strtoupper($data['address']['country_code'] ?? '');
            $countryName = $data['address']['country'] ?? null;

            if (!$countryCode) {
                return null;
            }

            $identifiers = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $countryCode);
            $timezone = $identifiers[0] ?? null;

            return [
                'country' => $countryName,
                'country_code' => $countryCode,
                'timezone' => $timezone,
            ];
        } catch (\Throwable $e) {
            Log::warning('GeoLocationService: no se pudo resolver ubicación', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
