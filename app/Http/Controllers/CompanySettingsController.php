<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanySettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings', [
            'conditions' => DB::table('stock_condition_settings')->orderBy('product_condition')->pluck('saleable', 'product_condition'),
            'lowStockThreshold' => DB::table('company_settings')->where('id', 1)->soleValue('low_stock_threshold'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'edit_conditions' => ['sometimes', 'accepted'],
            'saleable_conditions' => ['sometimes', 'array'],
            'saleable_conditions.*' => ['required', 'string', 'distinct', 'exists:stock_condition_settings,product_condition'],
            'low_stock_threshold' => ['bail', 'required_without:edit_conditions', 'integer', 'between:0,2147483647'],
        ], [
            'low_stock_threshold.required_without' => 'Informe o limite de estoque baixo.',
            'low_stock_threshold.integer' => 'Informe uma quantidade inteira de unidades.',
            'low_stock_threshold.between' => 'Informe uma quantidade entre 0 e 2.147.483.647 unidades.',
        ]);

        DB::transaction(function () use ($validated): void {
            if (isset($validated['low_stock_threshold'])) {
                DB::table('company_settings')->where('id', 1)->update([
                    'low_stock_threshold' => (int) $validated['low_stock_threshold'],
                ]);
            }
            if (isset($validated['edit_conditions']) || isset($validated['saleable_conditions'])) {
                $selected = $validated['saleable_conditions'] ?? [];
                DB::table('stock_condition_settings')->whereNotIn('product_condition', $selected)->update(['saleable' => false]);
                DB::table('stock_condition_settings')->whereIn('product_condition', $selected)->update(['saleable' => true]);
            }
        });

        return to_route('settings.edit')->with('stock_settings_saved', true)->with('status', 'Parâmetros de estoque atualizados para toda a empresa.');
    }
}
