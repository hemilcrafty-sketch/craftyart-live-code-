<?php
namespace App\Models\Automation;
use App\Http\Controllers\HelperController;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\WhatsappTemplate
 *
 * @property int $id
 * @property string $campaign_name
 * @property int $template_params_count
 * @property int|null $media_url
 * @property string|null $url
 * @property int $status
 * @property string $template_params
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|WhatsappTemplate newModelQuery()
 * @method static Builder|WhatsappTemplate newQuery()
 * @method static Builder|WhatsappTemplate query()
 * @method static Builder|WhatsappTemplate whereCampaignName($value)
 * @method static Builder|WhatsappTemplate whereCreatedAt($value)
 * @method static Builder|WhatsappTemplate whereId($value)
 * @method static Builder|WhatsappTemplate whereMediaUrl($value)
 * @method static Builder|WhatsappTemplate whereStatus($value)
 * @method static Builder|WhatsappTemplate whereTemplateParamsCount($value)
 * @method static Builder|WhatsappTemplate whereUpdatedAt($value)
 * @method static Builder|WhatsappTemplate whereUrl($value)
 * @method static Builder|WhatsappTemplate whereTemplateParams($value)
 * @mixin Eloquent
 */
class WhatsappTemplate extends Model
{
    protected $table = 'whatsapp_template';
    protected $connection = 'crafty_automation_mysql';
    protected $fillable = [
        'campaign_name',
        'template_params_count',
        'template_params',
        'media_url',
        'url',
    ];
    public $timestamps = true;

    public function getTemplateParamsAttribute($value)
    {
        return $value ? json_decode($value,true) : [];
    }

    public function getUrlAttribute($value): string
    {
        if($value) return HelperController::$mediaUrl . $value;


        return "";
    }

}
