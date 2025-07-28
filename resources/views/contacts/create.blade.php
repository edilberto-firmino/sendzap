{{-- resources/views/contacts/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Novo Contato</h1>

    <form method="POST" action="{{ route('contacts.store') }}">
        @csrf

        @include('contacts.partials.form', ['contact' => null])

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
