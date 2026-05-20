<?php

namespace App\Models\Creator\Designer;

use App\Http\Controllers\Utils\HelperController;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesignSubmission extends Model
{
    protected $connection = 'crafty_creator_mysql';

    protected $fillable = [
        'string_id',
        'template_id',
        'designer_id',
        'user_id', // Renamed from app_user_uid
        'caricature_ids',
        'ratio',
        'width',
        'height',
        'title',
        'description',
        'category_id',
        'thumbs',
        'video',
        'designs',
        'msg',
        'is_live',
        'tags',
        'status',
        'designer_head_notes',
        'seo_head_notes',
        'designer_head_reviewed_by',
        'designer_head_reviewed_at',
        'seo_head_reviewed_by',
        'seo_head_reviewed_at',
        'published_at',
        'total_sales',
        'total_revenue',
        'crafty_design_id',
    ];

    protected $casts = [
        'thumbs' => 'array',
        'caricature_ids' => 'array',
        'tags' => 'array',
        'ratio' => 'float',
        'designer_head_reviewed_at' => 'datetime',
        'seo_head_reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'total_revenue' => 'decimal:2',
    ];

    /** Freelancer: designer_id = user_data.id (changed from users.id) */
    public function designer()
    {
        // Use UserData instead of User to avoid modifying users table
        // Note: design_submissions is in crafty_creator_mysql, user_data is in crafty_db (mysql)
        // Laravel handles cross-database relationships automatically
        return $this->belongsTo(UserData::class, 'designer_id');
    }

    /** Legacy: Keep old relationship for backward compatibility if needed */
    public function designerUser()
    {
        // This can be used if we need to access users table (but won't modify it)
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function designerHeadReviewer()
    {
        return $this->belongsTo(User::class, 'designer_head_reviewed_by');
    }

    public function seoHeadReviewer()
    {
        return $this->belongsTo(User::class, 'seo_head_reviewed_by');
    }

    public function seoDetails()
    {
        return $this->hasOne(DesignSeoDetail::class, 'design_submission_id');
    }

    /**
     * First preview path: thumbs.thumb → original → 0 → preview → first extra_* → first other scalar value.
     */
    public function primaryPreviewStoragePath(): ?string
    {
        $thumbs = $this->thumbs;

        if (!is_array($thumbs) || $thumbs === []) {
            return null;
        }

        foreach (['thumb', 'original', 'preview'] as $k) {
            if (!empty($thumbs[$k])) {
                return (string) $thumbs[$k];
            }
        }

        if (!empty($thumbs[0])) {
            return (string) $thumbs[0];
        }

        $extra = [];
        foreach ($thumbs as $key => $val) {
            if (is_string($key) && preg_match('/^extra_\d+$/', $key) && $val !== null && $val !== '' && !is_array($val)) {
                $extra[$key] = (string) $val;
            }
        }
        if ($extra !== []) {
            ksort($extra, SORT_NATURAL);

            return reset($extra);
        }

        foreach ($thumbs as $val) {
            if ($val === null || $val === '' || is_array($val)) {
                continue;
            }

            return (string) $val;
        }

        return null;
    }

    /**
     * Public URL for panel/API (handles http(s), /public uploads, and storage disk paths).
     * Uses HelperController::generatePublicUrl for consistency
     */
    public function previewImageUrl(): ?string
    {
        $path = $this->primaryPreviewStoragePath();
        if (!$path) {
            return null;
        }

        return HelperController::generatePublicUrl($path);
    }

    /**
     * Check if media source exists (for panel preview rendering)
     * Used by views to determine if preview should be shown
     */
    public static function panelMediaSourceExists(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }
        $path = trim($path);

        // Base64 data URIs always exist
        if (Str::startsWith($path, 'data:image/') || Str::startsWith($path, 'data:video/')) {
            return true;
        }

        // Remote URLs always exist (assume valid)
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return true;
        }

        // Check if file exists in storage
        $rel = ltrim($path, '/');
        if (Str::startsWith($rel, 'storage/')) {
            $rel = substr($rel, strlen('storage/'));
        }

        return Storage::disk('public')->exists($rel);
    }

    /**
     * Get display URL for admin panel previews
     * Uses HelperController::generatePublicUrl for consistency
     */
    public static function panelMediaDisplayUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $p = trim($path);

        // Return base64 data URIs as-is
        if (Str::startsWith($p, 'data:image/') || Str::startsWith($p, 'data:video/')) {
            return $p;
        }

        // Use HelperController for all other paths
        return HelperController::generatePublicUrl($p);
    }
}

