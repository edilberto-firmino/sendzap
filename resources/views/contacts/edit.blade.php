{{-- resources/views/contacts/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Contato</h1>

    <form method="POST" action="{{ route('contacts.update', $contact) }}">
        @csrf
        @method('PUT')

        @include('contacts.partials.form', ['contact' => $contact])

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
