<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseHistory extends Model
{
    protected $table = 'purchase_history';
    protected $connection = 'mysql';
    use HasFactory;

    public function userData()
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }
}
