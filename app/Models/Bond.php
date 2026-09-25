<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bond extends Model
{
    //

    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_DISBURSEMENT = 'disbursement';

    protected $fillable = [
        'stack_id',
        'type',
        'bond_serial',
        'note',
        'bond_link',
        'date',
        'operation_name',
        'received_from',
        'car_number',
        'is_missing'
    ];

    public function items() { return $this->hasMany(BondItem::class); }
    public function stack() { return $this->belongsTo(Stack::class); }

    /**
     * Check if this bond is a Material Receipt Bond (سند استلام مواد).
     */
    public function isReceipt(): bool
    {
        return empty($this->type) || $this->type === self::TYPE_RECEIPT;
    }

    /**
     * Check if this bond is a Material Disbursement Bond (سند صرف مواد).
     */
    public function isDisbursement(): bool
    {
        return $this->type === self::TYPE_DISBURSEMENT;
    }

    /**
     * Get Arabic label for bond type.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->isDisbursement() ? 'سند صرف مواد' : 'سند استلام مواد';
    }

    /**
     * Check if the bond is cancelled (physical paper is attached, but voided).
     */
    public function isCancelled(): bool
    {
        return $this->received_from === 'ملغي' 
            || $this->operation_name === 'ملغي' 
            || $this->note === 'ملغي';
    }

    /**
     * Check if the bond is missing (physically cut off / missing from stack).
     */
    public function isMissing(): bool
    {
        return (bool) $this->is_missing 
            || $this->received_from === 'مفقود' 
            || $this->operation_name === 'مفقود';
    }

    /**
     * Get normalized status key ('cancelled', 'missing', 'normal').
     */
    public function getStatusAttribute(): string
    {
        if ($this->isMissing()) return 'missing';
        if ($this->isCancelled()) return 'cancelled';
        return 'normal';
    }

    /**
     * Get Arabic label for status.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isMissing()) return 'مفقود';
        if ($this->isCancelled()) return 'ملغي';
        return 'سليم';
    }

    /**
     * Get the full URL to the bond image (supports local /storage/ paths and external URLs).
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->bond_link)) {
            return null;
        }

        if (str_starts_with($this->bond_link, 'http://') || str_starts_with($this->bond_link, 'https://')) {
            return $this->bond_link;
        }

        $cleanPath = ltrim($this->bond_link, '/');

        // When accessed via HTTP request, use the incoming scheme & host so it points to the real server domain/IP
        try {
            if (app()->bound('request') && request() && request()->hasHeader('host')) {
                return request()->schemeAndHttpHost() . '/' . $cleanPath;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return url($cleanPath);
    }

    /**
     * Determine if the bond has an attached image.
     */
    public function hasImage(): bool
    {
        return !empty($this->bond_link);
    }
}
