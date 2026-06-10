<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\helpers;

use PHPUnit\Framework\TestCase;
use BSBI\WebBase\helpers\GeoHelper;

/**
 * Tests for GeoHelper: haversine distance, bounding box, and Irish grid ref conversion.
 */
final class GeoHelperTest extends TestCase
{
    // ── Haversine ─────────────────────────────────────────────────────────────

    public function testHaversineKnownLondonToEdinburgh(): void
    {
        // London (51.5074, -0.1278) to Edinburgh (55.9533, -3.1883) ≈ 332 miles
        $miles = GeoHelper::haversineDistanceMiles(51.5074, -0.1278, 55.9533, -3.1883);
        $this->assertEqualsWithDelta(332.0, $miles, 5.0);
    }

    public function testHaversineZeroDistanceSamePoint(): void
    {
        $miles = GeoHelper::haversineDistanceMiles(51.5, -0.1, 51.5, -0.1);
        $this->assertEqualsWithDelta(0.0, $miles, 0.001);
    }

    public function testHaversineShortDistance(): void
    {
        // About 1 mile north of (51.5, -0.1) is approximately (51.5145, -0.1)
        $miles = GeoHelper::haversineDistanceMiles(51.5, -0.1, 51.5145, -0.1);
        $this->assertEqualsWithDelta(1.0, $miles, 0.1);
    }

    // ── Bounding box ──────────────────────────────────────────────────────────

    public function testBoundingBoxLatitudesAreSymmetric(): void
    {
        $box = GeoHelper::boundingBoxForRadiusMiles(51.5, -0.1, 100.0);
        $this->assertArrayHasKey('minLat', $box);
        $this->assertArrayHasKey('maxLat', $box);
        $this->assertArrayHasKey('minLon', $box);
        $this->assertArrayHasKey('maxLon', $box);

        $this->assertLessThan(51.5, $box['minLat']);
        $this->assertGreaterThan(51.5, $box['maxLat']);
        $this->assertLessThan(-0.1, $box['minLon']);
        $this->assertGreaterThan(-0.1, $box['maxLon']);

        // Latitude span should be roughly symmetric around the centre
        $latSpan = $box['maxLat'] - $box['minLat'];
        $this->assertEqualsWithDelta(
            $box['maxLat'] - 51.5,
            51.5 - $box['minLat'],
            0.001
        );
        $this->assertGreaterThan(0, $latSpan);
    }

    public function testBoundingBoxContainsPointWithinRadius(): void
    {
        $centre = [51.5, -0.1];
        $box = GeoHelper::boundingBoxForRadiusMiles($centre[0], $centre[1], 50.0);

        // A point ~30 miles north should be inside the box
        $nearbyLat = 51.5 + (30.0 / 69.0);
        $this->assertGreaterThanOrEqual($box['minLat'], $nearbyLat);
        $this->assertLessThanOrEqual($box['maxLat'], $nearbyLat);
    }

    // ── Irish grid ref parsing ────────────────────────────────────────────────

    public function testParseValidDublinGridRef(): void
    {
        // O15743412 → E=315740, N=234120
        $result = GeoHelper::parseIrishGridRef('O15743412');
        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(315740.0, $result[0], 5.0);
        $this->assertEqualsWithDelta(234120.0, $result[1], 5.0);
    }

    public function testParseGridRefLowercaseAccepted(): void
    {
        $upper = GeoHelper::parseIrishGridRef('O15743412');
        $lower = GeoHelper::parseIrishGridRef('o15743412');
        $this->assertEquals($upper, $lower);
    }

    public function testParseInvalidLetterIReturnsNull(): void
    {
        $this->assertNull(GeoHelper::parseIrishGridRef('I12345678'));
    }

    public function testParseEmptyStringReturnsNull(): void
    {
        $this->assertNull(GeoHelper::parseIrishGridRef(''));
    }

    public function testParseOddDigitCountReturnsNull(): void
    {
        $this->assertNull(GeoHelper::parseIrishGridRef('O12345'));
    }

    public function testParseTwoLetterPrefixReturnsNull(): void
    {
        $this->assertNull(GeoHelper::parseIrishGridRef('TQ12345678'));
    }

    // ── Irish grid ref → WGS84 ───────────────────────────────────────────────

    public function testIrishGridRefToLatLngDublin(): void
    {
        // D02XY45 postcode → BSBI API returns O15743412
        // Expected approximately 53.34°N, 6.26°W
        $result = GeoHelper::irishGridRefToLatLng('O15743412');
        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(53.34, $result[0], 0.02);
        $this->assertEqualsWithDelta(-6.26, $result[1], 0.02);
    }

    public function testIrishGridRefToLatLngGalway(): void
    {
        // Galway grid ref M29772500 → ~53.27°N, 9.05°W
        $result = GeoHelper::irishGridRefToLatLng('M29772500');
        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(53.27, $result[0], 0.02);
        $this->assertEqualsWithDelta(-9.05, $result[1], 0.03);
    }

    public function testIrishGridRefToLatLngCork(): void
    {
        // T12Y337 Eircode → BSBI API returns W65977114, Cork area ~51.89°N, 8.47°W
        $result = GeoHelper::irishGridRefToLatLng('W65977114');
        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(51.89, $result[0], 0.02);
        $this->assertEqualsWithDelta(-8.47, $result[1], 0.05);
    }

    public function testIrishGridRefToLatLngReturnsNullForInvalidRef(): void
    {
        $this->assertNull(GeoHelper::irishGridRefToLatLng('INVALID'));
        $this->assertNull(GeoHelper::irishGridRefToLatLng(''));
        $this->assertNull(GeoHelper::irishGridRefToLatLng('I12345678'));
    }
}
