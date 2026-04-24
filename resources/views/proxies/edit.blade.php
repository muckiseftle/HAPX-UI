@extends('layouts.app')

@section('header', 'Proxy Host bearbeiten')

@section('content')
<form action="{{ route('proxies.update', $proxy) }}" method="POST">
    @csrf
    @method('PUT')
    @include('proxies._form', ['proxy' => $proxy])
    
    <div class="flex justify-end mt-6">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition">
            Änderungen übernehmen
        </button>
    </div>
</form>
@endsection
