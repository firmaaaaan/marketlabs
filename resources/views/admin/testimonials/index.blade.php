@extends('layouts.admin')

@section('title', 'Kelola Testimoni - MarketLabs')

@section('page', 'Kelola Testimoni')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola Testimoni</h1>
        <p class="mt-1 text-sm text-slate-600">
            Testimoni tampil di section "Apa Kata Pengguna Kami" pada landing page.
            Testimoni nonaktif tidak akan ditampilkan di halaman publik.
        </p>
    </div>
</div>

@if (session('success'))
    <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-sm font-bold text-red-700">Periksa kembali isian Anda:</p>
        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Tambah testimoni --}}
<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-bold text-slate-900">Tambah Testimoni Baru</h2>
    <form action="{{ route('admin.testimonials.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2">
        @csrf
        <div>
            <label for="new-name" class="block text-sm font-semibold text-slate-700">Nama <span class="text-red-500">*</span></label>
            <input type="text" id="new-name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Dr. Ratna Dewi"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div>
            <label for="new-role" class="block text-sm font-semibold text-slate-700">Peran / Instansi</label>
            <input type="text" id="new-role" name="role" value="{{ old('role') }}" placeholder="Contoh: Peneliti · Universitas Padjadjaran"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div class="sm:col-span-2">
            <label for="new-quote" class="block text-sm font-semibold text-slate-700">Isi Testimoni <span class="text-red-500">*</span></label>
            <textarea id="new-quote" name="quote" rows="3" maxlength="1000" required placeholder="Tuliskan pengalaman pengguna..."
                      class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('quote') }}</textarea>
        </div>
        <div class="flex items-end gap-6">
            <div>
                <label for="new-rating" class="block text-sm font-semibold text-slate-700">Rating</label>
                <select id="new-rating" name="rating"
                        class="mt-1.5 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ old('rating', 5) == $i ? 'selected' : '' }}>{{ $i }} bintang</option>
                    @endfor
                </select>
            </div>
            <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" checked
                       class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                Aktif
            </label>
        </div>
        <div class="flex items-end justify-end">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                + Tambah Testimoni
            </button>
        </div>
    </form>
</div>

{{-- Daftar testimoni --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Peran</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Testimoni</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($testimonials as $testimonial)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $testimonial->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $testimonial->role ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="flex items-center gap-0.5 text-amber-400">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="h-3.5 w-3.5 {{ $i <= $testimonial->rating ? '' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                    </svg>
                                @endfor
                            </span>
                        </td>
                        <td class="max-w-xs px-6 py-4">
                            <p class="line-clamp-2 text-sm text-slate-600">&ldquo;{{ $testimonial->quote }}&rdquo;</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $testimonial->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $testimonial->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="toggleEdit({{ $testimonial->id }})"
                                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                    Edit
                                </button>
                                <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST"
                                      data-confirm="Hapus testimoni {{ $testimonial->name }}?" data-confirm-accept="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr id="edit-form-{{ $testimonial->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50">
                        <td colspan="6" class="px-6 py-4">
                            <form action="{{ route('admin.testimonials.update', $testimonial) }}" method="POST" class="grid gap-4 sm:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Nama</label>
                                    <input type="text" name="name" value="{{ $testimonial->name }}" required
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Peran / Instansi</label>
                                    <input type="text" name="role" value="{{ $testimonial->role }}" placeholder="Opsional"
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-500">Isi Testimoni</label>
                                    <textarea name="quote" rows="3" maxlength="1000" required
                                              class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ $testimonial->quote }}</textarea>
                                </div>
                                <div class="flex flex-wrap items-end gap-6">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500">Rating</label>
                                        <select name="rating"
                                                class="mt-1 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}" {{ $testimonial->rating == $i ? 'selected' : '' }}>{{ $i }} bintang</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" {{ $testimonial->is_active ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Aktif
                                    </label>
                                </div>
                                <div class="flex items-end justify-end gap-2">
                                    <button type="button" onclick="toggleEdit({{ $testimonial->id }})"
                                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                            Belum ada testimoni. Tambahkan testimoni pertama melalui form di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function toggleEdit(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form) form.classList.toggle('hidden');
    }
</script>
@endpush

@endsection
