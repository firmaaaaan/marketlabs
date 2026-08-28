<?php

namespace App\Models\Concerns;

use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            $model->recordActivity('create');
        });

        static::updated(function (Model $model) {
            $model->recordActivity('update');
        });

        static::deleted(function (Model $model) {
            $model->recordActivity('delete');
        });
    }

    protected function recordActivity(string $action): void
    {
        $user = auth()->user();

        if (! $user || ! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_LABORAN], true)) {
            return;
        }

        ActivityLogger::log($user, $action, $this->activityDescription($action), $this, $this->activityProperties());
    }

    protected function activityDescription(string $action): string
    {
        $verb = match ($action) {
            'create' => 'menambahkan',
            'update' => 'memperbarui',
            'delete' => 'menghapus',
            default => $action,
        };

        $noun = $this->activityLogNoun();
        $label = $this->activityLogLabel();

        if ($action === 'update') {
            $fields = array_values(array_diff(array_keys($this->getChanges()), ['updated_at']));

            if ($label) {
                return $fields
                    ? "{$verb} {$noun} '{$label}' (".implode(', ', $fields).')'
                    : "{$verb} {$noun} '{$label}'";
            }

            return $fields ? "{$verb} {$noun} (".implode(', ', $fields).')' : "{$verb} {$noun}";
        }

        return $label ? "{$verb} {$noun} '{$label}'" : "{$verb} {$noun}";
    }

    /**
     * Property tambahan (old/new value) untuk update.
     *
     * @return array<string, mixed>
     */
    protected function activityProperties(): array
    {
        if (empty($this->getChanges())) {
            return [];
        }

        $changes = $this->getChanges();
        $old = [];
        $new = [];

        foreach ($changes as $key => $value) {
            if ($key === 'updated_at') {
                continue;
            }
            $new[$key] = $value;
            $old[$key] = $this->getOriginal($key);
        }

        return ['old' => $old, 'new' => $new];
    }

    protected function activityLogNoun(): string
    {
        return static::activityLogNounMap()[static::class]
            ?? Str::headline(class_basename($this));
    }

    protected function activityLogLabel(): ?string
    {
        foreach (['name', 'code', 'title', 'question', 'key', 'email'] as $attribute) {
            if (isset($this->{$attribute}) && $this->{$attribute} !== null && $this->{$attribute} !== '') {
                return (string) $this->{$attribute};
            }
        }

        return null;
    }

    /**
     * @return array<class-string, string>
     */
    public static function activityLogNounMap(): array
    {
        return [
            'App\Models\BenchFeeRate' => 'tarif bench fee',
            'App\Models\Borrowing' => 'peminjaman',
            'App\Models\BorrowingItem' => 'item peminjaman',
            'App\Models\ExaminerWeeklySchedule' => 'jadwal pemeriksa',
            'App\Models\Event' => 'event',
            'App\Models\EventRegistration' => 'pendaftar event',
            'App\Models\Faq' => 'FAQ',
            'App\Models\FooterLogo' => 'logo footer',
            'App\Models\HealthCheckup' => 'pemeriksaan kesehatan',
            'App\Models\HealthTestType' => 'jenis pemeriksaan',
            'App\Models\Laboratorium' => 'laboratorium',
            'App\Models\ResearchLogbook' => 'logbook riset',
            'App\Models\ResearchProposal' => 'permohonan riset',
            'App\Models\ResearchProposalMember' => 'anggota riset',
            'App\Models\SampleForm' => 'bentuk sampel',
            'App\Models\SampleTest' => 'pengujian sampel',
            'App\Models\SampleTestItem' => 'item pengujian sampel',
            'App\Models\SampleType' => 'jenis sampel',
            'App\Models\SampleUnit' => 'satuan sampel',
            'App\Models\Setting' => 'pengaturan',
            'App\Models\TestParameter' => 'parameter pengujian',
            'App\Models\Testimonial' => 'testimoni',
            'App\Models\Tool' => 'alat',
            'App\Models\ToolCategory' => 'kategori alat',
            'App\Models\User' => 'user',
        ];
    }
}
