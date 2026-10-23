<x-layouts.app title="Vendas · JEMP" page="sales">
    @php
        $saleItems = [
            ['code' => '000001', 'product' => 'Produto A', 'lot' => 'LT0001', 'quantity' => 10, 'unit_price' => 100, 'discount' => 0],
            ['code' => '000002', 'product' => 'Produto B', 'lot' => 'LT0002', 'quantity' => 5, 'unit_price' => 150, 'discount' => 0],
        ];
        $saleSubtotal = collect($saleItems)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
    @endphp

    <div class="page-head sales-page__head">
        <div>
            <h1>Venda de Produtos</h1>
            <p>Registre e gerencie as vendas de produtos.</p>
        </div>
    </div>

    <section class="sales-card" aria-labelledby="sale-form-title">
        <header class="sales-card__header">
            <span class="sales-card__icon"><i class="ph ph-handshake" aria-hidden="true"></i></span>
            <div>
                <h2 id="sale-form-title">Dados da Venda</h2>
                <p>Informe os dados necessários para registrar a venda.</p>
            </div>
        </header>

        <form class="sales-form" data-sales-form>
            <div class="sales-form__grid">
                <label class="sales-field">
                    <span>Cliente / Empresa</span>
                    <select name="customer" required>
                        <option value="">Selecione o cliente</option>
                        <option>Penha</option><option>Cristália</option><option>Jampac</option><option>MAMÃ</option><option>AQUI</option>
                    </select>
                </label>
                <label class="sales-field"><span>Data da Venda</span><input type="date" name="date" value="2026-09-26" required></label>
                <label class="sales-field"><span>Número da NF-e</span><input name="invoice" placeholder="Informe o número"></label>
                <label class="sales-field"><span>Série</span><input name="series" value="1"></label>
                <label class="sales-field">
                    <span>Forma de Pagamento</span>
                    <select name="payment"><option value="">Selecione</option><option>Dinheiro</option><option>Cartão</option><option>Pix</option><option>Boleto</option></select>
                </label>
                <label class="sales-field"><span>Status</span><select name="status"><option>Pendente</option><option>Concluída</option><option>Cancelada</option></select></label>
            </div>

            <section class="sales-items" aria-labelledby="sale-items-title">
                <header class="sales-items__header">
                    <h3 id="sale-items-title">Itens Vinculados</h3>
                    <button class="btn btn-secondary sales-add-item" type="button" data-sales-add><i class="ph ph-plus" aria-hidden="true"></i> Adicionar Produto</button>
                </header>
                <div class="sales-table-wrap">
                    <table class="sales-table">
                        <thead><tr><th>Código</th><th>Produto</th><th>Lote</th><th>Quantidade</th><th>Valor Unitário</th><th>Desconto</th><th>Total</th><th>Ações</th></tr></thead>
                        <tbody data-sales-items>
                            @foreach ($saleItems as $item)
                                <tr data-sale-item data-quantity="{{ $item['quantity'] }}" data-price="{{ $item['unit_price'] }}" data-discount="{{ $item['discount'] }}">
                                    <td>{{ $item['code'] }}</td><td>{{ $item['product'] }}</td><td>{{ $item['lot'] }}</td><td>{{ $item['quantity'] }}</td>
                                    <td>R$ {{ number_format($item['unit_price'], 2, ',', '.') }}</td><td>R$ {{ number_format($item['discount'], 2, ',', '.') }}</td>
                                    <td data-item-total>R$ {{ number_format($item['quantity'] * $item['unit_price'] - $item['discount'], 2, ',', '.') }}</td>
                                    <td><div class="sales-table__actions"><button class="sales-table__edit" type="button" data-sales-edit aria-label="Editar {{ $item['product'] }}"><i class="ph ph-pencil-simple-line" aria-hidden="true"></i></button><button class="sales-table__remove" type="button" data-sales-remove aria-label="Remover {{ $item['product'] }}"><i class="ph ph-trash" aria-hidden="true"></i></button></div></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sales-totals" aria-live="polite">
                    <div><span>Subtotal</span><strong data-sales-subtotal>R$ {{ number_format($saleSubtotal, 2, ',', '.') }}</strong></div>
                    <div><span>Desconto</span><strong data-sales-discount>R$ 0,00</strong></div>
                    <div class="sales-totals__grand"><span>Total</span><strong data-sales-total>R$ {{ number_format($saleSubtotal, 2, ',', '.') }}</strong></div>
                </div>
            </section>

            <footer class="sales-form__footer">
                <button class="btn btn-secondary" type="reset">Cancelar</button>
                <button class="btn btn-primary" type="submit">Salvar Venda</button>
            </footer>
        </form>
        <p class="sales-feedback" data-sales-feedback role="status" hidden>Venda demonstrativa validada. Os dados ainda não são persistidos.</p>
    </section>

    <div class="sales-dialog-layer" data-sales-dialog hidden>
        <section class="jemp-dialog jemp-dialog--small jemp-dialog--editing" role="dialog" aria-modal="true" aria-labelledby="sales-dialog-title">
            <header class="jemp-dialog__header"><div><h2 class="jemp-dialog__title" id="sales-dialog-title">Editar item</h2><p class="jemp-dialog__description" data-sales-dialog-product></p></div><button class="jemp-dialog__close" type="button" data-sales-dialog-close aria-label="Fechar">×</button></header>
            <form class="sales-dialog-form jemp-form-dialog--edit" data-sales-dialog-form>
                <label>Quantidade<input type="number" name="quantity" min="1" step="1" required></label>
                <label>Valor unitário<input type="number" name="price" min="0" step="0.01" required></label>
                <label>Desconto<input type="number" name="discount" min="0" step="0.01" required></label>
                <footer class="jemp-dialog__footer"><button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-sales-dialog-close>Cancelar</button><button class="jemp-dialog__button jemp-dialog__button--primary" type="submit">Salvar item</button></footer>
            </form>
        </section>
    </div>
    <div class="sales-dialog-layer" data-sales-add-dialog hidden>
        <section class="jemp-dialog jemp-dialog--small" role="dialog" aria-modal="true" aria-labelledby="sales-add-dialog-title">
            <header class="jemp-dialog__header"><div><h2 class="jemp-dialog__title" id="sales-add-dialog-title">Adicionar produto à venda</h2><p class="jemp-dialog__description">Selecione o produto e informe os dados do item.</p></div><button class="jemp-dialog__close" type="button" data-sales-add-close aria-label="Fechar">×</button></header>
            <form class="sales-dialog-form" data-sales-add-form>
                <label>Produto<select name="product" required data-sales-product><option value="">Selecione o produto</option><option value="produto-a">Produto A</option><option value="produto-b">Produto B</option><option value="produto-c">Produto C</option><option value="produto-d">Produto D</option><option value="produto-e">Produto E</option></select></label>
                <label>Lote<input name="lot" type="text" readonly required data-sales-lot></label>
                <label>Quantidade<input name="quantity" type="number" min="1" step="1" value="1" required></label>
                <label>Valor unitário<input name="price" type="number" min="0" step="0.01" required data-sales-price></label>
                <label>Desconto<input name="discount" type="number" min="0" step="0.01" value="0" required></label>
                <footer class="jemp-dialog__footer"><button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-sales-add-close>Cancelar</button><button class="jemp-dialog__button jemp-dialog__button--primary" type="submit">Adicionar à venda</button></footer>
            </form>
        </section>
    </div>
    <div class="sales-dialog-layer" data-sales-confirm-layer hidden>
        <x-dialogs.confirmation title="Remover produto?" message="Este item será removido da venda demonstrativa." confirm-label="Remover produto" cancel-label="Cancelar" data-sales-confirm-dialog />
    </div>
</x-layouts.app>
