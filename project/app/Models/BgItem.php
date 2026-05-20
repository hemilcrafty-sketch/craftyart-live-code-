<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UpdateLogger;

class BgItem extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;

    protected $fillable = [
        "emp_id",
        "bg_cat_id",
        "bg_name",
        "bg_thumb",
        "bg_image",
        "width",
        "height",
        "bg_type",
        "is_premium",
        "status",
    ];

    public function BgCategory(): BelongsTo
    {
        return $this->belongsTo(BgCategory::class, 'bg_cat_id');
    }

    public function BgMode(): BelongsTo{
        return $this->belongsTo(BgMode::class, 'bg_type', 'value');
    }

}
