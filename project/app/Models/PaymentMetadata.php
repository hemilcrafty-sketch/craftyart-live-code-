<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentMetadata extends Model
{
    protected $fillable = [
        'transaction_id',
        'meta_data',
        'status',
    ];
    protected $casts = [
        'meta_data' => 'array',
    ];
}