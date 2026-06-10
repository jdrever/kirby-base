<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

/**
 * Geodetic utility methods: haversine distance, bounding boxes, and Irish National Grid conversion.
 *
 * Irish grid ref → WGS84 uses the TM75 (Airy Modified ellipsoid) datum with
 * standard Helmert transformation parameters from Ordnance Survey Ireland.
 */
class GeoHelper
{
    private const float EARTH_RADIUS_MILES = 3958.8;

    /** Miles per degree of latitude (approximate) */
    private const float MILES_PER_DEGREE_LAT = 69.0;

    // ── Haversine ─────────────────────────────────────────────────────────────

    /**
     * Returns the great-circle distance in miles between two WGS84 points.
     *
     * @param float $lat1 Latitude of point 1 (degrees)
     * @param float $lon1 Longitude of point 1 (degrees)
     * @param float $lat2 Latitude of point 2 (degrees)
     * @param float $lon2 Longitude of point 2 (degrees)
     * @return float Distance in miles
     */
    public static function haversineDistanceMiles(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $lat1R = deg2rad($lat1);
        $lat2R = deg2rad($lat2);
        $dLat  = deg2rad($lat2 - $lat1);
        $dLon  = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos($lat1R) * cos($lat2R) * sin($dLon / 2) ** 2;

        return 2 * self::EARTH_RADIUS_MILES * asin(sqrt($a));
    }

    /**
     * Returns a lat/lon bounding box large enough to contain all points within
     * the given radius of the centre. The box is an over-approximation; use
     * haversineDistanceMiles() for exact filtering after the SQL pre-filter.
     *
     * @param float $lat    Centre latitude (degrees)
     * @param float $lon    Centre longitude (degrees)
     * @param float $radiusMiles Radius in miles
     * @return array{minLat: float, maxLat: float, minLon: float, maxLon: float}
     */
    public static function boundingBoxForRadiusMiles(
        float $lat,
        float $lon,
        float $radiusMiles
    ): array {
        $deltaLat = $radiusMiles / self::MILES_PER_DEGREE_LAT;

        // Longitude degrees per mile depends on latitude
        $cosLat    = cos(deg2rad($lat));
        $deltaLon  = $cosLat > 0.0 ? $radiusMiles / (self::MILES_PER_DEGREE_LAT * $cosLat) : 360.0;

        return [
            'minLat' => $lat - $deltaLat,
            'maxLat' => $lat + $deltaLat,
            'minLon' => $lon - $deltaLon,
            'maxLon' => $lon + $deltaLon,
        ];
    }

    // ── Irish National Grid → WGS84 ───────────────────────────────────────────

    /**
     * Converts an Irish National Grid reference (TM75) to WGS84 lat/lng.
     *
     * Accepts 2–10 digit references (e.g. "O15743412" for 1m precision).
     * Returns null if the reference cannot be parsed.
     *
     * @param string $gridRef Irish National Grid reference (e.g. "O15743412")
     * @return array{0: float, 1: float}|null [latitude, longitude] in WGS84 degrees, or null
     */
    public static function irishGridRefToLatLng(string $gridRef): ?array
    {
        $en = self::parseIrishGridRef($gridRef);
        if ($en === null) {
            return null;
        }

        [$easting, $northing] = $en;
        [$latTM75, $lonTM75]  = self::irishGridEastingNorthingToTM75($easting, $northing);

        return self::tm75ToWGS84($latTM75, $lonTM75);
    }

    /**
     * Parses an Irish National Grid reference string into metric easting and northing.
     *
     * The letter covers a 100km square; the digits (even count, 2–10) give the
     * position within that square at 50km, 10km, 1km, 100m, or 1m precision.
     *
     * @param string $gridRef Grid reference (e.g. "O15743412")
     * @return array{0: float, 1: float}|null [easting, northing] in metres, or null on failure
     */
    public static function parseIrishGridRef(string $gridRef): ?array
    {
        $gridRef = strtoupper(trim($gridRef));

        if (!preg_match('/^([A-HJ-Z])(\d+)$/', $gridRef, $m)) {
            return null;
        }

        $digits = $m[2];
        $len    = strlen($digits);

        if ($len % 2 !== 0 || $len < 2 || $len > 10) {
            return null;
        }

        $half   = intdiv($len, 2);
        $scale  = 10 ** (5 - $half);       // scale digits to 1m within the 100km square
        $eLocal = (int) substr($digits, 0, $half) * $scale;
        $nLocal = (int) substr($digits, $half) * $scale;

        // Letter grid: ABCDEFGHJKLMNOPQRSTUVWXYZ (no I), north→south, west→east, 5×5
        $letters = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
        $pos     = strpos($letters, $m[1]);
        if ($pos === false) {
            return null;
        }

        $col     = $pos % 5;
        $rowFromN = intdiv($pos, 5);         // 0 = northernmost row

        $easting  = (float) ($col * 100000 + $eLocal);
        $northing = (float) ((4 - $rowFromN) * 100000 + $nLocal);

        return [$easting, $northing];
    }

    /**
     * Inverse Transverse Mercator: Irish National Grid easting/northing → geographic on TM75.
     *
     * Uses the Airy Modified ellipsoid and the Irish Grid projection parameters.
     *
     * @param float $E Easting in metres
     * @param float $N Northing in metres
     * @return array{0: float, 1: float} [latitude, longitude] in degrees on TM75
     */
    private static function irishGridEastingNorthingToTM75(float $E, float $N): array
    {
        // Airy Modified ellipsoid
        $a  = 6377340.189;
        $b  = 6356034.447;
        $f0 = 1.000035;                // scale factor at true origin
        $lat0 = deg2rad(53.5);         // latitude of true origin
        $lon0 = deg2rad(-8.0);         // central meridian
        $E0  = 200000.0;               // false easting
        $N0  = 250000.0;               // false northing

        $e2 = 1.0 - ($b ** 2) / ($a ** 2);
        $n  = ($a - $b) / ($a + $b);

        // Iterate to find latitude from northing
        $Np  = $N - $N0;
        $lat = $lat0 + $Np / ($a * $f0);

        do {
            $M          = self::meridionalArc($b, $f0, $n, $lat0, $lat);
            $correction = ($Np - $M) / ($a * $f0);
            $lat       += $correction;
        } while (abs($correction) > 1e-12);

        $sinLat = sin($lat);
        $cosLat = cos($lat);
        $tanLat = tan($lat);
        $tan2   = $tanLat ** 2;
        $tan4   = $tanLat ** 4;
        $tan6   = $tanLat ** 6;

        $nu   = $a * $f0 / sqrt(1.0 - $e2 * $sinLat ** 2);
        $rho  = $a * $f0 * (1.0 - $e2) / (1.0 - $e2 * $sinLat ** 2) ** 1.5;
        $eta2 = $nu / $rho - 1.0;

        $VII  = $tanLat / (2.0  * $rho * $nu);
        $VIII = $tanLat / (24.0 * $rho * $nu ** 3) * (5.0 + 3.0 * $tan2 + $eta2 - 9.0 * $eta2 * $tan2);
        $IX   = $tanLat / (720.0 * $rho * $nu ** 5) * (61.0 + 90.0 * $tan2 + 45.0 * $tan4);
        $X    = 1.0 / ($cosLat * $nu);
        $XI   = 1.0 / ($cosLat * 6.0   * $nu ** 3) * ($nu / $rho + 2.0 * $tan2);
        $XII  = 1.0 / ($cosLat * 120.0 * $nu ** 5) * (5.0 + 28.0 * $tan2 + 24.0 * $tan4);
        $XIIA = 1.0 / ($cosLat * 5040.0 * $nu ** 7) * (61.0 + 662.0 * $tan2 + 1320.0 * $tan4 + 720.0 * $tan6);

        $dE  = $E - $E0;

        $latResult = $lat
            - $VII  * $dE ** 2
            + $VIII * $dE ** 4
            - $IX   * $dE ** 6;

        $lonResult = $lon0
            + $X    * $dE
            - $XI   * $dE ** 3
            + $XII  * $dE ** 5
            - $XIIA * $dE ** 7;

        return [rad2deg($latResult), rad2deg($lonResult)];
    }

    /**
     * Meridional arc distance from latitude of origin to given latitude.
     *
     * @param float $b   Semi-minor axis
     * @param float $f0  Scale factor
     * @param float $n   Third flattening (a-b)/(a+b)
     * @param float $lat0 Latitude of true origin (radians)
     * @param float $lat  Current latitude (radians)
     * @return float Meridional arc in metres
     */
    private static function meridionalArc(
        float $b,
        float $f0,
        float $n,
        float $lat0,
        float $lat
    ): float {
        return $b * $f0 * (
            (1.0 + $n + 1.25 * $n ** 2 + 1.25 * $n ** 3)
                * ($lat - $lat0)
            - (3.0 * $n + 3.0 * $n ** 2 + 2.625 * $n ** 3)
                * sin($lat - $lat0) * cos($lat + $lat0)
            + (1.875 * $n ** 2 + 1.875 * $n ** 3)
                * sin(2.0 * ($lat - $lat0)) * cos(2.0 * ($lat + $lat0))
            - (35.0 / 24.0 * $n ** 3)
                * sin(3.0 * ($lat - $lat0)) * cos(3.0 * ($lat + $lat0))
        );
    }

    /**
     * Converts geographic coordinates on the TM75 datum (Airy Modified ellipsoid)
     * to WGS84 using a 7-parameter Helmert transformation.
     *
     * Helmert parameters from Ordnance Survey Ireland.
     *
     * @param float $lat Latitude in degrees on TM75
     * @param float $lon Longitude in degrees on TM75
     * @return array{0: float, 1: float} [latitude, longitude] in WGS84 degrees
     */
    private static function tm75ToWGS84(float $lat, float $lon): array
    {
        // TM75: Airy Modified ellipsoid
        $a  = 6377340.189;
        $b  = 6356034.447;
        $e2 = 1.0 - ($b ** 2) / ($a ** 2);

        $latR = deg2rad($lat);
        $lonR = deg2rad($lon);
        $nu   = $a / sqrt(1.0 - $e2 * sin($latR) ** 2);

        // Geographic → geocentric Cartesian (height assumed 0)
        $X = $nu * cos($latR) * cos($lonR);
        $Y = $nu * cos($latR) * sin($lonR);
        $Z = $nu * (1.0 - $e2) * sin($latR);

        // Helmert parameters: TM75 → WGS84 (OSi)
        $tx = -482.530;
        $ty =  130.596;
        $tz = -564.557;
        $rx = deg2rad(-1.042 / 3600.0);
        $ry = deg2rad(-0.214 / 3600.0);
        $rz = deg2rad(-0.631 / 3600.0);
        $s  = 1.0 + (-8.150 / 1e6);

        $X2 = $tx + $s * ($X         - $rz * $Y + $ry * $Z);
        $Y2 = $ty + $s * ($rz * $X   + $Y        - $rx * $Z);
        $Z2 = $tz + $s * (-$ry * $X  + $rx * $Y  + $Z);

        // WGS84 ellipsoid
        $a2  = 6378137.000;
        $b2  = 6356752.3142;
        $e22 = 1.0 - ($b2 ** 2) / ($a2 ** 2);
        $p   = sqrt($X2 ** 2 + $Y2 ** 2);

        // Geocentric Cartesian → geographic (iterative)
        $lat2 = atan2($Z2, $p * (1.0 - $e22));
        do {
            $prev  = $lat2;
            $nu2   = $a2 / sqrt(1.0 - $e22 * sin($lat2) ** 2);
            $lat2  = atan2($Z2 + $e22 * $nu2 * sin($lat2), $p);
        } while (abs($lat2 - $prev) > 1e-12);

        $lon2 = atan2($Y2, $X2);

        return [rad2deg($lat2), rad2deg($lon2)];
    }
}
