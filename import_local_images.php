<?php
/**
 * import_local_images.php
 *
 * Fast local CLI script to import bond images directly into local project storage
 * without using third-party services like ImageKit.
 *
 * Usage:
 *   php import_local_images.php [folder_path_or_stack_id]
 *
 * Examples:
 *   php import_local_images.php bonds_images/24
 *   php import_local_images.php 24
 *   php import_local_images.php "C:\path\to\scanned_bonds"
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Bond;
use App\Models\Stack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "=======================================================\n";
echo " Local Bond Images Importer (In-Project Storage)\n";
echo "=======================================================\n";

$targetArg = $argv[1] ?? null;

// If not provided, list folders found in bonds_images/
if (!$targetArg) {
    $candidates = [];
    if (is_dir(__DIR__ . '/bonds_images')) {
        $dirs = File::directories(__DIR__ . '/bonds_images');
        foreach ($dirs as $d) {
            $candidates[] = $d;
        }
    }

    if (!empty($candidates)) {
        echo "Found available folders in bonds_images/:\n";
        foreach ($candidates as $i => $c) {
            echo "  [" . ($i + 1) . "] " . basename($c) . "\n";
        }
        echo "Please run: php import_local_images.php <folder_name_or_path>\n";
        exit(1);
    } else {
        echo "Usage: php import_local_images.php <path_to_images_folder>\n";
        exit(1);
    }
}

// Resolve directory path
$dirPath = null;
if (is_dir($targetArg)) {
    $dirPath = realpath($targetArg);
} elseif (is_dir(__DIR__ . '/bonds_images/' . $targetArg)) {
    $dirPath = realpath(__DIR__ . '/bonds_images/' . $targetArg);
}

if (!$dirPath || !is_dir($dirPath)) {
    echo "[ERROR] Directory not found: {$targetArg}\n";
    exit(1);
}

echo "Scanning folder: {$dirPath}\n";

// Target storage directory
$destinationDir = storage_path('app/public/bonds');
if (!is_dir($destinationDir)) {
    mkdir($destinationDir, 0755, true);
}

// Find all image files
$files = File::files($dirPath);
$imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

$matchedCount = 0;
$unmatchedCount = 0;
$skippedCount = 0;

DB::beginTransaction();

try {
    foreach ($files as $file) {
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, $imageExtensions)) {
            $skippedCount++;
            continue;
        }

        $filename = $file->getFilename();
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

        // Extract serial number from filename (digits)
        preg_match('/(\d+)/', $nameWithoutExt, $matches);
        $extractedSerial = $matches[1] ?? null;

        if (!$extractedSerial) {
            echo "  [?] Cannot extract serial number from: {$filename}\n";
            $unmatchedCount++;
            continue;
        }

        // Query bond in database
        $bond = Bond::where('bond_serial', $extractedSerial)
            ->orWhere('bond_serial', ltrim($extractedSerial, '0'))
            ->orWhereRaw('CAST(bond_serial AS UNSIGNED) = ?', [(int)$extractedSerial])
            ->first();

        // Copy file to storage
        $safeName = 'bond_' . ($bond ? $bond->bond_serial : $extractedSerial) . '_' . time() . '_' . substr(md5($filename), 0, 6) . '.' . $ext;
        $destPath = $destinationDir . '/' . $safeName;
        copy($file->getRealPath(), $destPath);
        $webPath = '/storage/bonds/' . $safeName;

        if ($bond) {
            $bond->update(['bond_link' => $webPath]);
            $matchedCount++;
            echo "  [✓] Linked Bond #{$bond->bond_serial} ({$bond->received_from}) -> {$safeName}\n";
        } else {
            $unmatchedCount++;
            echo "  [!] Image copied but no matching bond for serial: {$extractedSerial} ({$filename})\n";
        }
    }

    DB::commit();

    echo "\n=======================================================\n";
    echo " Import Complete Summary\n";
    echo "=======================================================\n";
    echo " Successfully matched & linked : {$matchedCount} bonds\n";
    if ($unmatchedCount > 0) {
        echo " Unmatched serials            : {$unmatchedCount}\n";
    }
    if ($skippedCount > 0) {
        echo " Non-image files skipped       : {$skippedCount}\n";
    }
    echo " Images stored in             : {$destinationDir}\n";
    echo " Accessible via URL           : /storage/bonds/...\n";
    echo "=======================================================\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n[ERROR] Transaction failed: " . $e->getMessage() . "\n";
    exit(1);
}
