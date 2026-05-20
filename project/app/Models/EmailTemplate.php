<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\EmailTemplate
 *
 * @property int $id
 * @property string $name
 * @property string $email_template
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|EmailTemplate newModelQuery()
 * @method static Builder|EmailTemplate newQuery()
 * @method static Builder|EmailTemplate query()
 * @method static Builder|EmailTemplate whereCreatedAt($value)
 * @method static Builder|EmailTemplate whereEmailTemplate($value)
 * @method static Builder|EmailTemplate whereId($value)
 * @method static Builder|EmailTemplate whereName($value)
 * @method static Builder|EmailTemplate whereStatus($value)
 * @method static Builder|EmailTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmailTemplate extends Model
{
    protected $table = 'email_templates';
    protected $connection = 'crafty_automation_mysql';
    use HasFactory;
    protected $fillable = ['name','email_template','status'];
}