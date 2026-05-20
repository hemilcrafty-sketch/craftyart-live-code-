<?php

namespace App\Models;

use Illumiate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubPlan extends Model
{
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
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'plan_id');
    }

    public function category()
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id', 'id', );
    }

    public function planRef()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function duration()
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id');
    }

}
