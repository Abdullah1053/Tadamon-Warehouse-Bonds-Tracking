<?php
/**
 * update_bond_links.php
 * 
 * Updates the bond_link field in the database for given bond serial numbers.
 * Usage:
 *   php update_bond_links.php [links.json]
 * Or via pipe:
 *   cat links.json | php update_bond_links.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Bond;
use Illuminate\Support\Facades\DB;

// Determine input source (file path argument or STDIN)
$inputFile = $argv[1] ?? null;
if ($inputFile && file_exists($inputFile)) {
    $content = file_get_contents($inputFile);
} else {
    $content = stream_get_contents(STDIN);
}

if (empty(trim($content))) {
    echo "Error: No JSON input provided.\n";
    echo "Usage: php update_bond_links.php <path_to_json_file>\n";
    exit(1);
}

$data = json_decode($content, true);

if (!is_array($data)) {
    echo "Error: Invalid JSON payload.\n";
    exit(1);
}

$updatedCount = 0;
$notFound = [];

DB::beginTransaction();
try {
    foreach ($data as $key => $val) {
        $serial = null;
        $url = null;

        if (is_array($val)) {
            $serial = $val['serial'] ?? $val['bond_serial'] ?? null;
            $url = $val['url'] ?? $val['bond_link'] ?? null;
        } else {
            $serial = (string) $key;
            $url = (string) $val;
        }

        if (!$serial || !$url) {
            continue;
        }

        $bond = Bond::where('bond_serial', $serial)->first();
        if ($bond) {
            $bond->update(['bond_link' => $url]);
            $updatedCount++;
        } else {
            $notFound[] = $serial;
        }
    }

    DB::commit();

    echo "========================================\n";
    echo " Database Update Summary\n";
    echo "========================================\n";
    echo " Successfully updated : {$updatedCount} bonds\n";
    if (!empty($notFound)) {
        echo " Serials not found in DB (" . count($notFound) . "): " . implode(', ', $notFound) . "\n";
    }
    echo "========================================\n";
    exit(0);

} catch (\Throwable $e) {
    DB::rollBack();
    echo "Error updating database: " . $e->getMessage() . "\n";
    exit(1);
}
