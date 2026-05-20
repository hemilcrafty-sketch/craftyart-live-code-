<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferPackage extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'offer_package';

    protected $fillable = [
        'string_id',
        'plan_id',
        'duration_id',
        'sub_plan_id',
        'bounce_code_id',
        'status'
    ];

    // 🔹 Relationships
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'string_id');
    }

    public function duration()
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id', 'id');
    }

    public function BonusPackage()
    {
        return $this->belongsTo(BonusPackage::class, 'bounce_code_id', 'id');
    }
}
