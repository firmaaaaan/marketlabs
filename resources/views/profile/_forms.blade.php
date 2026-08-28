<div>
    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Profil Saya</h1>
    <p class="mt-1 text-sm text-slate-600">Kelola informasi akun dan kata sandi Anda.</p>

    @if (session('success'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 space-y-6">
        {{-- Kode Partisipan --}}
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-6 shadow-sm sm:p-8">
            <h2 class="text-lg font-bold text-slate-900">Kode Partisipan</h2>
            <p class="mt-1 text-sm text-slate-500">Bagikan kode ini kepada teman agar mereka bisa didaftarkan ke event atas nama Anda.</p>
            <div class="mt-4 flex items-center gap-3">
                <code id="participant-code"
                     class="rounded-lg bg-white border border-emerald-200 px-5 py-3 text-lg font-bold tracking-widest text-emerald-800 select-all">
                    {{ Auth::user()->participant_code }}
                </code>
                <button type="button" onclick="copyCode()"
                        class="shrink-0 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Salin
                </button>
            </div>
            <p id="copy-msg" class="mt-2 hidden text-xs font-semibold text-emerald-600">Berhasil disalin!</p>
        </div>

        {{-- Informasi akun --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-lg font-bold text-slate-900">Informasi Akun</h2>
            <p class="mt-1 text-sm text-slate-500">Nama dan email yang digunakan untuk login dan ditampilkan pada peminjaman.</p>

            <form action="{{ route('profile.update') }}" method="POST" class="mt-6 grid gap-5 sm:grid-cols-2">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required
                           class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required
                           class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nim_nip" class="block text-sm font-semibold text-slate-700">NIM / NIP / NIDN / NIK <span class="text-red-500">*</span></label>
                    <input type="text" id="nim_nip" name="nim_nip" value="{{ old('nim_nip', Auth::user()->nim_nip) }}" required
                           placeholder="Contoh: 2101234567"
                           class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    @error('nim_nip')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="institution" class="block text-sm font-semibold text-slate-700">Instansi <span class="text-red-500">*</span></label>
                    <input type="text" id="institution" name="institution" value="{{ old('institution', Auth::user()->institution) }}" required
                           placeholder="Contoh: Universitas Contoh / PT Riset Nusantara"
                           class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    @error('institution')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-slate-700">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}" required
                           placeholder="Contoh: 081234567890"
                           class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <button type="submit"
                            class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Ubah kata sandi --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-lg font-bold text-slate-900">Ubah Kata Sandi</h2>
            <p class="mt-1 text-sm text-slate-500">Gunakan kata sandi baru yang kuat dan tidak dipakai di layanan lain.</p>

            <form action="{{ route('profile.password') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="current_password" class="block text-sm font-semibold text-slate-700">Kata Sandi Saat Ini <span class="text-red-500">*</span></label>
                    <div class="relative mt-1.5">
                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 pr-11 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <button type="button" onclick="togglePassword('current_password', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" tabindex="-1">
                            <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg class="eye-closed h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <div class="relative mt-1.5">
                            <input type="password" id="password" name="password" required autocomplete="new-password"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 pr-11 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            <button type="button" onclick="togglePassword('password', this)"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" tabindex="-1">
                                <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="eye-closed h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Ulangi Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <div class="relative mt-1.5">
                            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 pr-11 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            <button type="button" onclick="togglePassword('password_confirmation', this)"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" tabindex="-1">
                                <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="eye-closed h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-700">
                        Ubah Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function copyCode() {
            var code = document.getElementById('participant-code').textContent.trim();
            navigator.clipboard.writeText(code).then(function () {
                var msg = document.getElementById('copy-msg');
                msg.classList.remove('hidden');
                setTimeout(function () { msg.classList.add('hidden'); }, 2000);
            });
        }

        function togglePassword(fieldId, btn) {
            const input = document.getElementById(fieldId);
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</div>
