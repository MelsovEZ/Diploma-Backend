<?php

namespace App\Services;

use App\Models\City\City;
use App\Models\District\District;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GisService
{
    /**
     * @throws ConnectionException
     */
    public function getCoordinatesFromAddress($address, $cityId, $districtId): ?array
    {
        $apiKey = env('GIS_API_KEY');

        $city = City::find($cityId);
        $district = District::find($districtId);

        $fullAddress = $address . ', ' . $district->name . ', ' . $city->name . ', Казахстан';

        $url = 'https://catalog.api.2gis.com/3.0/items/geocode';

        $response = Http::get($url, [
            'q' => $address,
            'key' => $apiKey,
            'fields' => 'items.point',
        ]);

         if ($response->successful()) {
             $data = $response->json();

             if (isset($data['result']['items'][0])) {
                 $latitude = $data['result']['items'][0]['point']['lat'];
                 $longitude = $data['result']['items'][0]['point']['lon'];

                 return ['latitude' => $latitude, 'longitude' => $longitude];
             }
         }

        return null;
    }
}
