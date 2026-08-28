<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormFields
{
    public const TYPES = ['text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox', 'file'];

    /**
     * Bersihkan & rapikan definisi field agar aman disimpan sebagai JSON.
     */
    public static function normalize(?array $fields): array
    {
        $clean = [];
        $usedKeys = [];

        foreach (array_values($fields ?? []) as $field) {
            $type = $field['type'] ?? 'text';

            if (! in_array($type, self::TYPES, true)) {
                continue;
            }

            $label = trim((string) ($field['label'] ?? '')) ?: null;
            $key = self::slugKey($field['key'] ?? '') ?: ($label !== null ? self::slugKey($label) : null);

            if ($key === null) {
                continue;
            }

            // Jamin keunikan key dalam satu form: duplikat diberi akhiran angka.
            $uniqueKey = $key;
            $suffix = 2;

            while (in_array($uniqueKey, $usedKeys, true)) {
                $uniqueKey = $key.'_'.$suffix;
                $suffix++;
            }

            $usedKeys[] = $uniqueKey;

            $clean[] = [
                'key' => $uniqueKey,
                'label' => $label ?? $uniqueKey,
                'type' => $type,
                'options' => array_values(array_filter(array_map('strval', $field['options'] ?? []))),
                'required' => (bool) ($field['required'] ?? false),
            ];
        }

        return $clean;
    }

    /**
     * Ubah teks menjadi kunci aman (huruf kecil, underscore, tanpa karakter khusus).
     * Mengembalikan string kosong bila tidak ada karakter valid.
     */
    public static function slugKey(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '_', $text) ?? '';
        $text = trim($text, '_');

        return $text;
    }

    /**
     * Validasi isian formulir kustom terhadap definisi field.
     *
     * @return array<string, string> nilai jawaban yang bersih (kunci → nilai).
     */
    public static function validate(Request $request, array $fields): array
    {
        $answers = [];
        $errors = [];

        foreach ($fields as $field) {
            $key = $field['key'];

            if ($field['type'] === 'file') {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);

                    $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'];
                    $allowedMimes = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ];
                    $maxBytes = 5 * 1024 * 1024;

                    $ext = strtolower($file->getClientOriginalExtension());
                    $valid = $file->isValid()
                        && $file->getSize() > 0
                        && $file->getSize() <= $maxBytes
                        && in_array($ext, $allowedExts, true)
                        && in_array($file->getMimeType(), $allowedMimes, true);

                    if (! $valid) {
                        $errors[$key] = "File pada bidang {$field['label']} tidak valid. Gunakan PDF/gambar maksimal 5MB.";

                        continue;
                    }

                    $answers[$key] = $file->store('events-answers', 'public');
                } elseif ($field['required']) {
                    $errors[$key] = "Bidang {$field['label']} wajib diisi.";
                }

                continue;
            }

            $value = $request->input($key);

            if ($field['required'] && ($value === null || trim((string) $value) === '')) {
                $errors[$key] = "Bidang {$field['label']} wajib diisi.";

                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if ($field['type'] === 'number' && ! is_numeric($value)) {
                $errors[$key] = "Bidang {$field['label']} harus berupa angka.";

                continue;
            }

            if (in_array($field['type'], ['select', 'radio'], true)
                && $field['options']
                && ! in_array((string) $value, $field['options'], true)) {
                $errors[$key] = "Nilai pada bidang {$field['label']} tidak valid.";

                continue;
            }

            $answers[$key] = (string) $value;
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $answers;
    }
}
