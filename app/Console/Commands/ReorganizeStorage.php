<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Models\PreacherProfile;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReorganizeStorage extends Command
{
    protected $signature = 'storage:reorganize {--dry-run : Show what would be moved without actually moving files}';
    protected $description = 'Reorganize storage files into church/preacher-based folder structure';

    private int $moved = 0;
    private int $skipped = 0;
    private int $errors = 0;
    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = $this->option('dry-run');

        if ($this->dryRun) {
            $this->info('🔍 DRY RUN MODE — no files will be moved.');
        } else {
            if (!$this->confirm('This will reorganize all storage files. Continue?')) {
                return 0;
            }
        }

        $this->info('');
        $this->reorganizeSermons();
        $this->reorganizeChurchLogos();
        $this->reorganizePreacherAvatars();

        $this->info('');
        $this->info("✅ Done! Moved: {$this->moved} | Skipped: {$this->skipped} | Errors: {$this->errors}");

        return 0;
    }

    private function reorganizeSermons(): void
    {
        $this->info('📦 Reorganizing sermon files...');

        Sermon::with(['church', 'preacherProfile'])
            ->whereNotNull('audio_url')
            ->orWhereNotNull('cover_url')
            ->chunkById(100, function ($sermons) {
                foreach ($sermons as $sermon) {
                    $ownerFolder = $this->getSermonOwnerFolder($sermon);
                    if (!$ownerFolder) {
                        $this->skipped++;
                        continue;
                    }

                    $updates = [];

                    // Move audio
                    if ($sermon->audio_url) {
                        $newUrl = $this->moveFile(
                            $sermon->audio_url,
                            $ownerFolder . '/sermons/audio/'
                        );
                        if ($newUrl && $newUrl !== $sermon->audio_url) {
                            $updates['audio_url'] = $newUrl;
                        }
                    }

                    // Move cover
                    if ($sermon->cover_url) {
                        $newUrl = $this->moveFile(
                            $sermon->cover_url,
                            $ownerFolder . '/sermons/covers/'
                        );
                        if ($newUrl && $newUrl !== $sermon->cover_url) {
                            $updates['cover_url'] = $newUrl;
                        }

                        // Move thumbnail too
                        $thumbUrl = $this->getThumbnailPath($sermon->cover_url);
                        if ($thumbUrl) {
                            $this->moveFile(
                                $thumbUrl,
                                $ownerFolder . '/sermons/covers/thumbs/'
                            );
                        }
                    }

                    if (!empty($updates) && !$this->dryRun) {
                        DB::table('sermons')->where('id', $sermon->id)->update($updates);
                    }
                }
            });
    }

    private function reorganizeChurchLogos(): void
    {
        $this->info('🏛️ Reorganizing church logos...');

        Church::whereNotNull('logo_url')->chunkById(100, function ($churches) {
            foreach ($churches as $church) {
                $ownerFolder = 'churches/' . $church->getStorageFolder();
                $newUrl = $this->moveFile(
                    $church->logo_url,
                    $ownerFolder . '/logo/'
                );
                if ($newUrl && $newUrl !== $church->logo_url && !$this->dryRun) {
                    DB::table('churches')->where('id', $church->id)->update(['logo_url' => $newUrl]);
                }

                // Move thumbnail
                $thumbUrl = $this->getThumbnailPath($church->logo_url);
                if ($thumbUrl) {
                    $this->moveFile($thumbUrl, $ownerFolder . '/logo/thumbs/');
                }
            }
        });
    }

    private function reorganizePreacherAvatars(): void
    {
        $this->info('🎤 Reorganizing preacher avatars...');

        PreacherProfile::whereNotNull('avatar_url')->chunkById(100, function ($profiles) {
            foreach ($profiles as $profile) {
                $ownerFolder = 'preachers/' . $profile->getStorageFolder();
                $newUrl = $this->moveFile(
                    $profile->avatar_url,
                    $ownerFolder . '/avatar/'
                );
                if ($newUrl && $newUrl !== $profile->avatar_url && !$this->dryRun) {
                    DB::table('preacher_profiles')->where('id', $profile->id)->update(['avatar_url' => $newUrl]);
                }

                // Move thumbnail
                $thumbUrl = $this->getThumbnailPath($profile->avatar_url);
                if ($thumbUrl) {
                    $this->moveFile($thumbUrl, $ownerFolder . '/avatar/thumbs/');
                }
            }
        });
    }

    private function getSermonOwnerFolder(Sermon $sermon): ?string
    {
        if ($sermon->church_id && $sermon->church) {
            return 'churches/' . $sermon->church->getStorageFolder();
        }
        if ($sermon->preacher_profile_id && $sermon->preacherProfile) {
            return 'preachers/' . $sermon->preacherProfile->getStorageFolder();
        }
        return null;
    }

    /**
     * Move a file from its current location to a new directory.
     * Returns the new storage URL or null if skipped/error.
     */
    private function moveFile(string $currentUrl, string $newDirectory): ?string
    {
        $disk = Storage::disk('public');

        // Convert DB URL to disk-relative path
        $oldPath = str_replace('storage/', '', $currentUrl);

        if (!$disk->exists($oldPath)) {
            $this->skipped++;
            return null;
        }

        // Already in the target directory?
        $filename = basename($oldPath);
        $newPath = rtrim($newDirectory, '/') . '/' . $filename;

        if ($oldPath === $newPath) {
            $this->skipped++;
            return $currentUrl;
        }

        $label = $this->dryRun ? '[DRY RUN] ' : '';
        $this->line("  {$label}{$oldPath} → {$newPath}");

        if (!$this->dryRun) {
            try {
                // Ensure target directory exists
                $targetDir = dirname($newPath);
                if (!$disk->exists($targetDir)) {
                    $disk->makeDirectory($targetDir);
                }

                $disk->move($oldPath, $newPath);
                $this->moved++;
                return 'storage/' . $newPath;
            } catch (\Exception $e) {
                $this->error("  Error moving {$oldPath}: {$e->getMessage()}");
                $this->errors++;
                return null;
            }
        }

        $this->moved++;
        return 'storage/' . $newPath;
    }

    /**
     * Get the thumbnail path for an image URL.
     */
    private function getThumbnailPath(string $imageUrl): ?string
    {
        $diskPath = str_replace('storage/', '', $imageUrl);
        $dir = dirname($diskPath);
        $filename = basename($diskPath);
        $thumbPath = $dir . '/thumbs/' . $filename;

        if (Storage::disk('public')->exists($thumbPath)) {
            return 'storage/' . $thumbPath;
        }

        return null;
    }
}
