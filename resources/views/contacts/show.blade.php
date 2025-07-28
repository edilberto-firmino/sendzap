{{-- resources/views/contacts/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalhes do Contato</h1>

    <ul class="list-group mb-3">
        <li class="list-group-item"><strong>Nome:</strong> {{ $contact->name }}</li>
        <li class="list-group-item"><strong>Telefone:</strong> {{ $contact->phone }}</li>
        <li class="list-group-item"><strong>Email:</strong> {{ $contact->email }}</li>
        <li class="list-group-item"><strong>Cidade:</strong> {{ $contact->city }}</li>
        <li class="list-group-item"><strong>Estado:</strong> {{ $contact->state }}</li>
        <li class="list-group-item"><strong>Gênero:</strong> {{ $contact->gender }}</li>
        <li class="list-group-item"><strong>Idade:</strong> {{ $contact->age }}</li>
        <li class="list-group-item"><strong>Tags:</strong>
            @if(is_array($contact->tags))
                {{ implode(', ', $contact->tags) }}
            @endif
        </li>
    </ul>

    <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
