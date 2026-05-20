<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\DesignerDraft
 *
 * @property int $id
 * @property string $string_id
 * @property string|null $template_id
 * @property string $user_id
 * @property array $caricature_ids
 * @property string $name
 * @property float $ratio
 * @property int $width
 * @property int $height
 * @property string $thumbs
 * @property string|null $video
 * @property string $designs
 * @property string|null $msg
 * @property int $is_live
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|DesignerDraft newModelQuery()
 * @method static Builder|DesignerDraft newQuery()
 * @method static Builder|DesignerDraft query()
 * @method static Builder|DesignerDraft whereCreatedAt($value)
 * @method static Builder|DesignerDraft whereDesigns($value)
 * @method static Builder|DesignerDraft whereHeight($value)
 * @method static Builder|DesignerDraft whereId($value)
 * @method static Builder|DesignerDraft whereIsLive($value)
 * @method static Builder|DesignerDraft whereMsg($value)
 * @method static Builder|DesignerDraft whereName($value)
 * @method static Builder|DesignerDraft whereRatio($value)
 * @method static Builder|DesignerDraft whereStringId($value)
 * @method static Builder|DesignerDraft whereTemplateId($value)
 * @method static Builder|DesignerDraft whereThumbs($value)
 * @method static Builder|DesignerDraft whereUpdatedAt($value)
 * @method static Builder|DesignerDraft whereUserId($value)
 * @method static Builder|DesignerDraft whereVideo($value)
 * @method static Builder|DesignerDraft whereWidth($value)
 * @mixin Eloquent
 */
class DesignerDraft extends Model
{
    protected $table = 'designer_drafts';
    protected $connection = 'mysql';
    use HasFactory;

    public function getCaricatureIdsAttribute($value): array
    {
        return match (true) {
            is_array($value) => $value,
            is_string($value) => json_decode($value, true) ?? [],
            default => [],
        };
    }
}
