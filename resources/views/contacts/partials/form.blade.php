{{-- resources/views/contacts/partials/form.blade.php --}}
<div class="mb-3">
    <label for="name" class="form-label">Nome</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $contact->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="phone" class="form-label">Telefone</label>
    <input type="text" name="phone" class="form-control" value="{{ old('phone', $contact->phone ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $contact->email ?? '') }}">
</div>

<div class="mb-3">
    <label for="city" class="form-label">Cidade</label>
    <input type="text" name="city" class="form-control" value="{{ old('city', $contact->city ?? '') }}">
</div>

<div class="mb-3">
    <label for="state" class="form-label">Estado</label>
    <input type="text" name="state" class="form-control" value="{{ old('state', $contact->state ?? '') }}">
</div>

<div class="mb-3">
    <label for="gender" class="form-label">Gênero</label>
    <select name="gender" class="form-select">
        <option value="">Selecione</option>
        <option value="male" {{ old('gender', $contact->gender ?? '') == 'male' ? 'selected' : '' }}>Masculino</option>
        <option value="female" {{ old('gender', $contact->gender ?? '') == 'female' ? 'selected' : '' }}>Feminino</option>
        <option value="other" {{ old('gender', $contact->gender ?? '') == 'other' ? 'selected' : '' }}>Outro</option>
    </select>
</div>

<div class="mb-3">
    <label for="age" class="form-label">Idade</label>
    <input type="number" name="age" class="form-control" value="{{ old('age', $contact->age ?? '') }}">
</div>

<div class="mb-3">
    <label for="tags" class="form-label">Tags (separadas por vírgula)</label>
    <input type="text" name="tags" class="form-control" value="{{ old('tags', isset($contact->tags) ? implode(',', $contact->tags) : '') }}">
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
