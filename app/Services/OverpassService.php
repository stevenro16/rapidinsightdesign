<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OverpassService
{
    private const ENDPOINTS = [
        'https://overpass-api.de/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
    ];

    private const QUERY_TIMEOUT = 60;  // Overpass server-side [timeout:]
    private const HTTP_TIMEOUT  = 75;  // client-side, slightly longer

    private const AMENITIES = 'restaurant|cafe|fast_food|bar|pub|ice_cream|dentist|doctors|clinic|veterinary|pharmacy|car_repair|car_wash|gym|childcare|driving_school|music_school|language_school';
    private const LEISURE   = 'fitness_centre|spa|bowling_alley|escape_game';
    private const TOURISM   = 'hotel|motel|guest_house';

    /**
     * @return array{businesses: array<int, array<string, mixed>>, total_raw: int}
     */
    public function searchBusinesses(float $lat, float $lng, int $radiusM): array
    {
        $elements = $this->post($this->buildQuery($lat, $lng, $radiusM));

        $businesses = [];
        foreach ($elements as $element) {
            $normalized = $this->normalize($element);
            if ($normalized !== null) {
                $normalized['presence_score'] = $this->computeScore($normalized);
                $businesses[] = $normalized;
            }
        }

        return ['businesses' => $businesses, 'total_raw' => count($elements)];
    }

    private function buildQuery(float $lat, float $lng, int $radiusM): string
    {
        $around   = sprintf('around:%d,%F,%F', $radiusM, $lat, $lng);
        $timeout  = self::QUERY_TIMEOUT;
        $amenity  = self::AMENITIES;
        $leisure  = self::LEISURE;
        $tourism  = self::TOURISM;

        return <<<QL
        [out:json][timeout:{$timeout}];
        (
          nwr["name"]["shop"]({$around});
          nwr["name"]["craft"]({$around});
          nwr["name"]["office"]({$around});
          nwr["name"]["healthcare"]({$around});
          nwr["name"]["amenity"~"^({$amenity})$"]({$around});
          nwr["name"]["leisure"~"^({$leisure})$"]({$around});
          nwr["name"]["tourism"~"^({$tourism})$"]({$around});
        );
        out center;
        QL;
    }

    /** @return array<int, array<string, mixed>> raw Overpass elements */
    private function post(string $query): array
    {
        $lastError = null;

        foreach (self::ENDPOINTS as $endpoint) {
            try {
                // Overpass returns 406 without a descriptive User-Agent
                $response = Http::asForm()
                    ->withHeaders([
                        'User-Agent' => 'RapidInsightDesigns-ProspectFinder/1.0',
                        'Accept'     => 'application/json',
                    ])
                    ->timeout(self::HTTP_TIMEOUT)
                    ->post($endpoint, ['data' => $query]);

                if ($response->failed()) {
                    $lastError = "HTTP {$response->status()} from {$endpoint}";
                    continue;
                }

                $json = $response->json();
                if (!is_array($json) || !array_key_exists('elements', $json)) {
                    $lastError = "Unexpected response from {$endpoint}";
                    continue;
                }

                return $json['elements'];
            } catch (ConnectionException $e) {
                $lastError = $e->getMessage();
            }
        }

        throw new RuntimeException("All Overpass endpoints failed: {$lastError}");
    }

    /** @return array<string, mixed>|null null = skip (unnamed, no coords, or chain) */
    private function normalize(array $element): ?array
    {
        $tags = $element['tags'] ?? null;
        if (!$tags || empty($tags['name'])) {
            return null;
        }

        // Chain businesses are tagged with brand — user wants independent SMBs only
        if (isset($tags['brand']) || isset($tags['brand:wikidata'])) {
            return null;
        }

        $lat = $element['lat'] ?? $element['center']['lat'] ?? null;
        $lng = $element['lon'] ?? $element['center']['lon'] ?? null;
        if ($lat === null || $lng === null) {
            return null;
        }

        $social = [];
        foreach (['facebook', 'instagram', 'twitter', 'youtube', 'tiktok'] as $network) {
            $value = $tags["contact:{$network}"] ?? $tags[$network] ?? null;
            if ($value) {
                $social[$network] = $value;
            }
        }

        return [
            'osm_type' => $element['type'],
            'osm_id'   => $element['id'],
            'name'     => $tags['name'],
            'category' => $this->category($tags),
            'lat'      => (float) $lat,
            'lng'      => (float) $lng,
            'address'  => $this->address($tags),
            'phone'    => $tags['phone'] ?? $tags['contact:phone'] ?? null,
            'website'  => $tags['website'] ?? $tags['contact:website'] ?? null,
            'email'    => $tags['email'] ?? $tags['contact:email'] ?? null,
            'social'   => $social ?: null,
            'osm_tags' => $tags,
        ];
    }

    public function computeScore(array $normalized): int
    {
        return min(100,
            (!empty($normalized['website']) ? 50 : 0)
            + min(count($normalized['social'] ?? []) * 10, 30)
            + (!empty($normalized['email']) ? 10 : 0)
            + (!empty($normalized['phone']) ? 10 : 0)
        );
    }

    private function category(array $tags): ?string
    {
        foreach (['shop', 'amenity', 'craft', 'office', 'healthcare', 'leisure', 'tourism'] as $key) {
            if (!empty($tags[$key]) && $tags[$key] !== 'yes') {
                return Str::headline($tags[$key]);
            }
        }

        return null;
    }

    private function address(array $tags): ?string
    {
        $street = trim(($tags['addr:housenumber'] ?? '') . ' ' . ($tags['addr:street'] ?? ''));
        $parts  = array_filter([$street, $tags['addr:city'] ?? null]);

        return $parts ? implode(', ', $parts) : null;
    }
}
