<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contact;

class Formatation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:formatation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Formata os números de telefone dos contatos para o padrão +55DDDNÚMERO';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Contact::all()->each(function ($contact) {
            $original = $contact->phone;

            // Regra 1: Telefone com 11 dígitos numéricos (ex: 21999999999)
            if (preg_match('/^\d{11}$/', $original)) {
                $novo = '+55' . $original;

                if ($this->atualizarSeNaoDuplicado($contact, $novo)) {
                    $this->info("Corrigido [sem +55]: {$original} → {$novo} (ID: {$contact->id})");
                }

                return;
            }

            // Regra 2: Telefone com 12 caracteres que começa com +, mas não com +55
            if (preg_match('/^\+(\d{2})(\d{9})$/', $original, $match) && $match[1] !== '55') {
                $novo = '+55' . $match[1] . $match[2];

                if ($this->atualizarSeNaoDuplicado($contact, $novo)) {
                    $this->info("Corrigido [sem código país]: {$original} → {$novo} (ID: {$contact->id})");
                }

                return;
            }

        });
    }

    /**
     * Atualiza o telefone do contato se não for duplicado.
     */
    private function atualizarSeNaoDuplicado($contact, $novoPhone)
    {
        if (
            $contact->phone === $novoPhone ||
            Contact::where('phone', $novoPhone)->where('id', '!=', $contact->id)->exists()
        ) {
            $this->warn("Já existe: {$novoPhone} (ID: {$contact->id})");
            return false;
        }

        $contact->phone = $novoPhone;
        $contact->save();
        return true;
    }
}
