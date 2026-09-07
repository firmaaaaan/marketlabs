@php
    $user = Auth::user();
    $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
    $isStaff = $user->isLaboran();
@endphp
@extends($isAdmin ? 'layouts.admin' : ($isStaff ? 'layouts.staff' : 'layouts.account'))

@section('title', 'Profil Saya - MarketLabs')
@section('page', 'Profil Saya')

@if ($isAdmin)
    @section('content')
        <section class="py-4">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                @include('profile._forms')
            </div>
        </section>
    @endsection
@elseif ($isStaff)
    @section('content')
        <section class="py-4">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                @include('profile._forms')
            </div>
        </section>
    @endsection
@else
    @section('account-content')
        @include('profile._forms')
    @endsection
@endif
