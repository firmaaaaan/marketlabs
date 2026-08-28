<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\EventNotification;
use App\Support\FormFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /** Maksimal jumlah teman yang bisa didaftarkan satu akun untuk satu event. */
    public const MAX_PROXY_PER_EVENT = 5;
    public function index()
    {
        $events = Event::withCount([
            'registrations' => fn ($q) => $q->where('status', EventRegistration::STATUS_REGISTERED),
        ])
            ->whereIn('status', [Event::STATUS_ACTIVE, Event::STATUS_COMPLETED])
            ->latest()
            ->paginate(12);

        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        abort_unless(in_array($event->status, [Event::STATUS_ACTIVE, Event::STATUS_COMPLETED]), 404);

        $event->loadCount([
            'registrations' => fn ($q) => $q->where('status', EventRegistration::STATUS_REGISTERED),
        ]);

        $alreadyRegistered = $event->isRegisteredBy(auth()->user());

        return view('events.show', compact('event', 'alreadyRegistered'));
    }

    public function store(Request $request, Event $event)
    {
        abort_unless($event->status === Event::STATUS_ACTIVE, 403);

        if (! $event->is_open) {
            return back()->with('error', 'Pendaftaran event ini sudah ditutup.');
        }

        $fields = FormFields::normalize($event->form_fields);

        // Mode: daftarkan teman (atas nama akun teman yang sudah punya kode partisipan).
        if ($request->input('register_for') === 'friend') {
            return $this->storeFriend($request, $event, $fields);
        }

        if ($event->isRegisteredBy(auth()->user())) {
            return back()->with('error', 'Anda sudah terdaftar pada event ini.');
        }

        $answers = FormFields::validate($request, $fields);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'status' => EventRegistration::STATUS_PENDING,
            'answers' => $answers,
            'attendance_token' => Str::random(32),
        ]);

        return redirect()->route('events.my')
            ->with('success', "Berhasil mendaftar pada '{$event->title}'. Menunggu konfirmasi dari admin.");
    }

    /**
     * Pendaftaran atas nama satu atau lebih teman berdasarkan kode partisipan.
     */
    protected function storeFriend(Request $request, Event $event, array $fields)
    {
        $validated = $request->validate([
            'friend_codes'   => ['required', 'array', 'min:1', 'max:'.self::MAX_PROXY_PER_EVENT],
            'friend_codes.*' => ['required', 'string'],
        ]);

        $codes = array_unique($validated['friend_codes']);
        $answers = FormFields::validate($request, $fields);

        // Validasi semua kode sekaligus sebelum membuat registrasi.
        $friends = [];
        $errors = [];
        $alreadyRegisteredNames = [];

        foreach ($codes as $code) {
            $friend = User::where('participant_code', $code)->first();

            if (! $friend) {
                $errors[] = "Kode '{$code}' tidak ditemukan.";
                continue;
            }

            if ($friend->id === auth()->id()) {
                $errors[] = "Kode '{$code}' adalah milik Anda sendiri.";
                continue;
            }

            if ($friend->role !== User::ROLE_USER) {
                $errors[] = " '{$friend->name}' bukan akun peserta.";
                continue;
            }

            if ($event->isRegisteredBy($friend)) {
                $alreadyRegisteredNames[] = $friend->name;
                continue;
            }

            $friends[] = $friend;
        }

        if ($alreadyRegisteredNames) {
            $errors[] = 'Sudah terdaftar: '.implode(', ', $alreadyRegisteredNames).'.';
        }

        if ($errors) {
            return back()->withInput()->with('error', implode(' ', $errors));
        }

        if ($friends === []) {
            return back()->with('error', 'Tidak ada kode teman yang valid untuk didaftarkan.');
        }

        $proxyCount = $event->registrations()
            ->where('registered_by', auth()->id())
            ->where('status', EventRegistration::STATUS_REGISTERED)
            ->count();

        if ($proxyCount + count($friends) > self::MAX_PROXY_PER_EVENT) {
            $sisa = self::MAX_PROXY_PER_EVENT - $proxyCount;
            return back()->with('error', "Anda sudah mendaftarkan {$proxyCount} teman. Sisa kuota hanya {$sisa} orang lagi.");
        }

        // Daftarkan diri sendiri juga jika dicentang & belum terdaftar.
        $selfRegistered = false;
        if ($request->boolean('register_self') && ! $event->isRegisteredBy(auth()->user())) {
            EventRegistration::create([
                'event_id'         => $event->id,
                'user_id'          => auth()->id(),
                'status'           => EventRegistration::STATUS_PENDING,
                'answers'          => $answers,
                'attendance_token' => Str::random(32),
            ]);
            $selfRegistered = true;
        }

        $registered = [];

        foreach ($friends as $friend) {
            EventRegistration::create([
                'event_id'         => $event->id,
                'user_id'          => $friend->id,
                'registered_by'    => auth()->id(),
                'status'           => EventRegistration::STATUS_PENDING,
                'answers'          => $answers,
                'attendance_token' => Str::random(32),
            ]);

            $friend->notify(new EventNotification(
                'Pendaftaran Event',
                "Anda didaftarkan oleh {$request->user()->name} pada event '{$event->title}'. Silakan cek presensi Anda di Riwayat Event.",
                route('events.my'),
                notifyViaEmail: true,
            ));

            $registered[] = $friend->name;
        }

        $count = count($registered);
        $names = implode(', ', $registered);
        $total = $selfRegistered ? $count + 1 : $count;

        $msg = $selfRegistered
            ? "Berhasil mendaftarkan diri Anda dan {$count} teman ({$names}) pada '{$event->title}'."
            : "Berhasil mendaftarkan {$count} teman ({$names}) pada '{$event->title}'.";

        return redirect()->route('events.my')->with('success', $msg);
    }

    /**
     * Cari akun teman berdasarkan kode partisipan untuk auto-isi form pendaftaran.
     */
    public function searchFriend(Request $request, Event $event)
    {
        $request->validate(['kode' => ['required', 'string']]);

        $friend = User::where('participant_code', $request->input('kode'))->first();

        if (! $friend || $friend->id === auth()->id() || $friend->role !== User::ROLE_USER) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'name' => $friend->name,
            'email' => $friend->email,
            'nim_nip' => $friend->nim_nip,
            'institution' => $friend->institution,
        ]);
    }

    public function my()
    {
        $registrations = EventRegistration::with('event')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        $proxied = EventRegistration::with(['event', 'user'])
            ->where('registered_by', auth()->id())
            ->latest()
            ->paginate(10);

        return view('events.my', compact('registrations', 'proxied'));
    }

    public function attendance(string $token)
    {
        $registration = EventRegistration::with('event')
            ->where('attendance_token', $token)
            ->firstOrFail();

        abort_unless(
            $registration->user_id === auth()->id() || auth()->user()?->isAdmin(),
            403
        );

        abort_unless($registration->status === EventRegistration::STATUS_REGISTERED, 403);

        abort_unless($registration->event->attendance_enabled, 403);

        return view('events.attendance', compact('registration'));
    }

    public function attendanceStore(Request $request, string $token)
    {
        $registration = EventRegistration::with('event')
            ->where('attendance_token', $token)
            ->firstOrFail();

        abort_unless(
            $registration->user_id === auth()->id() || auth()->user()?->isAdmin(),
            403
        );

        abort_unless($registration->status === EventRegistration::STATUS_REGISTERED, 403);

        abort_unless($registration->event->attendance_enabled, 403);

        if ($registration->attended_at) {
            return redirect()->route('events.my')->with('success', 'Presensi Anda sudah dicatat sebelumnya.');
        }

        $answers = FormFields::validate($request, FormFields::normalize($registration->event->attendance_fields));

        $registration->update([
            'attendance_answers' => $answers,
            'attended_at' => now(),
        ]);

        return redirect()->route('events.my')
            ->with('success', 'Presensi berhasil dicatat. Terima kasih sudah hadir!');
    }

    public function certificate(EventRegistration $registration)
    {
        abort_unless($registration->has_certificate, 404);

        abort_unless(
            $registration->user_id === auth()->id() || auth()->user()?->isAdmin(),
            403
        );

        return view('events.certificate', compact('registration'));
    }

    public function certificateDownload(EventRegistration $registration)
    {
        abort_unless($registration->has_certificate, 404);

        abort_unless(
            $registration->user_id === auth()->id() || auth()->user()?->isAdmin(),
            403
        );

        $back = request()->boolean('back') && $registration->has_certificate_back;

        $path = Storage::disk('public')->path($back ? $registration->certificate_back_path : $registration->certificate_path);
        $name = 'sertifikat-'.Str::slug($registration->event->title).($back ? '-belakang' : '').'.png';

        return response()->download($path, $name);
    }
}
