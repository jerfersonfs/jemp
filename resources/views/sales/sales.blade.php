<x-layouts.app title="Vendas · JEMP" page="sales">
    @php
        $productOptions = $products->mapWithKeys(fn ($product) => [$product->id => $product->code.' — '.$product->name])->all();
    @endphp
    <div class="page-head sales-page__head"><div><h1>Venda de Produtos</h1><p>Registre vendas vinculadas aos clientes, produtos e estoque.</p></div></div>
    @if (session('status')) <p class="sales-feedback" role="status">{{ session('status') }}</p> @endif
    @if ($errors->any()) <p class="sales-feedback sales-feedback--error" role="alert">{{ $errors->first() }}</p> @endif

    <section class="sales-card" aria-labelledby="sale-form-title">
        <header class="sales-card__header"><span class="sales-card__icon"><i class="ph ph-handshake" aria-hidden="true"></i></span><div><h2 id="sale-form-title">Dados da Venda</h2><p>Vendas concluídas baixam o estoque do local escolhido.</p></div></header>
        <form class="sales-form" data-sales-form method="POST" action="{{ route('sales.store') }}">
            @csrf
            <p class="sales-feedback" data-sales-feedback role="alert" hidden></p>
            <div class="sales-form__grid">
                <label class="sales-field"><span>Cliente / Empresa</span><select name="customer_id" required><option value="">Selecione o cliente</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>@endforeach</select></label>
                <label class="sales-field"><span>Data da Venda</span><input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required></label>
                <label class="sales-field"><span>Número da NF-e</span><input name="invoice" value="{{ old('invoice') }}" placeholder="Informe o número"></label>
                <label class="sales-field"><span>Série</span><input name="series" value="{{ old('series', '1') }}"></label>
                <label class="sales-field"><span>Forma de Pagamento</span><select name="payment"><option value="">Selecione</option>@foreach (['Dinheiro', 'Cartão', 'Pix', 'Boleto'] as $payment)<option @selected(old('payment') === $payment)>{{ $payment }}</option>@endforeach</select></label>
                <label class="sales-field"><span>Status</span><select name="status"><option @selected(old('status', 'Pendente') === 'Pendente')>Pendente</option><option @selected(old('status') === 'Concluída')>Concluída</option><option @selected(old('status') === 'Cancelada')>Cancelada</option></select></label>
                <label class="sales-field"><span>Almoxarifado</span><select name="warehouse" required><option value="">Selecione o local</option>@foreach ($locations as $value => $label)<option value="{{ $value }}" @selected(old('warehouse') === $value)>{{ $label }}</option>@endforeach</select></label>
            </div>

            <section class="sales-items" aria-labelledby="sale-items-title">
                <header class="sales-items__header"><h3 id="sale-items-title">Itens Vinculados</h3><button class="btn btn-secondary sales-add-item" type="button" data-sales-add><i class="ph ph-plus" aria-hidden="true"></i> Adicionar Produto</button></header>
                <div class="sales-table-wrap"><table class="sales-table"><thead><tr><th>Código</th><th>Produto</th><th>Lote / Código</th><th>Quantidade</th><th>Valor Unitário</th><th>Desconto</th><th>Total</th><th>Ações</th></tr></thead><tbody data-sales-items>
                    @foreach (old('items', []) as $index => $item)
                        @php $product = $products->firstWhere('id', $item['product_id'] ?? null); @endphp
                        @if ($product)
                            <tr data-sale-item data-product-id="{{ $product->id }}" data-quantity="{{ $item['quantity'] }}" data-price="{{ $item['unit_price'] }}" data-discount="{{ $item['discount'] }}">
                                <td>{{ $product->code }}</td><td>{{ $product->name }}</td><td>{{ $product->code }}</td><td>{{ $item['quantity'] }}</td><td>R$ {{ number_format($item['unit_price'], 2, ',', '.') }}</td><td>R$ {{ number_format($item['discount'], 2, ',', '.') }}</td><td data-item-total>R$ {{ number_format($item['quantity'] * $item['unit_price'] - $item['discount'], 2, ',', '.') }}</td>
                                <td><div class="sales-table__actions"><button class="sales-table__edit" type="button" data-sales-edit aria-label="Editar {{ $product->name }}"><i class="ph ph-pencil-simple-line" aria-hidden="true"></i></button><button class="sales-table__remove" type="button" data-sales-remove aria-label="Remover {{ $product->name }}"><i class="ph ph-trash" aria-hidden="true"></i></button></div>@foreach (['product_id' => $product->id, 'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'], 'discount' => $item['discount']] as $field => $value)<input type="hidden" name="items[{{ $index }}][{{ $field }}]" value="{{ $value }}" data-sale-input="{{ $field }}">@endforeach</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody></table></div>
                <div class="sales-totals" aria-live="polite"><div><span>Subtotal</span><strong data-sales-subtotal>R$ 0,00</strong></div><div><span>Desconto</span><strong data-sales-discount>R$ 0,00</strong></div><div class="sales-totals__grand"><span>Total</span><strong data-sales-total>R$ 0,00</strong></div></div>
            </section>
            <footer class="sales-form__footer"><button class="btn btn-secondary" type="button" data-sales-reset>Limpar</button><button class="btn btn-primary" type="submit">Salvar Venda</button></footer>
        </form>
    </section>

    <x-cards.panel class="sales-card sales-history" variant="mint" title="Vendas Registradas" description="Histórico persistido no banco de dados.">
        <div class="sales-table-wrap"><table class="sales-table"><thead><tr><th>Código</th><th>Cliente</th><th>Data</th><th>Itens</th><th>Status</th><th>Total</th></tr></thead><tbody>
            @forelse ($sales as $sale)
                <tr><td>{{ $sale->code }}</td><td>{{ $sale->customer->name }}</td><td>{{ $sale->sold_at->format('d/m/Y') }}</td><td>{{ $sale->items_count }}</td><td>{{ $sale->status }}</td><td>R$ {{ number_format((float) $sale->total, 2, ',', '.') }}</td></tr>
            @empty
                <tr><td colspan="6" class="jemp-table__empty">Nenhuma venda registrada.</td></tr>
            @endforelse
        </tbody></table></div>
    </x-cards.panel>

    <div class="sales-dialog-layer" data-sales-dialog hidden><section class="jemp-dialog jemp-dialog--small jemp-dialog--editing" role="dialog" aria-modal="true" aria-labelledby="sales-dialog-title"><header class="jemp-dialog__header"><div><h2 class="jemp-dialog__title" id="sales-dialog-title">Editar item</h2><p class="jemp-dialog__description" data-sales-dialog-product></p></div><button class="jemp-dialog__close" type="button" data-sales-dialog-close aria-label="Fechar">×</button></header><form class="sales-dialog-form" data-sales-dialog-form><label>Quantidade<input type="number" name="quantity" min="1" step="1" required></label><label>Valor unitário<input type="number" name="unit_price" min="0" step="0.01" required></label><label>Desconto<input type="number" name="discount" min="0" step="0.01" required></label><footer class="jemp-dialog__footer"><button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-sales-dialog-close>Cancelar</button><button class="jemp-dialog__button jemp-dialog__button--primary" type="submit">Salvar item</button></footer></form></section></div>
    <div class="sales-dialog-layer" data-sales-add-dialog hidden><section class="jemp-dialog jemp-dialog--small" role="dialog" aria-modal="true" aria-labelledby="sales-add-dialog-title"><header class="jemp-dialog__header"><div><h2 class="jemp-dialog__title" id="sales-add-dialog-title">Adicionar produto à venda</h2><p class="jemp-dialog__description">Selecione o produto e informe os valores do item.</p></div><button class="jemp-dialog__close" type="button" data-sales-add-close aria-label="Fechar">×</button></header><form class="sales-dialog-form" data-sales-add-form><label>Produto<select name="product_id" required data-sales-product><option value="">Selecione o produto</option>@foreach ($products as $product)<option value="{{ $product->id }}" data-code="{{ $product->code }}" data-name="{{ $product->name }}" data-stock="{{ $product->stocks_sum_quantity ?? $product->quantity }}">{{ $product->code }} — {{ $product->name }} ({{ $product->stocks_sum_quantity ?? $product->quantity }} un.)</option>@endforeach</select></label><label>Quantidade<input type="number" name="quantity" min="1" step="1" value="1" required></label><label>Valor unitário<input type="number" name="unit_price" min="0" step="0.01" value="0" required></label><label>Desconto<input type="number" name="discount" min="0" step="0.01" value="0" required></label><footer class="jemp-dialog__footer"><button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-sales-add-close>Cancelar</button><button class="jemp-dialog__button jemp-dialog__button--primary" type="submit">Adicionar à venda</button></footer></form></section></div>
    <div class="sales-dialog-layer" data-sales-confirm-layer hidden><x-dialogs.confirmation title="Remover produto?" message="Este item será removido da venda." confirm-label="Remover produto" cancel-label="Cancelar" data-sales-confirm-dialog /></div>
</x-layouts.app>
