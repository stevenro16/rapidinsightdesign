<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\ProspectNote;
use App\Models\ProspectSearchArea;
use App\Services\OverpassService;
use App\Services\WebsiteScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProspectController extends Controller
{
    /** Redlands, CA */
    private const DEFAULT_CENTER = [34.0556, -117.1825];

    public function index(): View
    {
        return view('admin.prospects.index', [
            'center'   => self::DEFAULT_CENTER,
            'statuses' => Prospect::STATUSES,
        ]);
    }

    public function search(Request $request, OverpassService $overpass): JsonResponse
    {
        $data = $request->validate([
            'lat'      => ['required', 'numeric', 'between:-90,90'],
            'lng'      => ['required', 'numeric', 'between:-180,180'],
            'radius_m' => ['required', 'integer', 'between:1609,40234'], // 1-25 miles
        ]);

        set_time_limit(120); // Overpass can take 30-60s; artisan serve enforces max_execution_time

        try {
            $result = $overpass->searchBusinesses($data['lat'], $data['lng'], $data['radius_m']);
        } catch (RuntimeException $e) {
            report($e);
            return response()->json([
                'message' => 'OpenStreetMap is busy or unreachable. Try again in a minute, or shrink the search radius.',
            ], 503);
        }

        $imported = 0;
        $updated  = 0;

        $area = DB::transaction(function () use ($data, $result, &$imported, &$updated) {
            foreach ($result['businesses'] as $business) {
                $prospect = Prospect::firstOrNew([
                    'osm_type' => $business['osm_type'],
                    'osm_id'   => $business['osm_id'],
                ]);

                $prospect->exists ? $updated++ : $imported++;

                // Never touch status on update — re-searching refreshes OSM
                // data without resetting the pipeline
                $prospect->fill([
                    'name'           => $business['name'],
                    'category'       => $business['category'],
                    'lat'            => $business['lat'],
                    'lng'            => $business['lng'],
                    'address'        => $business['address'],
                    'phone'          => $business['phone'],
                    'website'        => $business['website'],
                    'email'          => $business['email'],
                    'social'         => $business['social'],
                    'osm_tags'       => $business['osm_tags'],
                    'presence_score' => $business['presence_score'],
                    'last_synced_at' => now(),
                ])->save();
            }

            return ProspectSearchArea::create([
                'lat'           => $data['lat'],
                'lng'           => $data['lng'],
                'radius_m'      => $data['radius_m'],
                'results_count' => count($result['businesses']),
                'new_count'     => $imported,
            ]);
        });

        return response()->json([
            'imported'    => $imported,
            'updated'     => $updated,
            'total_found' => count($result['businesses']),
            'area'        => $area,
        ]);
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'prospects' => Prospect::withCount('notes')->get([
                'id', 'name', 'category', 'lat', 'lng', 'address', 'phone',
                'website', 'email', 'social', 'presence_score', 'status',
                'scan_data', 'scanned_at', 'created_at',
            ]),
            'areas' => ProspectSearchArea::all(),
        ]);
    }

    public function show(Prospect $prospect): JsonResponse
    {
        return response()->json($prospect->load('notes'));
    }

    /**
     * Crawl the prospect's website and extract contact info
     * (emails, phones, owner names, social links).
     */
    public function scan(Prospect $prospect, WebsiteScanService $scanner): JsonResponse
    {
        if (empty($prospect->website)) {
            return response()->json([
                'message' => 'This business has no website to scan.',
            ], 422);
        }

        set_time_limit(90);

        $scan = $scanner->scan($prospect->website);

        $prospect->update([
            'scan_data'  => $scan,
            'scanned_at' => now(),
        ]);

        return response()->json([
            'scan_data'  => $prospect->scan_data,
            'scanned_at' => $prospect->scanned_at,
        ]);
    }

    public function updateStatus(Request $request, Prospect $prospect): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Prospect::STATUSES)],
        ]);

        $prospect->update($data);

        return response()->json($prospect);
    }

    public function storeNote(Request $request, Prospect $prospect): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $note = $prospect->notes()->create($data);

        return response()->json($note, 201);
    }

    public function destroy(Prospect $prospect): JsonResponse
    {
        $prospect->delete();

        return response()->json(['ok' => true]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Prospect::withCount('notes')->orderBy('presence_score')->orderBy('name');

        if ($statuses = array_filter(explode(',', $request->query('status', '')))) {
            $query->whereIn('status', $statuses);
        }
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }
        if ($band = $request->query('band')) {
            match ($band) {
                'low'    => $query->where('presence_score', '<', 30),
                'medium' => $query->whereBetween('presence_score', [30, 59]),
                'high'   => $query->where('presence_score', '>=', 60),
                default  => null,
            };
        }
        if ($q = $request->query('q')) {
            $query->where('name', 'like', "%{$q}%");
        }

        $filename = 'prospects-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Name', 'Category', 'Status', 'Presence Score', 'Phone', 'Email', 'Website', 'Address',
                'Found Emails', 'Found Phones', 'Found Names', 'Lat', 'Lng', 'Notes', 'Added',
            ]);

            $query->chunk(500, function ($prospects) use ($out) {
                foreach ($prospects as $p) {
                    $scan = $p->scan_data ?? [];
                    fputcsv($out, [
                        $p->name, $p->category, $p->status, $p->presence_score,
                        $p->phone, $p->email, $p->website, $p->address,
                        implode(' | ', $scan['emails'] ?? []),
                        implode(' | ', $scan['phones'] ?? []),
                        implode(' | ', $scan['names'] ?? []),
                        $p->lat, $p->lng, $p->notes_count, $p->created_at->format('Y-m-d'),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
