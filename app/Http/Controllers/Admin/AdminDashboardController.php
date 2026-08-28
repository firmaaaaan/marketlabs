<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Laboratorium;
use App\Models\ResearchProposal;
use App\Models\Tool;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $activeBorrowings = Borrowing::where('status', Borrowing::STATUS_BORROWED)->count();
        $pendingBorrowings = Borrowing::where('status', Borrowing::STATUS_PENDING)->count();
        $returnedBorrowings = Borrowing::where('status', Borrowing::STATUS_RETURNED)->count();

        $totalTools = Tool::count();
        $totalLaboratoriums = Laboratorium::count();
        $totalRisets = ResearchProposal::count();
        $pendingRisets = ResearchProposal::where('status', ResearchProposal::STATUS_PENDING)->count();
        $ongoingRisets = ResearchProposal::where('status', ResearchProposal::STATUS_ONGOING)->count();
        $doneRisets = ResearchProposal::where('status', ResearchProposal::STATUS_DONE)->count();
        $paidRisets = ResearchProposal::where('payment_status', ResearchProposal::PAYMENT_PAID)->count();

        $recentUsers = User::latest()->limit(5)->get();
        $recentBorrowings = Borrowing::with(['user', 'items.tool'])->latest()->limit(5)->get();
        $recentRisets = ResearchProposal::with(['user'])->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeBorrowings',
            'pendingBorrowings',
            'returnedBorrowings',
            'totalTools',
            'totalLaboratoriums',
            'totalRisets',
            'pendingRisets',
            'ongoingRisets',
            'doneRisets',
            'paidRisets',
            'recentUsers',
            'recentBorrowings',
            'recentRisets'
        ));
    }
}
