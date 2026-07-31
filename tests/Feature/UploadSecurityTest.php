<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Requests\StoreExpenseRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

/**
 * Tahap 5.4 — Keamanan upload file.
 * Memastikan upload divalidasi dengan whitelist mime (image jpg/jpeg/png,
 * max 5MB), menolak file berbahaya (php/executable), dan opsional.
 */
class UploadSecurityTest extends TestCase
{
    private function validateWithPhoto(?UploadedFile $file)
    {
        $rules = (new StoreExpenseRequest())->rules();
        return Validator::make([
            'description' => 'Test expense',
            'amount' => 50000,
            'expense_date' => '2024-01-01',
            'photo' => $file,
        ], $rules);
    }

    public function test_valid_jpg_photo_passes()
    {
        $file = UploadedFile::fake()->image('receipt.jpg', 100, 100);
        $this->assertTrue($this->validateWithPhoto($file)->passes());
    }

    public function test_valid_png_photo_passes()
    {
        $file = UploadedFile::fake()->image('receipt.png', 100, 100);
        $this->assertTrue($this->validateWithPhoto($file)->passes());
    }

    public function test_php_file_is_rejected()
    {
        $file = UploadedFile::fake()->create('evil.php', 100);
        $validator = $this->validateWithPhoto($file);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('photo', $validator->errors()->toArray());
    }

    public function test_executable_binary_is_rejected()
    {
        $file = UploadedFile::fake()->create('malware.exe', 100);
        $this->assertTrue($this->validateWithPhoto($file)->fails());
    }

    public function test_script_file_is_rejected()
    {
        $file = UploadedFile::fake()->create('shell.sh', 100);
        $this->assertTrue($this->validateWithPhoto($file)->fails());
    }

    public function test_oversized_photo_is_rejected()
    {
        $file = UploadedFile::fake()->image('big.jpg', 100, 100)->size(6000); // 6000KB > 5MB
        $this->assertTrue($this->validateWithPhoto($file)->fails());
    }

    public function test_photo_is_optional()
    {
        $this->assertTrue($this->validateWithPhoto(null)->passes());
    }
}
