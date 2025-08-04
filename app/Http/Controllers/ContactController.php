<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Lista os contatos.
     */
    public function index()
    {
        $contacts = Contact::where('phone', 'like', '+%')->latest()->orderBy('name','ASC')->paginate(20);
        return view('contacts.index', compact('contacts'));
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create()
    {
        return view('contacts.create');
    }

    /**
     * Salva um novo contato.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:contacts,phone',
            'email' => 'nullable|email',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'age' => 'nullable|integer',
            'tags' => 'nullable|string',
        ]);

        $validated['tags'] = $validated['tags']
            ? json_encode(array_map('trim', explode(',', $validated['tags'])))
            : null;

        Contact::create($validated);

        return redirect()->route('contacts.index')->with('success', 'Contato cadastrado com sucesso!');
    }

    /**
     * Exibe um contato específico.
     */
    public function show(Contact $contact)
    {
        return view('contacts.show', compact('contact'));
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(Contact $contact)
    {
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Atualiza o contato.
     */
    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:contacts,phone,' . $contact->id,
            'email' => 'nullable|email',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'age' => 'nullable|integer',
            'tags' => 'nullable|string',
        ]);

        $validated['tags'] = $validated['tags']
            ? json_encode(array_map('trim', explode(',', $validated['tags'])))
            : null;

        $contact->update($validated);

        return redirect()->route('contacts.index')->with('success', 'Contato atualizado com sucesso!');
    }


    public function importForm()
    {
        return view('contacts.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows));

        foreach ($rows as $index => $row) {
            $data = array_combine($header, $row);

            if (!isset($data['Nome']) || !isset($data['Telefone'])) {
                continue; // ignora se nome ou telefone estiverem ausentes
            }

            try {
                Contact::create([
                    'name' => $data['Nome'],
                    'email' => $data['Email'] ?? null,
                    'phone' => $data['Telefone'],
                    'cpf' => $data['CPF'] ?? null,
                    'social_name' => $data['Nome Social'] ?? null,
                    'birthdate' => isset($data['Data de Nascimento']) && $data['Data de Nascimento'] !== ''
                        ? Carbon::parse($data['Data de Nascimento'])->format('Y-m-d')
                        : null,
                    'created_at' => isset($data['Criado em']) && $data['Criado em'] !== ''
                        ? Carbon::parse($data['Criado em'])
                        : now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error("Erro ao importar linha {$index}: " . $e->getMessage());
                continue;
            }
        }

        return redirect()->route('contacts.index')->with('success', 'Contatos importados com sucesso!');
    }

    /**
     * Remove o contato.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('success', 'Contato removido com sucesso!');
    }
}
