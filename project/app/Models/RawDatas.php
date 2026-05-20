<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawDatas extends Model
{
    protected $table = 'raw_datas';
    protected $connection = 'mysql';
    use HasFactory;

    const ASSET_TYPES = [
        0 => 'image',
        1 => 'gif',
        2 => 'vector',
        3 => 'video',
        4 => 'audio',
        5 => 'frame',
    ];

    const RAW_TYPES = [
        0 => 'sticker',
        1 => 'background',
        2 => 'frame',
        3 => 'vector',
        4 => 'audio',
        5 => 'gif',
        6 => 'video',
    ];

    protected $fillable = [
        'id',
        'string_id',
        'user_id',
        'asset_type',
        'name',
        'ratio',
        'width',
        'height',
        'image',
        'thumbnail',
        'compress_vdo',
        'duration',
        'asset_size',
    ];

    public function getAssetTypeAttribute($value)
    {
        return self::ASSET_TYPES[$value] ?? 'Unknown';
    }

    public function rawItem()
    {
        return $this->hasMany(RawDatas::class);
    }

    public function getCategoryNameAttribute()
    {
        return self::RAW_TYPES[$this->category_id] ?? 'Not Assign';
    }

    public function getRawTypeAttribute($value)
    {
        return self::RAW_TYPES[$value] ?? 'Not Assign';
    }


}