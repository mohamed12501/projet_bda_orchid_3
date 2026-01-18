<?php

namespace App\Models;

use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions'          => 'array',
        'email_verified_at'    => 'datetime',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
           'id'         => Where::class,
           'name'       => Like::class,
           'email'      => Like::class,
           'updated_at' => WhereDateStartEnd::class,
           'created_at' => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'updated_at',
        'created_at',
    ];

    public function meta(): HasOne
    {
        return $this->hasOne(UsersMeta::class, 'id', 'id');
    }

    /**
     * Check if user has access to platform based on users_meta role
     */
    public function hasAccess(string $permit, bool $cache = true): bool
    {
        // Grant access to all users with any role in users_meta
        // This allows all our custom roles to access the platform
        $meta = $this->meta ?? UsersMeta::find($this->id);
        if ($meta && $meta->role) {
            return true;
        }

        // Fallback to parent implementation for Orchid roles
        return parent::hasAccess($permit, $cache);
    }

    /**
     * Check if user has any access to platform
     */
    public function hasAnyAccess($permissions, bool $cache = true): bool
    {
        // Grant access to all users with any role in users_meta
        $meta = $this->meta ?? UsersMeta::find($this->id);
        if ($meta && $meta->role) {
            return true;
        }

        // Fallback to parent implementation
        return parent::hasAnyAccess($permissions, $cache);
    }
}
