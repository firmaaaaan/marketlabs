<?php

namespace App\Http\Controllers;

use App\Models\ResearchLogbook;
use App\Models\ResearchProposal;
use App\Models\ResearchProposalMember;
use App\Models\Tool;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResearchProposalController extends Controller
{
    public function index()
    {
        $memberProposalIds = ResearchProposalMember::where('user_id', auth()->id())
            ->pluck('research_proposal_id');

        $proposals = ResearchProposal::where('user_id', auth()->id())
            ->orWhereIn('id', $memberProposalIds)
            ->latest()
            ->paginate(10);

        return view('research.index', compact('proposals'));
    }

    public function create()
    {
        $user = auth()->user();
        $tools = Tool::active()->orderBy('name')->get();

        return view('research.create', compact('user', 'tools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'field' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:5000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'institution' => ['required', 'string', 'max:255'],
            'nim_nip' => ['required', 'string', 'max:50'],
            'customer_type' => ['required', Rule::in(array_keys(ResearchProposal::customerTypes()))],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            'letter' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            'replacement_letter' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            'needs_laboran' => ['sometimes', 'boolean'],
            'members' => ['nullable', 'array'],
            'members.*.name' => ['required_with:members', 'string', 'max:255'],
            'members.*.role' => ['nullable', 'string', 'max:100'],
            'members.*.user_id' => ['nullable', 'string'],
            'tools' => ['nullable', 'array'],
            'tools.*' => ['required', 'string', 'exists:tools,id'],
            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['required', 'integer', 'min:1'],
            'days' => ['nullable', 'array'],
            'days.*' => ['required', 'integer', 'min:1'],
            'bench_fee_level' => ['required', Rule::in(['S1', 'S2/S3'])],
            'bench_fee_type' => ['required', Rule::in(['dalam', 'luar'])],
            'bench_fee_category' => ['required', Rule::in(array_keys(ResearchProposal::benchFeeCategories()))],
        ]);

        $benchFee = ResearchProposal::calculateBenchFee(
            $validated['bench_fee_level'],
            $validated['bench_fee_type'],
            $validated['bench_fee_category'],
            $validated['start_date'],
            $validated['end_date'],
        );

        $userName = Str::slug(auth()->user()->name);
        $uniqueSuffix = time().'-'.Str::random(6);
        $ext = fn ($file) => $file->getClientOriginalExtension();

        $documentPath = $request->hasFile('document')
            ? $request->file('document')->storeAs('research-documents', 'dokumen-pendukung-'.$userName.'-'.$uniqueSuffix.'.'.strtolower($ext($request->file('document'))))
            : null;

        $letterPath = $request->file('letter')->storeAs('research-letters', 'surat-permohonan-'.$userName.'-'.$uniqueSuffix.'.'.strtolower($ext($request->file('letter'))));

        $replacementLetterPath = $request->hasFile('replacement_letter')
            ? $request->file('replacement_letter')->storeAs('research-letters', 'surat-penggantian-'.$userName.'-'.$uniqueSuffix.'.'.strtolower($ext($request->file('replacement_letter'))))
            : null;

        $needsLaboran = $request->boolean('needs_laboran');

        $proposal = DB::transaction(function () use ($validated, $documentPath, $letterPath, $replacementLetterPath, $benchFee, $needsLaboran) {
            $proposal = ResearchProposal::create([
                'user_id' => auth()->id(),
                'code' => $this->generateCode(),
                'title' => $validated['title'],
                'field' => $validated['field'],
                'description' => $validated['description'],
                'objectives' => $validated['objectives'] ?? null,
                'institution' => $validated['institution'] ?? auth()->user()->institution,
                'nim_nip' => $validated['nim_nip'] ?? auth()->user()->nim_nip,
                'customer_type' => $validated['customer_type'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => ResearchProposal::STATUS_PENDING,
                'document_path' => $documentPath,
                'letter_path' => $letterPath,
                'replacement_letter_path' => $replacementLetterPath,
                'bench_fee' => $benchFee,
                'bench_fee_level' => $validated['bench_fee_level'],
                'bench_fee_type' => $validated['bench_fee_type'],
                'bench_fee_category' => $validated['bench_fee_category'],
                'needs_laboran' => $needsLaboran,
            ]);

            // Anggota penelitian.
            foreach ($validated['members'] ?? [] as $member) {
                if (trim((string) ($member['name'] ?? '')) === '') {
                    continue;
                }

                ResearchProposalMember::create([
                    'research_proposal_id' => $proposal->id,
                    'user_id' => $member['user_id'] ?? null,
                    'name' => trim($member['name']),
                    'role' => trim((string) ($member['role'] ?? '')) !== '' ? trim($member['role']) : null,
                ]);
            }

            // Alat yang dibutuhkan.
            foreach (array_unique($validated['tools'] ?? []) as $toolId) {
                $quantity = (int) ($validated['quantities'][$toolId] ?? 1);
                $days = (int) ($validated['days'][$toolId] ?? 1);

                if ($quantity < 1 || $days < 1) {
                    continue;
                }

                $proposal->tools()->attach($toolId, [
                    'quantity' => $quantity,
                    'days' => $days,
                ]);
            }

            return $proposal;
        });

        // Beri tahu admin bahwa ada permohonan riset baru menunggu persetujuan.
        foreach (User::admin()->get() as $admin) {
            $admin->notify(new BorrowingNotification(
                'Permohonan Riset Baru',
                "Permohonan riset {$proposal->code} \"{$proposal->title}\" diajukan oleh ".auth()->user()->name.' dan menunggu persetujuan.',
                route('admin.research.show', $proposal),
                notifyViaEmail: true,
            ));
        }

        return redirect()->route('research.show', $proposal)
            ->with('success', "Permohonan riset {$proposal->code} berhasil diajukan dan menunggu persetujuan.");
    }

    public function show(ResearchProposal $proposal)
    {
        // Pemilik riset, anggota riset, atau laboran yang ditugaskan bisa melihat detail.
        $isMember = ResearchProposalMember::where('research_proposal_id', $proposal->id)
            ->where('user_id', auth()->id())
            ->exists();

        abort_unless($proposal->user_id === auth()->id() || $isMember || $proposal->laboran_id === auth()->id(), 403);

        $proposal->load(['members', 'tools.category', 'laboran', 'laboratorium', 'logbooks']);

        return view('research.show', compact('proposal'));
    }

    public function cancel(ResearchProposal $proposal)
    {
        abort_unless($proposal->user_id === auth()->id(), 403);
        abort_unless(in_array($proposal->status, [
            ResearchProposal::STATUS_PENDING,
            ResearchProposal::STATUS_APPROVED,
        ]), 403);

        $proposal->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Permohonan riset dibatalkan.');
    }

    public function logbook(ResearchProposal $proposal)
    {
        abort_unless($proposal->user_id === auth()->id() || $this->isMember($proposal), 403);

        $proposal->load(['logbooks']);

        return view('research.logbook', compact('proposal'));
    }

    public function logbookPrint(ResearchProposal $proposal)
    {
        abort_unless($proposal->user_id === auth()->id() || $this->isMember($proposal), 403);

        $proposal->load(['user', 'logbooks', 'laboran']);

        return view('research.logbook-print', compact('proposal'));
    }

    public function storeLogbook(Request $request, ResearchProposal $proposal)
    {
        abort_unless($proposal->user_id === auth()->id() || $this->isMember($proposal), 403);
        abort_unless($proposal->status === ResearchProposal::STATUS_ONGOING, 403);

        $validated = $request->validate([
            'log_date' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['required', 'string', 'max:5000'],
            'obstacle' => ['nullable', 'string', 'max:5000'],
        ]);

        ResearchLogbook::create([
            'research_proposal_id' => $proposal->id,
            'log_date' => $validated['log_date'],
            'note' => trim($validated['note']),
            'obstacle' => trim((string) ($validated['obstacle'] ?? '')) !== '' ? trim($validated['obstacle']) : null,
        ]);

        return back()->with('success', 'Entri logbook berhasil ditambahkan.');
    }

    public function destroyLogbook(ResearchProposal $proposal, ResearchLogbook $logbook)
    {
        abort_unless($proposal->user_id === auth()->id() || $this->isMember($proposal), 403);
        abort_unless($logbook->research_proposal_id === $proposal->id, 403);

        $logbook->delete();

        return back()->with('success', 'Entri logbook dihapus.');
    }

    public function invoice(ResearchProposal $proposal)
    {
        abort_unless($proposal->user_id === auth()->id(), 403);

        $proposal->load(['members', 'tools.category', 'laboran', 'laboratorium']);

        return view('research.invoice', compact('proposal'));
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-RST-'.date('Ymd');

        do {
            $number = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (ResearchProposal::where('invoice_number', $number)->exists());

        return $number;
    }

    protected function generateCode(): string
    {
        $prefix = 'RST-'.date('Ymd');

        do {
            $code = $prefix.'-'.strtoupper(substr(uniqid(), -5));
        } while (ResearchProposal::where('code', $code)->exists());

        return $code;
    }

    /**
     * Cek apakah user yang sedang login adalah anggota riset ini.
     */
    protected function isMember(ResearchProposal $proposal): bool
    {
        return ResearchProposalMember::where('research_proposal_id', $proposal->id)
            ->where('user_id', auth()->id())
            ->exists();
    }

    /**
     * Cari anggota penelitian berdasarkan kode unik (participant_code).
     */
    public function searchMember(Request $request)
    {
        $request->validate(['kode' => ['required', 'string']]);

        $user = User::where('participant_code', $request->input('kode'))
            ->where('role', User::ROLE_USER)
            ->first();

        if (! $user) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'name' => $user->name,
            'email' => $user->email,
            'nim_nip' => $user->nim_nip,
            'institution' => $user->institution,
        ]);
    }
}
