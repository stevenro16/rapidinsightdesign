<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebsiteScanService
{
    private const HTTP_TIMEOUT = 12;
    private const MAX_PAGES     = 3;       // homepage + up to 2 contact-ish pages
    private const MAX_BYTES     = 600_000; // cap each page to keep parsing fast

    /** Common contact page paths to try after the homepage. */
    private const CONTACT_PATHS = [
        '/contact', '/contact-us', '/contactus', '/about', '/about-us',
    ];

    /**
     * Crawl a business website and extract contact details.
     *
     * @return array{emails: string[], phones: string[], names: string[], social: array<string,string>, pages_scanned: int}
     */
    public function scan(string $website): array
    {
        $base = $this->normalizeUrl($website);
        if (!$base) {
            return $this->empty();
        }

        $emails  = [];
        $phones  = [];
        $names   = [];
        $social  = [];
        $scanned = 0;

        foreach ($this->candidateUrls($base) as $url) {
            if ($scanned >= self::MAX_PAGES) {
                break;
            }

            $html = $this->fetch($url);
            if ($html === null) {
                continue;
            }
            $scanned++;

            $emails = array_merge($emails, $this->extractEmails($html));
            $phones = array_merge($phones, $this->extractPhones($html));
            $names  = array_merge($names, $this->extractNames($html));
            $social = array_merge($this->extractSocial($html), $social);
        }

        return [
            'emails'        => $this->dedupe($emails, 5),
            'phones'        => $this->dedupe($phones, 5),
            'names'         => $this->dedupe($names, 5),
            'social'        => $social,
            'pages_scanned' => $scanned,
        ];
    }

    private function candidateUrls(string $base): array
    {
        return array_merge(
            [$base],
            array_map(fn ($p) => rtrim($base, '/') . $p, self::CONTACT_PATHS),
        );
    }

    private function fetch(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; RapidInsightProspectScanner/1.0)',
                'Accept'     => 'text/html,application/xhtml+xml',
            ])
                ->timeout(self::HTTP_TIMEOUT)
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if ($response->failed()) {
                return null;
            }

            $contentType = $response->header('Content-Type');
            if ($contentType && !Str::contains($contentType, ['text/html', 'application/xhtml'])) {
                return null;
            }

            return substr($response->body(), 0, self::MAX_BYTES);
        } catch (ConnectionException) {
            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return string[] */
    private function extractEmails(string $html): array
    {
        $found = [];

        // mailto: links are the most reliable
        if (preg_match_all('/mailto:([^"\'?\s>]+)/i', $html, $m)) {
            $found = array_merge($found, $m[1]);
        }

        // bare emails in text
        if (preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $html, $m)) {
            $found = array_merge($found, $m[0]);
        }

        return array_filter($found, function ($email) {
            $email = strtolower($email);
            // drop asset filenames misread as emails (e.g. icon@2x.png)
            if (Str::endsWith($email, ['.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg'])) {
                return false;
            }
            // drop obvious placeholders
            return !Str::contains($email, ['example.com', 'sentry.io', 'wixpress.com', '@2x', '@3x']);
        });
    }

    /** @return string[] */
    private function extractPhones(string $html): array
    {
        $found = [];

        // tel: links first
        if (preg_match_all('/tel:([+0-9().\-\s]{7,})/i', $html, $m)) {
            foreach ($m[1] as $raw) {
                if ($formatted = $this->formatPhone($raw)) {
                    $found[] = $formatted;
                }
            }
        }

        // US-style phone numbers in visible text
        if (preg_match_all('/(\+?1[\s.\-]?)?\(?\d{3}\)?[\s.\-]?\d{3}[\s.\-]?\d{4}/', $html, $m)) {
            foreach ($m[0] as $raw) {
                if ($formatted = $this->formatPhone($raw)) {
                    $found[] = $formatted;
                }
            }
        }

        return $found;
    }

    private function formatPhone(string $raw): ?string
    {
        $digits = preg_replace('/\D/', '', $raw);

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return null;
        }

        // Valid NANP numbers: area code and exchange code both start with 2-9
        if (!preg_match('/^[2-9]\d{2}[2-9]\d{6}$/', $digits)) {
            return null;
        }

        // Reject obvious placeholders: 6+ of the same digit (e.g. 333-333-3333, 555-555-5555)
        if (preg_match('/(\d)\1{5,}/', $digits)) {
            return null;
        }

        return sprintf('(%s) %s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 4),
        );
    }

    /**
     * Heuristic owner/contact name extraction — looks for "Owner: Jane Doe",
     * "Contact Jane Doe", "Dr. Jane Doe", etc.
     *
     * @return string[]
     */
    private function extractNames(string $html): array
    {
        $text = $this->visibleText($html);
        $found = [];

        $patterns = [
            // "Owner: Jane Doe" / "Owner Jane Doe" / "Manager - Jane Doe"
            '/\b(?:owner|founder|proprietor|manager|president|ceo|principal|director)\b[:\s\-]+([A-Z][a-z]+(?:\s+[A-Z]\.?)?\s+[A-Z][a-z]+)/',
            // "Jane Doe, Owner"
            '/\b([A-Z][a-z]+(?:\s+[A-Z]\.?)?\s+[A-Z][a-z]+),?\s+(?:is the\s+)?(?:owner|founder|proprietor)\b/',
            // "Dr. Jane Doe"
            '/\b(Dr\.?\s+[A-Z][a-z]+\s+[A-Z][a-z]+)\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $m)) {
                $found = array_merge($found, $m[1]);
            }
        }

        return array_filter(array_map('trim', $found), fn ($n) => strlen($n) >= 5 && strlen($n) <= 50);
    }

    /** @return array<string,string> */
    private function extractSocial(string $html): array
    {
        $networks = [
            'facebook'  => '/https?:\/\/(?:www\.)?facebook\.com\/[A-Za-z0-9._\-\/]+/i',
            'instagram' => '/https?:\/\/(?:www\.)?instagram\.com\/[A-Za-z0-9._\-\/]+/i',
            'twitter'   => '/https?:\/\/(?:www\.)?(?:twitter|x)\.com\/[A-Za-z0-9._\-\/]+/i',
            'linkedin'  => '/https?:\/\/(?:www\.)?linkedin\.com\/[A-Za-z0-9._\-\/]+/i',
            'youtube'   => '/https?:\/\/(?:www\.)?youtube\.com\/[A-Za-z0-9._\-\/@]+/i',
            'tiktok'    => '/https?:\/\/(?:www\.)?tiktok\.com\/[A-Za-z0-9._\-\/@]+/i',
        ];

        $social = [];
        foreach ($networks as $name => $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $url = $m[0];
                // skip generic share/intent links
                if (!Str::contains(strtolower($url), ['sharer', 'share?', 'intent', '/plugins/'])) {
                    $social[$name] = $url;
                }
            }
        }

        return $social;
    }

    private function visibleText(string $html): string
    {
        $html = preg_replace('/<(script|style|noscript)\b[^>]*>.*?<\/\1>/is', ' ', $html) ?? $html;
        $text = strip_tags($html);

        return html_entity_decode(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    private function normalizeUrl(string $website): ?string
    {
        $website = trim($website);
        if ($website === '') {
            return null;
        }

        if (!Str::startsWith($website, ['http://', 'https://'])) {
            $website = 'https://' . $website;
        }

        $parts = parse_url($website);
        if (!isset($parts['host'])) {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'https';

        return $scheme . '://' . $parts['host'] . ($parts['path'] ?? '');
    }

    /** @param string[] $items */
    private function dedupe(array $items, int $limit): array
    {
        $seen = [];
        foreach ($items as $item) {
            $key = strtolower(trim($item));
            if ($key !== '' && !isset($seen[$key])) {
                $seen[$key] = trim($item);
            }
        }

        return array_slice(array_values($seen), 0, $limit);
    }

    private function empty(): array
    {
        return ['emails' => [], 'phones' => [], 'names' => [], 'social' => [], 'pages_scanned' => 0];
    }
}
