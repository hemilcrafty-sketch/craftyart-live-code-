<?php

namespace App\Models\Revenue;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
  protected $connection = 'crafty_revenue_mysql';
  protected $table = 'sales';

  protected $fillable = [
    'sales_person_id',
    'user_name',
    'email',
    'contact_no',
    'payment_method',
    'plan_id',
    'subscription_type',
    'amount',
    'plan_type',
    'caricature',
    'usage_type', // personal or professional
    'reference_id',
    'payment_link_id',
    'phonepe_order_id',
    'payment_link_url',
    'short_url',
    'status',
    'order_id',
    'paid_at',
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'paid_at' => 'datetime',
    'caricature' => 'integer',
  ];

  /**
   * Boot the model
   */
  protected static function boot()
  {
    parent::boot();

    static::saving(function ($sale) {
      if ($sale->caricature > 100) {
        throw new \InvalidArgumentException('Caricature value cannot exceed 100');
      }
      if ($sale->caricature < 0) {
        throw new \InvalidArgumentException('Caricature value cannot be negative');
      }
    });
  }

  /**
   * Generate unique reference ID
   */
  public static function generateReferenceId(): string
  {
    do {
      $referenceId = 'txn_' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
    } while (self::where('reference_id', $referenceId)->exists());

    return $referenceId;
  }

  /**
   * Get sales person
   */
  public function salesPerson()
  {
    return $this->belongsTo(User::class, 'sales_person_id');
  }

  /**
   * Get related order
   */
  public function order()
  {
    return $this->belongsTo(Order::class, 'order_id');
  }
}
