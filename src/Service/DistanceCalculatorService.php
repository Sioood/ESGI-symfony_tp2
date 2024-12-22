<?php

namespace App\Service;

class DistanceCalculatorService
{
    private const EARTH_RADIUS = 6371; // Rayon de la Terre en km

    public function calculateDistance(float $startLatitude, float $startLongitude, float $endLatitude, float $endLongitude): float
    {
        $startLatitudeRadians = deg2rad($startLatitude);
        $startLongitudeRadians = deg2rad($startLongitude);
        $endLatitudeRadians = deg2rad($endLatitude);
        $endLongitudeRadians = deg2rad($endLongitude);

        $latitudeDifference = $endLatitudeRadians - $startLatitudeRadians;
        $longitudeDifference = $endLongitudeRadians - $startLongitudeRadians;

        $haversineTerm = sin($latitudeDifference/2) * sin($latitudeDifference/2) + cos($startLatitudeRadians) * cos($endLatitudeRadians) * sin($longitudeDifference/2) * sin($longitudeDifference/2);
        $angularDistance  = 2 * atan2(sqrt($haversineTerm), sqrt(1-$haversineTerm));

        return self::EARTH_RADIUS * $angularDistance ;
    }
}
