<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    /**
     * Get the users for the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if this role has admin privileges
     */
    public function hasAdminPrivileges(): bool
    {
        return in_array($this->name, ['admin']);
    }

    /**
     * Check if this role can moderate content
     */
    public function canModerate(): bool
    {
        return in_array($this->name, ['admin', 'moderator']);
    }

    /**
     * Check if this role can manage church content
     */
    public function canManageChurch(): bool
    {
        return in_array($this->name, ['church_admin']);
    }

    /**
     * Scope to get roles by name
     */
    public function scopeOfType($query, string $roleName)
    {
        return $query->where('name', $roleName);
    }

    /**
     * Static method to get role by name
     */
    public static function getByName(string $roleName): ?self
    {
        return static::where('name', $roleName)->first();
    }

    /**
     * Check if role is a specific type by name
     */
    public function isType(string $roleName): bool
    {
        return $this->name === $roleName;
    }
}
