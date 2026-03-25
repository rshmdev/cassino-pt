<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlackPearlPayPayment extends Model
{
    use HasFactory;

    protected $table = 'black_pearl_pay_payments';
    protected $appends = ['dateHumanReadable', 'createdAt'];

    protected $fillable = [
        'payment_id',
        'user_id',
        'withdrawal_id',
        'pix_key',
        'pix_type',
        'amount',
        'observation',
        'status',
    ];

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->getStatus($value),
        );
    }

    private function getStatus($status)
    {
        switch ($status) {
            case '1':
                return 'pago';
            case '0':
                return 'pendente';
        }
    }

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at']);
    }

    public function getDateHumanReadableAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
