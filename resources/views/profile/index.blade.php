@php
    // Staff (laboran) memakai layout internal; user biasa memakai layout publik dua kolom.
    $isStaff = Auth::user()->isLaboran();
@endphp
@extends($isStaff ? 'layouts.staff' : 'layouts.account')

@section('title', 'Profil Saya - MarketLabs')
@section('page', 'Profil Saya')

@if ($isStaff)
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
