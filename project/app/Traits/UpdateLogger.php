<?php

namespace App\Traits;

use App\Models\AdminChangesLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Trait UpdateLogger
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait UpdateLogger
{
    public static function bootUpdateLogger()
    {
        if (!is_subclass_of(static::class, Model::class)) {
            return;
        }

        static::updated(function ($model) {
            try {
                // Get changed attributes (new values)
                $changes = $model->getChanges();

                // Exclude some fields from logging
                $exclude = ['updated_at', 'created_at', 'password', 'remember_token'];
                foreach ($exclude as $key) {
                    unset($changes[$key]);
                }

                if (empty($changes)) {
                    return;
                }

                // Get old values for the changed fields
                $original = $model->getOriginal();
                $oldValues = [];
                foreach ($changes as $key => $newValue) {
                    $oldValues[$key] = $original[$key] ?? null;
                }

                // Save to log table
                AdminChangesLog::create([
                    'emp_id' => Auth::id(),
                    'model' => get_class($model),
                    'model_id' => $model->getKey(),
                    'old_values' => json_encode($oldValues),
                    'updated_fields' => json_encode($changes),
                    'ip_address' => Request::ip(),
                ]);
            } catch (\Throwable $e) {
                Log::error('UpdateLogger error: ' . $e->getMessage());
            }
        });
    }
}