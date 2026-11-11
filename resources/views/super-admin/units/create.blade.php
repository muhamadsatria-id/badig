@extends('layouts.app')

@section('title', 'Tambah Unit')
@section('page_title', 'Tambah Unit')

@section('content')
<div class="w-full min-w-0">
    @include('super-admin.units._form', [
        'title' => 'Tambah Unit',
        'action' => route('super-admin.units.store'),
        'method' => 'POST',
    ])
</div>
@endsection
