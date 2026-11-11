@extends('layouts.app')

@section('title', 'Edit Unit')
@section('page_title', 'Edit Unit')

@section('content')
<div class="w-full min-w-0">
    @include('super-admin.units._form', [
        'title' => 'Edit Unit',
        'action' => route('super-admin.units.update', $unit),
        'method' => 'PUT',
    ])
</div>
@endsection
