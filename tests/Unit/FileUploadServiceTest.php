<?php

namespace Tests\Unit;

use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
    use RefreshDatabase;

    private FileUploadService $uploadService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uploadService = new FileUploadService();
        Storage::fake('public');
    }

    /**
     * Test successful audio upload
     */
    public function test_handles_valid_audio_upload(): void
    {
        $base64Audio = 'data:audio/mp3;base64,' . base64_encode('fake audio content');

        $result = $this->uploadService->handleAudioUpload($base64Audio);

        $this->assertStringContainsString('/storage/sermons/audio/', $result);
        $this->assertStringContainsString('.mp3', $result);
    }

    /**
     * Test successful image upload
     */
    public function test_handles_valid_image_upload(): void
    {
        $base64Image = 'data:image/jpeg;base64,' . base64_encode('fake image content');

        $result = $this->uploadService->handleImageUpload($base64Image);

        $this->assertStringContainsString('/storage/sermons/covers/', $result);
        $this->assertStringContainsString('.jpeg', $result);
    }

    /**
     * Test invalid audio format throws exception
     */
    public function test_throws_exception_for_invalid_audio_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base64 audio format');

        $this->uploadService->handleAudioUpload('invalid audio data');
    }

    /**
     * Test invalid image format throws exception
     */
    public function test_throws_exception_for_invalid_image_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base64 image format');

        $this->uploadService->handleImageUpload('invalid image data');
    }

    /**
     * Test unsupported audio extension throws exception
     */
    public function test_throws_exception_for_unsupported_audio_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported audio format');

        $base64Audio = 'data:audio/flac;base64,' . base64_encode('fake audio content');
        $this->uploadService->handleAudioUpload($base64Audio);
    }

    /**
     * Test unsupported image extension throws exception
     */
    public function test_throws_exception_for_unsupported_image_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported image format');

        $base64Image = 'data:image/bmp;base64,' . base64_encode('fake image content');
        $this->uploadService->handleImageUpload($base64Image);
    }

    /**
     * Test file deletion
     */
    public function test_deletes_file_successfully(): void
    {
        // First upload a file
        $base64Audio = 'data:audio/mp3;base64,' . base64_encode('fake audio content');
        $audioUrl = $this->uploadService->handleAudioUpload($base64Audio);

        // Verify file exists
        $relativePath = str_replace(asset('storage/'), '', $audioUrl);
        $this->assertTrue(Storage::disk('public')->exists($relativePath));

        // Delete file
        $result = $this->uploadService->deleteFile($audioUrl, 'audio');

        // Verify deletion
        $this->assertTrue($result);
        $this->assertFalse(Storage::disk('public')->exists($relativePath));
    }

    /**
     * Test audio file info retrieval
     */
    public function test_gets_audio_file_info(): void
    {
        $base64Audio = 'data:audio/mp3;base64,' . base64_encode('fake audio content');
        $audioUrl = $this->uploadService->handleAudioUpload($base64Audio);

        $fileInfo = $this->uploadService->getAudioFileInfo($audioUrl);

        $this->assertNotNull($fileInfo);
        $this->assertArrayHasKey('size', $fileInfo);
        $this->assertArrayHasKey('size_formatted', $fileInfo);
        $this->assertArrayHasKey('last_modified', $fileInfo);
        $this->assertTrue($fileInfo['exists']);
    }
}
