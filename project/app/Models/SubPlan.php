<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubPlan extends Model
{
    public static array $PLAN_KEYS = ["inr_price", "usd_price", "inr_discount", "usd_discount", "inr_offer_price", "usd_offer_price"];
    protected $connection = 'mysql';

    // Add the fields that can be mass-assigned
    protected $fillable = [
        "string_id",
        'plan_id',
        'deleted',
        'duration_id',
        'plan_details',
    ];

    // SubPlan.php
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'string_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id', 'id');
    }

    public function planRef(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function duration(): BelongsTo
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id');
    }

    public function getPlanDetailsAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }

}
