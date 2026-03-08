<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\PreacherProfile;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageCleanupController extends Controller
{
    /**
     * Analyze storage with streamed progress via SSE.
     */
    public function analyze()
    {
        return new StreamedResponse(function () {
            $send = function (string $event, array $data) {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data) . "\n\n";
                if (ob_get_level()) ob_flush();
                flush();
            };

            $disk = Storage::disk('public');

            // Step 1: Collect DB references
            $send('progress', ['step' => 'db', 'percent' => 5, 'detail' => 'sermons']);
            $refs = collect();
            $refs = $refs->merge(Sermon::whereNotNull('audio_url')->pluck('audio_url'));
            $send('progress', ['step' => 'db', 'percent' => 10, 'detail' => 'covers']);
            $refs = $refs->merge(Sermon::whereNotNull('cover_url')->pluck('cover_url'));
            $send('progress', ['step' => 'db', 'percent' => 14, 'detail' => 'churches']);
            $refs = $refs->merge(Church::whereNotNull('logo_url')->pluck('logo_url'));
            $send('progress', ['step' => 'db', 'percent' => 17, 'detail' => 'users']);
            $refs = $refs->merge(User::whereNotNull('avatar_url')->pluck('avatar_url'));
            $send('progress', ['step' => 'db', 'percent' => 19, 'detail' => 'preachers']);
            $refs = $refs->merge(PreacherProfile::whereNotNull('avatar_url')->pluck('avatar_url'));

            $referencedPaths = $refs
                ->map(fn ($url) => str_replace('storage/', '', $url))
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $send('progress', ['step' => 'db', 'percent' => 20, 'detail' => 'done', 'referenced' => count($referencedPaths)]);

            // Step 2: List all files
            $send('progress', ['step' => 'listing', 'percent' => 22]);
            $allFiles = $disk->allFiles();
            $totalFiles = count($allFiles);
            $send('progress', ['step' => 'listing', 'percent' => 25, 'total_files' => $totalFiles]);

            // Step 3: Scan files (orphans + sizes) — 25% to 70%
            $orphanFiles = [];
            $hashMap = [];
            $totalSize = 0;
            $orphanSize = 0;
            $folderStats = [];

            $scanRange = 45; // 25% → 70%
            $lastSentPercent = 25;

            foreach ($allFiles as $i => $file) {
                $size = $disk->size($file);
                $totalSize += $size;

                // Folder stats
                $folder = explode('/', $file)[0] ?? 'root';
                if (!isset($folderStats[$folder])) {
                    $folderStats[$folder] = ['count' => 0, 'size' => 0];
                }
                $folderStats[$folder]['count']++;
                $folderStats[$folder]['size'] += $size;

                // Orphan check
                if (!in_array($file, $referencedPaths)) {
                    $orphanFiles[] = [
                        'path' => $file,
                        'size' => $size,
                        'last_modified' => $disk->lastModified($file),
                    ];
                    $orphanSize += $size;
                }

                // Duplicate candidate
                if ($size > 10240) {
                    $sizeKey = (string) $size;
                    $hashMap[$sizeKey][] = $file;
                }

                // Send progress every ~5%
                $currentPercent = 25 + (int)(($i + 1) / $totalFiles * $scanRange);
                if ($currentPercent >= $lastSentPercent + 5 || $i === $totalFiles - 1) {
                    $lastSentPercent = $currentPercent;
                    $send('progress', [
                        'step' => 'scanning',
                        'percent' => min($currentPercent, 70),
                        'scanned' => $i + 1,
                        'total_files' => $totalFiles,
                        'orphans_found' => count($orphanFiles),
                    ]);
                }
            }

            // Step 4: Check duplicates (70% → 95%)
            $send('progress', ['step' => 'duplicates', 'percent' => 72]);
            $duplicates = [];
            $candidateGroups = array_filter($hashMap, fn ($files) => count($files) >= 2);
            $totalCandidates = count($candidateGroups);
            $processed = 0;

            foreach ($candidateGroups as $sizeFiles) {
                $md5Map = [];
                foreach ($sizeFiles as $file) {
                    $md5 = md5($disk->get($file));
                    $md5Map[$md5][] = $file;
                }

                foreach ($md5Map as $hash => $files) {
                    if (count($files) > 1) {
                        $fileDetails = array_map(fn ($f) => [
                            'path' => $f,
                            'size' => $disk->size($f),
                            'referenced' => in_array($f, $referencedPaths),
                        ], $files);

                        $duplicates[] = [
                            'hash' => $hash,
                            'files' => $fileDetails,
                            'wasted_size' => $disk->size($files[0]) * (count($files) - 1),
                        ];
                    }
                }

                $processed++;
                $dupPercent = 72 + (int)($processed / max($totalCandidates, 1) * 23);
                if ($processed % max(1, (int)($totalCandidates / 5)) === 0 || $processed === $totalCandidates) {
                    $send('progress', [
                        'step' => 'duplicates',
                        'percent' => min($dupPercent, 95),
                        'checked' => $processed,
                        'total_candidates' => $totalCandidates,
                        'duplicates_found' => count($duplicates),
                    ]);
                }
            }

            // Sort orphans by size descending
            usort($orphanFiles, fn ($a, $b) => $b['size'] - $a['size']);

            // Step 5: Detect empty folders
            $send('progress', ['step' => 'finalizing', 'percent' => 96]);
            $allDirs = $disk->allDirectories();
            $emptyFolders = [];
            foreach ($allDirs as $dir) {
                if (count($disk->files($dir)) === 0 && count($disk->directories($dir)) === 0) {
                    $emptyFolders[] = $dir;
                }
            }
            sort($emptyFolders);

            $send('progress', ['step' => 'finalizing', 'percent' => 98]);

            $send('result', [
                'total_files' => $totalFiles,
                'total_size' => $totalSize,
                'referenced_count' => count($referencedPaths),
                'orphan_files' => $orphanFiles,
                'orphan_count' => count($orphanFiles),
                'orphan_size' => $orphanSize,
                'duplicates' => $duplicates,
                'duplicate_groups' => count($duplicates),
                'folder_stats' => $folderStats,
                'empty_folders' => $emptyFolders,
                'empty_folder_count' => count($emptyFolders),
            ]);

            $send('progress', ['step' => 'done', 'percent' => 100]);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Browse a specific folder in storage.
     */
    public function browse(Request $request)
    {
        $path = $request->query('path', '');

        // Prevent path traversal
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        $path = trim($path, '/');

        $disk = Storage::disk('public');

        // Collect DB references for "referenced" status
        $referencedPaths = collect()
            ->merge(Sermon::whereNotNull('audio_url')->pluck('audio_url'))
            ->merge(Sermon::whereNotNull('cover_url')->pluck('cover_url'))
            ->merge(Church::whereNotNull('logo_url')->pluck('logo_url'))
            ->merge(User::whereNotNull('avatar_url')->pluck('avatar_url'))
            ->merge(PreacherProfile::whereNotNull('avatar_url')->pluck('avatar_url'))
            ->map(fn ($url) => str_replace('storage/', '', $url))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Get directories at this level
        $allDirs = $disk->directories($path);
        $folders = [];
        foreach ($allDirs as $dir) {
            $files = $disk->allFiles($dir);
            $subDirs = $disk->directories($dir);
            $size = 0;
            foreach ($files as $f) {
                $size += $disk->size($f);
            }
            $folders[] = [
                'name' => basename($dir),
                'path' => $dir,
                'count' => count($files),
                'size' => $size,
                'empty' => count($files) === 0 && count($subDirs) === 0,
            ];
        }

        // Get files directly in this folder
        $allFiles = $disk->files($path);
        $files = [];
        foreach ($allFiles as $file) {
            $size = $disk->size($file);
            $files[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => $size,
                'referenced' => in_array($file, $referencedPaths),
                'last_modified' => $disk->lastModified($file),
            ];
        }

        // Sort folders by name, files by size desc
        usort($folders, fn ($a, $b) => strcmp($a['name'], $b['name']));
        usort($files, fn ($a, $b) => $b['size'] - $a['size']);

        return response()->json([
            'current_path' => $path,
            'folders' => $folders,
            'files' => $files,
        ]);
    }

    /**
     * Delete selected empty folders.
     */
    public function cleanupFolders(Request $request)
    {
        $validated = $request->validate([
            'folders' => ['required', 'array', 'min:1'],
            'folders.*' => ['required', 'string'],
        ]);

        $disk = Storage::disk('public');
        $deleted = 0;
        $skipped = 0;

        foreach ($validated['folders'] as $folder) {
            // Safety: prevent path traversal
            $normalized = str_replace(['..', '\\'], ['', '/'], $folder);
            if ($normalized !== $folder) {
                $skipped++;
                continue;
            }

            // Only delete truly empty directories (no files, no subdirs)
            if ($disk->directoryExists($folder)
                && count($disk->files($folder)) === 0
                && count($disk->directories($folder)) === 0
            ) {
                $disk->deleteDirectory($folder);
                $deleted++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'deleted' => $deleted,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Delete selected orphan files.
     */
    public function cleanup(Request $request)
    {
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'string'],
        ]);

        $disk = Storage::disk('public');

        // Re-check that files are truly orphans before deleting
        $referencedPaths = collect()
            ->merge(Sermon::whereNotNull('audio_url')->pluck('audio_url'))
            ->merge(Sermon::whereNotNull('cover_url')->pluck('cover_url'))
            ->merge(Church::whereNotNull('logo_url')->pluck('logo_url'))
            ->merge(User::whereNotNull('avatar_url')->pluck('avatar_url'))
            ->merge(PreacherProfile::whereNotNull('avatar_url')->pluck('avatar_url'))
            ->map(fn ($url) => str_replace('storage/', '', $url))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $deleted = 0;
        $freedSize = 0;
        $skipped = 0;

        foreach ($validated['files'] as $file) {
            // Safety: prevent path traversal
            $normalized = str_replace(['..', '\\'], ['', '/'], $file);
            if ($normalized !== $file) {
                $skipped++;
                continue;
            }

            // Don't delete files still referenced in DB
            if (in_array($file, $referencedPaths)) {
                $skipped++;
                continue;
            }

            if ($disk->exists($file)) {
                $freedSize += $disk->size($file);
                $disk->delete($file);
                $deleted++;
            }
        }

        return response()->json([
            'deleted' => $deleted,
            'freed_size' => $freedSize,
            'skipped' => $skipped,
        ]);
    }
}
