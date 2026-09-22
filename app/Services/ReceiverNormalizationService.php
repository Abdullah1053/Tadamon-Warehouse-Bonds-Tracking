<?php

namespace App\Services;

use App\Models\Bond;
use Illuminate\Support\Facades\DB;

class ReceiverNormalizationService
{
    /**
     * Normalize Arabic string for accurate comparison.
     * - Strips tashkeel (diacritics), tatweel (kashida), punctuation.
     * - Standardizes Alef variations (أ, إ, آ, ٱ -> ا).
     * - Standardizes Yaa / Alef Maqsura (ى -> ي).
     * - Standardizes Ta Marbuta / Haa (ة -> ه).
     * - Normalizes multiple spaces into a single space.
     */
    public function normalizeArabic(string $text): string
    {
        // 1. Remove Tashkeel (Arabic diacritics)
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

        // 2. Remove Tatweel / Kashida
        $text = preg_replace('/\x{0640}/u', '', $text);

        // 3. Normalize Alefs
        $text = preg_replace('/[إأآٱ]/u', 'ا', $text);

        // 4. Normalize Alef Maqsura to Yaa
        $text = preg_replace('/ى/u', 'ي', $text);

        // 5. Normalize Ta Marbuta to Haa
        $text = preg_replace('/ة/u', 'ه', $text);

        // 6. Replace non-letter & non-number characters (punctuation, hyphens, brackets) with space
        $text = preg_replace('/[^\p{L}\p{N}]/u', ' ', $text);

        // 7. Collapse multiple spaces and trim
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        // 8. Lowercase for latin characters if present
        return mb_strtolower($text, 'UTF-8');
    }

    /**
     * Strip common business / courtesy prefixes to find the core entity name.
     * e.g. "محلات سعيد الشوافي" -> "سعيد الشوافي", "محطة البدر" -> "البدر"
     */
    public function getCoreEntityName(string $normalized): string
    {
        $prefixes = [
            'محلات', 'محل', 'شركه', 'مؤسسه', 'محطه', 'معرض', 'ورشه', 'مصنع',
            'مركز', 'الاخ', 'السيد', 'الاستاذ', 'مقاولات', 'تجاره', 'مكتب', 'مطرح'
        ];

        $suffixes = [
            'للتجاره', 'للتجارة', 'للمقاولات', 'العامه', 'العامة', 'المحدوده',
            'المحدودة', 'الاستيراد', 'والتصدير', 'للهيدروليك'
        ];

        $tokens = explode(' ', $normalized);

        // Strip prefixes from start
        while (count($tokens) > 1 && in_array($tokens[0], $prefixes, true)) {
            array_shift($tokens);
        }

        // Strip suffixes from end
        while (count($tokens) > 1 && in_array($tokens[count($tokens) - 1], $suffixes, true)) {
            array_pop($tokens);
        }

        // Also remove leading "ال" from the first token if token is long enough
        if (!empty($tokens)) {
            if (mb_substr($tokens[0], 0, 2, 'UTF-8') === 'ال' && mb_strlen($tokens[0], 'UTF-8') > 4) {
                $tokens[0] = mb_substr($tokens[0], 2, null, 'UTF-8');
            }
        }

        return implode(' ', $tokens);
    }

    /**
     * Compute similarity percentage between two names (0 to 100).
     */
    public function calculateSimilarity(string $nameA, string $nameB): float
    {
        $normA = $this->normalizeArabic($nameA);
        $normB = $this->normalizeArabic($nameB);

        // 1. Exact match after standard normalization
        if ($normA === $normB) {
            return 100.0;
        }

        // 2. Exact match after stripping common business prefixes
        $coreA = $this->getCoreEntityName($normA);
        $coreB = $this->getCoreEntityName($normB);

        if (!empty($coreA) && !empty($coreB) && $coreA === $coreB) {
            return 96.0;
        }

        // 3. Check token set overlap / containment
        $tokensA = array_values(array_filter(explode(' ', $normA)));
        $tokensB = array_values(array_filter(explode(' ', $normB)));

        if (!empty($tokensA) && !empty($tokensB)) {
            $intersection = array_intersect($tokensA, $tokensB);
            $union = array_unique(array_merge($tokensA, $tokensB));
            $jaccard = count($intersection) / count($union);

            // If one is completely contained within the other and shares significant tokens (>= 2 tokens)
            $minTokens = min(count($tokensA), count($tokensB));
            if ($minTokens > 0 && count($intersection) === $minTokens && $minTokens >= 2) {
                return 92.0;
            }

            if ($jaccard >= 0.75) {
                return 90.0 * $jaccard;
            }
        }

        // 4. Character similarity on core entities (prevents common prefixes from inflating similarity)
        $lenA = mb_strlen($coreA, 'UTF-8');
        $lenB = mb_strlen($coreB, 'UTF-8');
        if ($lenA === 0 || $lenB === 0) {
            return 0.0;
        }

        // If lengths differ by more than 40%, unlikely to be the same name
        $maxLen = max($lenA, $lenB);
        $minLen = min($lenA, $lenB);
        if ($maxLen > 4 && ($minLen / $maxLen) < 0.6) {
            return 0.0;
        }

        similar_text($coreA, $coreB, $corePercent);

        // For short names (<= 4 chars like "بدر", "نصر"), require Levenshtein <= 1
        if ($maxLen <= 4) {
            $lev = levenshtein($coreA, $coreB);
            if ($lev > 1) {
                return 0.0;
            }
        }

        return (float) $corePercent;
    }

    /**
     * Get all unique receivers from the bonds table with their count.
     * @return array<string, int> Associative array of [receiver_name => bond_count]
     */
    public function getUniqueReceiversWithCounts(): array
    {
        $rows = Bond::select('received_from', DB::raw('COUNT(*) as count'))
            ->whereNotNull('received_from')
            ->where('received_from', '!=', '')
            ->where('received_from', '!=', '--- N/A ---')
            ->where('received_from', '!=', 'مفقود')
            ->where('received_from', '!=', 'ملغي')
            ->groupBy('received_from')
            ->orderByDesc('count')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $name = trim($row->received_from);
            if ($name !== '') {
                $result[$name] = (int) $row->count;
            }
        }

        return $result;
    }

    /**
     * Group similar names into clusters.
     *
     * @param float $threshold Minimum similarity percentage (0-100), default 80%
     * @param string|null $search Optional filter keyword
     * @return array Array of clusters, where each cluster contains variations, bond counts, and suggested target
     */
    public function getClusters(float $threshold = 80.0, ?string $search = null): array
    {
        $receiversWithCounts = $this->getUniqueReceiversWithCounts();
        $names = array_keys($receiversWithCounts);
        $count = count($names);

        if ($count === 0) {
            return [
                'stats' => [
                    'total_unique_names' => 0,
                    'clusters_count' => 0,
                    'affected_bonds' => 0,
                ],
                'clusters' => [],
            ];
        }

        $assigned = [];
        $clusters = [];
        $totalAffectedBonds = 0;

        foreach ($names as $i => $centerName) {
            if (isset($assigned[$centerName])) {
                continue;
            }

            $clusterVariants = [$centerName];

            foreach ($names as $j => $candidateName) {
                if ($i === $j || isset($assigned[$candidateName])) {
                    continue;
                }

                $sim = $this->calculateSimilarity($centerName, $candidateName);
                if ($sim >= $threshold) {
                    $clusterVariants[] = $candidateName;
                }
            }

            // We only care about clusters with 2 or more variations
            if (count($clusterVariants) >= 2) {
                foreach ($clusterVariants as $v) {
                    $assigned[$v] = true;
                }

                $variations = [];
                $clusterBondCount = 0;

                foreach ($clusterVariants as $variant) {
                    $bCount = $receiversWithCounts[$variant] ?? 0;
                    $clusterBondCount += $bCount;

                    $variations[] = [
                        'name' => $variant,
                        'count' => $bCount,
                    ];
                }

                // Sort variations descending by count
                usort($variations, fn($a, $b) => $b['count'] <=> $a['count']);

                $totalAffectedBonds += $clusterBondCount;

                $clusterData = [
                    'id' => md5(implode('|', $clusterVariants)),
                    'canonical_suggestion' => $centerName,
                    'total_bonds' => $clusterBondCount,
                    'variations_count' => count($variations),
                    'variations' => $variations,
                ];

                // Apply optional search filter
                if (!empty($search)) {
                    $searchNorm = $this->normalizeArabic($search);
                    $matchesSearch = false;
                    foreach ($variations as $v) {
                        if (str_contains($this->normalizeArabic($v['name']), $searchNorm)) {
                            $matchesSearch = true;
                            break;
                        }
                    }
                    if (!$matchesSearch) {
                        continue;
                    }
                }

                $clusters[] = $clusterData;
            }
        }

        // Sort clusters by highest total bonds first
        usort($clusters, fn($a, $b) => $b['total_bonds'] <=> $a['total_bonds']);

        return [
            'stats' => [
                'total_unique_names' => $count,
                'clusters_count' => count($clusters),
                'affected_bonds' => $totalAffectedBonds,
            ],
            'clusters' => $clusters,
        ];
    }

    /**
     * Merge a list of variant names into a single canonical target name.
     *
     * @param string $targetName The chosen clean/master name
     * @param array<string> $variants Names to be updated to $targetName
     * @return int Number of affected bonds
     */
    public function mergeVariants(string $targetName, array $variants): int
    {
        $targetName = trim($targetName);
        if ($targetName === '' || empty($variants)) {
            return 0;
        }

        // Exclude the target name itself from variants to update
        $variantsToUpdate = array_values(array_filter($variants, fn($v) => trim($v) !== $targetName));

        if (empty($variantsToUpdate)) {
            return 0;
        }

        return DB::transaction(function () use ($targetName, $variantsToUpdate) {
            return Bond::whereIn('received_from', $variantsToUpdate)
                ->update(['received_from' => $targetName]);
        });
    }
}
