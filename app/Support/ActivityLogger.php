<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Catat sebuah aktivitas ke tabel activity_logs.
     */
    public static function log(
        User $user,
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        array $properties = []
    ): ?ActivityLog {
        if (! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_LABORAN], true)) {
            // Log login/logout for regular users, skip other actions.
            if (! in_array($action, ['login', 'logout'], true)) {
                return null;
            }
        }

        return ActivityLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role,
            'action' => $action,
            'description' => $description ?? $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Label aksi untuk filter tampilan log.
     *
     * @return array<string, string>
     */
    public static function actionLabels(): array
    {
        return [
            'create' => 'Menambahkan',
            'update' => 'Memperbarui',
            'delete' => 'Menghapus',
            'login' => 'Login',
            'logout' => 'Logout',
        ];
    }
}
