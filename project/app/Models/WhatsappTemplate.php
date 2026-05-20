<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\WhatsappTemplate
 *
 * @property int $id
 * @property string $campaign_name
 * @property int $template_params_count
 * @property int|null $media_url
 * @property string|null $url
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
 * @mixin \Eloquent
 */
class WhatsappTemplate extends Model
{
    protected $table = 'whatsapp_template';
    protected $connection = 'crafty_automation_mysql';
    protected $fillable = [
        'campaign_name',
        'template_params_count',
        'media_url',
        'url',
    ];
    public $timestamps = true;

}