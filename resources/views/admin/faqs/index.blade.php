@extends('layouts.admin')

@section('title', 'Kelola FAQ - MarketLabs')

@section('page', 'Kelola FAQ')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola FAQ</h1>
        <p class="mt-1 text-sm text-slate-600">
            FAQ tampil di section "Pertanyaan yang Sering Ditanyakan" pada landing page.
            Urutan diurutkan dari nilai yang paling kecil. FAQ nonaktif tidak ditampilkan di halaman publik.
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

{{-- Tambah FAQ --}}
<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-bold text-slate-900">Tambah FAQ Baru</h2>
    <form action="{{ route('admin.faqs.store') }}" method="POST" class="mt-4 grid gap-4">
        @csrf
        <div>
            <label for="new-question" class="block text-sm font-semibold text-slate-700">Pertanyaan <span class="text-red-500">*</span></label>
            <input type="text" id="new-question" name="question" value="{{ old('question') }}" required placeholder="Contoh: Bagaimana cara melakukan booking pemeriksaan?"
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <div>
            <label for="new-answer" class="block text-sm font-semibold text-slate-700">Jawaban <span class="text-red-500">*</span></label>
            <textarea id="new-answer" name="answer" rows="3" maxlength="2000" required placeholder="Tuliskan jawaban..."
                      class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('answer') }}</textarea>
        </div>
        <div class="flex flex-wrap items-end gap-6">
            <div>
                <label for="new-sort_order" class="block text-sm font-semibold text-slate-700">Urutan</label>
                <input type="number" id="new-sort_order" name="sort_order" value="{{ old('sort_order', $faqs->max('sort_order') + 1) }}" min="0"
                       class="mt-1.5 w-28 rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
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
                + Tambah FAQ
            </button>
        </div>
    </form>
</div>

{{-- Daftar FAQ --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Urutan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pertanyaan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jawaban</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($faqs as $faq)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-sm font-bold text-emerald-700">{{ $faq->sort_order }}</span>
                        </td>
                        <td class="max-w-xs px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $faq->question }}</p>
                        </td>
                        <td class="max-w-md px-6 py-4">
                            <p class="line-clamp-2 text-sm text-slate-600">{{ $faq->answer }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $faq->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $faq->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="toggleEdit({{ $faq->id }})"
                                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                                    Edit
                                </button>
                                <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST"
                                      data-confirm="Hapus FAQ {{ $faq->question }}?" data-confirm-accept="Ya, Hapus">
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
                    <tr id="edit-form-{{ $faq->id }}" class="hidden border-t border-emerald-100 bg-emerald-50/50">
                        <td colspan="5" class="px-6 py-4">
                            <form action="{{ route('admin.faqs.update', $faq) }}" method="POST" class="grid gap-4">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Pertanyaan</label>
                                    <input type="text" name="question" value="{{ $faq->question }}" required
                                           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500">Jawaban</label>
                                    <textarea name="answer" rows="3" maxlength="2000" required
                                              class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ $faq->answer }}</textarea>
                                </div>
                                <div class="flex flex-wrap items-end gap-6">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500">Urutan</label>
                                        <input type="number" name="sort_order" value="{{ $faq->sort_order }}" min="0"
                                               class="mt-1 w-28 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    </div>
                                    <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" {{ $faq->is_active ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Aktif
                                    </label>
                                </div>
                                <div class="flex items-end justify-end gap-2">
                                    <button type="button" onclick="toggleEdit({{ $faq->id }})"
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
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                            Belum ada FAQ. Tambahkan FAQ pertama melalui form di atas.
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