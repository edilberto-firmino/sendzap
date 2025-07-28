{{-- resources/views/contacts/import.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Importar Contatos via CSV</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('contacts.import') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="csv_file" class="form-label">Arquivo CSV</label>
            <input type="file" name="csv_file" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Importar</button>
        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
