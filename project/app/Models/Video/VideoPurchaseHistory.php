<?php

namespace App\Models\Video;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UpdateLogger;

class VideoPurchaseHistory extends Model
{
	protected $table = 'purchase_history';
	protected $connection = 'crafty_video_mysql';
    use HasFactory;
    use UpdateLogger;

    public function userData()
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }

}
