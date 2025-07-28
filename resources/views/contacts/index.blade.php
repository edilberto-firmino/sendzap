{{-- resources/views/contacts/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Contatos</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('contacts.create') }}" class="btn btn-primary mb-3">+ Novo Contato</a>
    <a href="{{ route('contacts.import.form') }}" class="btn btn-success mb-3">Importar Arquivo</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Cidade</th>
                <th>Estado</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
                <tr>
                    <td>{{ $contact->name }}</td>
                    <td>{{ $contact->phone }}</td>
                    <td>{{ $contact->email }}</td>
                    <td>{{ $contact->city }}</td>
                    <td>{{ $contact->state }}</td>
                    <td>
                        <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('contacts.destroy', $contact) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Tem certeza que deseja excluir?')" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Nenhum contato encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $contacts->links('pagination::bootstrap-5') }}
</div>
@endsection
