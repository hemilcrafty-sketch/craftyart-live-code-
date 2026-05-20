<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserEmailChangeLog
 *
 * @property int $id
 * @property string $uid
 * @property string $old_email
 * @property string $new_email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailChangeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailChangeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailChangeLog query()
 * @mixin \Eloquent
 */
class UserEmailChangeLog extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'uid',
        'old_email',
        'new_email',
    ];
}
