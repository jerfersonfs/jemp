<?php

namespace App;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalePayment
{
    public static function rules(): array
    {
        return [
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'installment_count' => ['sometimes', 'required', 'integer', 'between:1,65535'],
            'payment_status' => ['required', Rule::in(['A vencer', 'Paga'])],
            'payment_date' => ['required_if:payment_status,Paga', 'prohibited_unless:payment_status,Paga', 'nullable', 'date_format:Y-m-d', 'before_or_equal:'.Payment::today()],
            'payment_method' => ['required_if:payment_status,Paga', 'nullable', Rule::in(Payment::METHODS)],
            'payments' => ['missing'],
        ];
    }

    public static function cents(string $amount): int
    {
        [$units, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $units * 100 + (int) str_pad($fraction, 2, '0');
    }

    /**
     * @return list<array{count:int,amount:string}>
     */
    public static function breakdown(string $amount, int $count): array
    {
        $cents = self::cents($amount);
        $base = intdiv($cents, $count);
        $extra = $cents % $count;
        $groups = [['count' => $count - $extra, 'amount' => number_format($base / 100, 2, '.', '')]];
        if ($extra > 0) {
            $groups[] = ['count' => $extra, 'amount' => number_format(($base + 1) / 100, 2, '.', '')];
        }

        return $groups;
    }

    public static function describe(string $amount, int $count): string
    {
        return collect(self::breakdown($amount, $count))->map(fn (array $group): string => $group['count'].' × R$ '.number_format((float) $group['amount'], 2, ',', '.')
        )->implode(' + ');
    }

    /**
     * @param  array{payment_terms_days:int|string, installment_count?:int|string, payment_status:string, payment_method?:string|null, payment_date?:string|null}  $data
     */
    public static function record(Invoice $invoice, array $data): void
    {
        $date = $invoice->issue_date->toImmutable()->startOfDay();
        $days = (int) $data['payment_terms_days'];
        $count = (int) ($data['installment_count'] ?? 1);
        if ($days > $date->diffInDays($date->setDate(9999, 12, 31))) {
            throw ValidationException::withMessages(['payment_terms_days' => 'O prazo resulta em uma data de vencimento inválida.']);
        }
        if ($count > max(1, self::cents($invoice->amount))) {
            throw ValidationException::withMessages(['installment_count' => 'Cada parcela precisa ter pelo menos um centavo.']);
        }
        $paidAt = $data['payment_status'] === 'Paga' ? $data['payment_date'] : null;
        $method = $data['payment_method'] ?? null;
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('CALL sp_registrar_pagamento_nota(?, ?, ?, ?, ?)', [$invoice->getKey(), $days, $count, $method, $paidAt]);

            return;
        }
        $invoice->payment()->create([
            'payment_terms_days' => $days, 'due_date' => $date->addDays($days)->toDateString(),
            'installment_count' => $count, 'payment_method' => $method, 'paid_at' => $paidAt,
        ]);
    }

    public static function receive(Invoice $invoice, string $method, string $paidAt): Payment
    {
        return DB::transaction(function () use ($invoice, $method, $paidAt): Payment {
            $locked = Invoice::whereKey($invoice->getKey())->lockForUpdate()->firstOrFail();
            $payment = $locked->payment()->lockForUpdate()->firstOrFail();
            if ($locked->is_cancelled || $payment->paid_at !== null) {
                abort(409, 'Nota cancelada ou venda já recebida.');
            }
            $payment->update(['payment_method' => $method, 'paid_at' => $paidAt]);

            return $payment;
        });
    }
}
