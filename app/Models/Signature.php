<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Signature extends Model
{
    protected $fillable = [
        'user_id',
        'signable_id',
        'signable_type',
        'signature_image',
        'signature_hash',
        'signature_reason',
        'signed_date',
        'ip_address',
        'otp_code',
        'user_agent',
        'is_verified',
        'verification_token',
        'verified_at',
        'external_name',
        'external_email',
        'external_title',
        'external_company',
        'token',
    ];

    protected $casts = [
        'signed_date' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($signature) {
            $signature->verifications()->delete();
        });
    }

    /**
     * Get the signer (user who signed)
     */
    public function signer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the signable model (Letter, Document, etc.)
     */
    public function signable()
    {
        return $this->morphTo();
    }

    /**
     * Get all verification records
     */
    public function verifications()
    {
        return $this->hasMany(SignatureVerification::class);
    }

    /**
     * Get the latest verification
     */
    public function latestVerification()
    {
        return $this->hasOne(SignatureVerification::class)->latest();
    }

    /**
     * Generate cryptographic signature hash
     */
    public static function generateSignatureHash($signatureData, $userId, $documentId)
    {
        $data = $signatureData . '|' . $userId . '|' . $documentId . '|' . now()->toDateTimeString();
        return hash('sha256', $data);
    }

    /**
     * Generate verification token
     */
    public static function generateVerificationToken()
    {
        return Str::random(64);
    }

    /**
     * Reconstruct the data string that was signed
     */
    public function getSignableData()
    {
        return $this->signable_id . '|' . $this->user_id . '|' . $this->signed_date->toDateTimeString();
    }

    /**
     * Check if signature is still valid via OpenSSL (not tampered)
     */
    public function isValid()
    {
        return $this->verifyWithOpenSSL($this->getSignableData());
    }

    /**
     * Verify the signature
     */
    public function verify($verifiedById, $remarks = null)
    {
        $this->verifications()->create([
            'verified_by_id' => $verifiedById,
            'status' => 'verified',
            'remarks' => $remarks,
        ]);

        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return $this;
    }

    /**
     * Reject the signature
     */
    public function reject($rejectedById, $remarks = null)
    {
        $this->verifications()->create([
            'verified_by_id' => $rejectedById,
            'status' => 'rejected',
            'remarks' => $remarks,
        ]);

        return $this;
    }
    public function signWithOpenSSL($data)
    {
        $privateKey = openssl_get_privatekey(file_get_contents(storage_path('app/keys/private.pem')));
        openssl_sign($data, $binarySignature, $privateKey, 'sha256WithRSAEncryption');

        // Simpan hasil biner dalam format base64 agar aman di database
        $this->signature_hash = base64_encode($binarySignature);
        $this->save();
    }
    public function verifyWithOpenSSL($data)
    {
        $publicKey = openssl_get_publickey(file_get_contents(storage_path('app/keys/public.pem')));
        $binarySignature = base64_decode($this->signature_hash);

        return openssl_verify($data, $binarySignature, $publicKey, 'sha256WithRSAEncryption') === 1;
    }
}
