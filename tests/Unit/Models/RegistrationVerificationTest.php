<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\RegistrationVerification;
use Illuminate\Support\Carbon;

class RegistrationVerificationTest extends TestCase
{
    public function test_fillable_contains_expected_fields()
    {
        $record = new RegistrationVerification();
        foreach (['email', 'otp', 'data', 'expires_at', 'verified_at'] as $field) {
            $this->assertTrue(in_array($field, $record->getFillable()), "fillable harus berisi {$field}");
        }
    }

    public function test_generate_otp_creates_six_digit_code()
    {
        $record = RegistrationVerification::generateOtp('user@example.com', ['name' => 'Budi']);

        $this->assertEquals('user@example.com', $record->email);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $record->otp);
        $this->assertEquals(['name' => 'Budi'], $record->data);
        $this->assertTrue($record->expires_at->isFuture());
    }

    public function test_generate_otp_replaces_previous_otp_for_same_email()
    {
        RegistrationVerification::generateOtp('user@example.com');
        $this->assertEquals(1, RegistrationVerification::where('email', 'user@example.com')->count());

        RegistrationVerification::generateOtp('user@example.com');
        $this->assertEquals(1, RegistrationVerification::where('email', 'user@example.com')->count());
    }

    public function test_verify_otp_succeeds_with_correct_otp()
    {
        $record = RegistrationVerification::generateOtp('user@example.com');
        $result = RegistrationVerification::verifyOtp('user@example.com', $record->otp);

        $this->assertNotNull($result);
        $this->assertTrue($result->isVerified());
        $this->assertNotNull($result->fresh()->verified_at);
    }

    public function test_verify_otp_fails_with_wrong_otp()
    {
        RegistrationVerification::generateOtp('user@example.com');
        $result = RegistrationVerification::verifyOtp('user@example.com', '000000');

        $this->assertNull($result);
    }

    public function test_verify_otp_fails_when_expired()
    {
        $record = RegistrationVerification::generateOtp('user@example.com');
        $record->update(['expires_at' => Carbon::yesterday()]);

        $result = RegistrationVerification::verifyOtp('user@example.com', $record->otp);

        $this->assertNull($result);
    }

    public function test_verify_otp_fails_when_already_verified()
    {
        $record = RegistrationVerification::generateOtp('user@example.com');
        RegistrationVerification::verifyOtp('user@example.com', $record->otp);

        $second = RegistrationVerification::verifyOtp('user@example.com', $record->otp);
        $this->assertNull($second);
    }

    public function test_is_expired_reflects_expires_at()
    {
        $record = new RegistrationVerification(['expires_at' => Carbon::yesterday()]);
        $this->assertTrue($record->isExpired());

        $future = new RegistrationVerification(['expires_at' => Carbon::tomorrow()]);
        $this->assertFalse($future->isExpired());
    }

    public function test_is_verified_reflects_verified_at()
    {
        $verified = new RegistrationVerification(['verified_at' => now()]);
        $this->assertTrue($verified->isVerified());

        $notVerified = new RegistrationVerification(['verified_at' => null]);
        $this->assertFalse($notVerified->isVerified());
    }
}
