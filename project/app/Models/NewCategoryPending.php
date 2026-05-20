<?php

namespace App\Models;

use App\Http\Controllers\Api\HelperController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UpdateLogger;

/**
 * @method static select(string $string)
 */
class NewCategoryPending extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    use UpdateLogger;
}
