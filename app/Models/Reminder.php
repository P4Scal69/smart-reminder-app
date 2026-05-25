<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Reminder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'title',
        'description',
        'trigger_type',
        'is_active',
        'triggered_at',
        'trigger_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'triggered_at' => 'datetime',
        'trigger_count' => 'integer',
    ];

    /**
     * Get the user that owns the reminder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location that the reminder belongs to.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Scope a query to only include active reminders.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive reminders.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to get reminders for a specific user.
     *
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to get untriggered reminders.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUntriggered(Builder $query): Builder
    {
        return $query->whereNull('triggered_at');
    }

    /**
     * Scope a query to get recently triggered reminders.
     *
     * @param Builder $query
     * @param int $hours Number of hours to look back
     * @return Builder
     */
    public function scopeRecentlyTriggered(Builder $query, int $hours = 24): Builder
    {
        return $query->where('triggered_at', '>=', now()->subHours($hours));
    }

    /**
     * Mark the reminder as triggered.
     *
     * @return bool
     */
    public function markAsTriggered(): bool
    {
        $this->triggered_at = now();
        $this->trigger_count += 1;
        return $this->save();
    }

    /**
     * Activate the reminder.
     *
     * @return bool
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate the reminder.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Toggle the reminder's active status.
     *
     * @return bool
     */
    public function toggleActive(): bool
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Check if the reminder should be triggered based on user's location.
     *
     * @param float $userLat User's current latitude
     * @param float $userLng User's current longitude
     * @return bool
     */
    public function shouldTrigger(float $userLat, float $userLng): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Load location if not already loaded
        if (!$this->relationLoaded('location')) {
            $this->load('location');
        }

        return $this->location->isWithinGeofence($userLat, $userLng);
    }
}
