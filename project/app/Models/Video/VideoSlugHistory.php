<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Video\VideoSlugHistory
 *
 * @property int $id
 * @property int $reference_id
 * @property string $reference_type
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|VideoSlugHistory newModelQuery()
 * @method static Builder|VideoSlugHistory newQuery()
 * @method static Builder|VideoSlugHistory query()
 * @method static Builder|VideoSlugHistory whereCreatedAt($value)
 * @method static Builder|VideoSlugHistory whereId($value)
 * @method static Builder|VideoSlugHistory whereReferenceId($value)
 * @method static Builder|VideoSlugHistory whereReferenceType($value)
 * @method static Builder|VideoSlugHistory whereSlug($value)
 * @method static Builder|VideoSlugHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */

class VideoSlugHistory extends Model
{
    use HasFactory;

    protected $connection = 'crafty_video_mysql';
    protected $table = 'slug_history';

    protected $fillable = [
        'reference_id','reference_type','slug'
    ];

    public static function checkSlugValidation(string|null $slug, $id = 0): ?string
    {
        if (!$slug) {
            return "Slug is Required";
        }

        // ❌ Should not end with '/'
        if (str_ends_with($slug, '/')) {
            return "Slug cannot end with '/'";
        }

        // ✅ Base validation (allowed characters)
        if (!preg_match('/^[a-zA-Z0-9\/_-]+$/', $slug)) {
            return "Slug must be a valid URL format (only letters, numbers, -, _, / allowed, no spaces)";
        }

        // ✅ Check: no segment should be purely numeric
        $segments = explode('/', $slug);

        foreach ($segments as $segment) {
            if ($segment !== '' && ctype_digit($segment)) {
                return "Slug cannot contain numeric-only segments like '{$segment}' after '/'";
            }
        }

        // ✅ Check uniqueness
        if ($id == 0) {
            $slugHistory = VideoSlugHistory::whereSlug($slug)->first();
        } else {
            $slugHistory = VideoSlugHistory::where('reference_id', '!=', $id)
                ->whereSlug($slug)
                ->first();
        }

        if ($slugHistory) {
            return "Slug " . $slug . " is Already in use";
        }

        return null;
    }

    public static function updateVideoSlug($id,$slug,$type,$update = false): void
    {
        if($update){
            $slugHistory = VideoSlugHistory::whereReferenceId($id)->whereReferenceType($type)->first();
        }

        if(!isset($slugHistory)) {
            $slugHistory = new VideoSlugHistory();
            $slugHistory->reference_id = $id;
            $slugHistory->reference_type = $type;
        }

        $slugHistory->slug = $slug;
        $slugHistory->save();
    }

}
