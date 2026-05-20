<?php

namespace App\Models\Revenue;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSupportPurchaseHistory extends Model
{
  use HasFactory;

  protected $table = 'business_support_purchase_history';
  protected $connection = 'crafty_revenue_mysql';

  protected $fillable = [
    'user_id',
    'product_id',
    'product_type',
    'transaction_id',
    'payment_id',
    'currency_code',
    'amount',
    'payment_method',
    'from_where',
    'contact_no',
    'payment_status',
    'status',
    'description',
  ];

  public function userData()
  {
    return $this->belongsTo(UserData::class, 'user_id', 'uid');
  }
}
