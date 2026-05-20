<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Candidate submission; may reference a specific job or be general (job_id null).
 */
class JobApplication extends Model
{
    protected $connection = 'mysql';

    use HasFactory;

    protected $fillable = [
        'job_id',
        'name',
        'email',
        'phone',
        'resume',
        'resume_link',
        'cover_letter',
        'portfolio_link',
    ];



    /**
     * Optional link to the role applied for.
     */
    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'job_id');
    }
}

