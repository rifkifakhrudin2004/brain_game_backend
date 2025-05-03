<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Filament method to allow user login to Filament panel
     */
    public function canAccessFilament(): bool
    {
        return true; // atau logika berdasarkan role
    }

    /**
     * Determine if the user can access a specific panel.
     *
     * @param \Filament\Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Kamu bisa menambahkan logika untuk menentukan apakah user dapat mengakses panel ini.
        // Contoh: hanya user dengan role 'admin' yang bisa mengakses
        return $this->role === 'admin';
    }

    /**
     * Define the user name shown in Filament panel (for newer Filament versions)
     */
    public function getFilamentName(): string
    {
        return $this->getAttribute('username') ?: 'Guest';
    }
    
    /**
     * For compatibility with various Filament versions
     */
    public function getUserName(): string
    {
        return $this->getAttribute('username') ?: 'Guest';
    }
    
    /**
     * For compatibility with some Filament versions that look for name property
     */
    public function getNameAttribute(): string
    {
        return $this->getAttribute('username') ?: 'Guest';
    }
    
    /**
     * Another method that might be required by some Filament versions
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return null; // Return avatar URL if available
    }
    
    /**
     * Make sure there's a default name if username is null
     */
    public function name(): string
    {
        return $this->getAttribute('username') ?: 'Guest';
    }
}