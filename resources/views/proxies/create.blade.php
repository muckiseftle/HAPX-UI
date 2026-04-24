@extends('layouts.app')

@section('header', 'Neuen Proxy Host anlegen')

@section('content')
<form action="{{ route('proxies.store') }}" method="POST">
    @csrf
    @include('proxies._form')
    
    <div class="flex justify-end mt-6">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition">
            Speichern & Aktivieren
        </button>
    </div>
</form>
@endsection
