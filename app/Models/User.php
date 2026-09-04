<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, LogsActivity, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_LABORAN = 'laboran';

    public const ROLE_USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'nim_nip',
        'institution',
        'phone',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'role',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * Buat kode partisipan acak yang unik (mis. ML-7KQ2P9XZ).
     */
    public static function generateParticipantCode(): string
    {
        do {
            $code = 'ML-'.strtoupper(Str::random(8));
        } while (self::where('participant_code', $code)->exists());

        return $code;
    }

    /**
     * Determine if the user has the given role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Daftar role yang tersedia.
     *
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_LABORAN => 'Laboran',
            self::ROLE_USER => 'User',
        ];
    }

    public static function roleLabel(string $role): string
    {
        return self::roles()[$role] ?? $role;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::roleLabel($this->role);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN || $this->role === self::ROLE_SUPERADMIN;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isLaboran(): bool
    {
        return $this->role === self::ROLE_LABORAN;
    }

    /**
     * Cek apakah profil pengguna sudah lengkap.
     */
    public function isProfileComplete(): bool
    {
        return filled($this->nim_nip)
            && filled($this->institution)
            && filled($this->phone);
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeLaboran($query)
    {
        return $query->where('role', self::ROLE_LABORAN);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
