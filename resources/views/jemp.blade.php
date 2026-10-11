<x-layouts.app
    title="{{ ['home' => 'Visão geral', 'catalogo' => 'Catálogo', 'produto' => 'Novo produto', 'inventario' => 'Inventário', 'saidas' => 'Saídas e NF-e'][$page] ?? 'JEMP' }} · JEMP"
    :page="$page">
                @if($page === 'home')
                    <div class="page-head">
                        <div>
                            <p class="eyebrow">Sexta-feira, 3 de outubro</p>
                            <h1>Olá, Maria! 👋</h1>
                            <p class="sub">Aqui está o resumo da sua operação hoje.</p>
                        </div><a href="{{ route('produtos.create') }}" class="button">＋ Novo produto</a>
                    </div>
                    <section class="stats">
                        <div class="card stat">
                            <div class="stat-top">Faturamento do mês <span class="stat-icon">↗</span></div><strong>R$
                                84.260</strong><small><span class="trend">↑ 12,5%</span> vs. mês anterior</small>
                        </div>
                        <div class="card stat">
                            <div class="stat-top">Produtos em estoque <span class="stat-icon">▦</span></div>
                            <strong>1.248</strong><small>em <span class="trend">86 categorias</span></small>
                        </div>
                        <div class="card stat">
                            <div class="stat-top">Estoque baixo <span class="stat-icon"
                                    style="background:#fff2dc;color:#ad7418">!</span></div><strong>12</strong><small>itens
                                exigem atenção</small>
                        </div>
                        <div class="card stat">
                            <div class="stat-top">Saídas este mês <span class="stat-icon">↗</span></div>
                            <strong>327</strong><small><span class="trend">↑ 8,2%</span> vs. mês anterior</small>
                        </div>
                    </section>
                    <section class="home-grid">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">Movimentação de estoque</h2><select class="select"
                                    style="height:34px;font-size:12px">
                                    <option>Últimos 7 dias</option>
                                </select>
                            </div>
                            <div class="chart">
                                <div class="chart-grid"></div><svg viewBox="0 0 600 190" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="area" x1="0" y1="0" x2="0" y2="1">
                                            <stop stop-color="#b9df45" stop-opacity=".33" />
                                            <stop offset="1" stop-color="#b9df45" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                    <path
                                        d="M0 170 L70 135 L145 148 L220 92 L300 115 L380 70 L455 102 L530 34 L600 55 V190 H0Z"
                                        fill="url(#area)" />
                                    <path d="M0 170 L70 135 L145 148 L220 92 L300 115 L380 70 L455 102 L530 34 L600 55"
                                        fill="none" stroke="#174c2e" stroke-width="3" vector-effect="non-scaling-stroke" />
                                    <circle cx="530" cy="34" r="5" fill="#b9df45" stroke="#174c2e" stroke-width="3" />
                                </svg>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">Atividade recente</h2><a class="link"
                                    href="{{ route('saidas') }}">Ver tudo</a>
                            </div>
                            <div class="activity">
                                <div class="activity-item"><i class="activity-dot"></i><span><strong>Nota fiscal
                                            emitida</strong>
                                        <p>NF-e #000.342 para Mercado Central</p>
                                    </span><time>há 12 min</time></div>
                                <div class="activity-item"><i class="activity-dot"
                                        style="background:#f6a949;box-shadow:0 0 0 5px #fff5e6"></i><span><strong>Estoque
                                            atualizado</strong>
                                        <p>Entrada de 120 un. de Café Premium</p>
                                    </span><time>há 38 min</time></div>
                                <div class="activity-item"><i class="activity-dot"
                                        style="background:#6aa6d8;box-shadow:0 0 0 5px #eaf3fc"></i><span><strong>Novo
                                            produto cadastrado</strong>
                                        <p>Chá verde orgânico 250g</p>
                                    </span><time>há 2h</time></div>
                            </div>
                        </div>
                    </section>
                @elseif($page === 'catalogo')
                    <div class="page-head">
                        <div>
                            <p class="eyebrow">Produtos</p>
                            <h1>Catálogo</h1>
                            <p class="sub">Gerencie os produtos disponíveis na sua operação.</p>
                        </div><a href="{{ route('produtos.create') }}" class="button">＋ Adicionar produto</a>
                    </div>
                    <div class="toolbar"><label class="search">⌕<input
                                placeholder="Buscar por nome, SKU ou categoria"></label>
                        <div class="filter-group"><select class="select">
                                <option>Todas categorias</option>
                            </select><button class="button light">Filtros</button></div>
                    </div>
                    <div class="card table-card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>SKU</th>
                                    <th>Categoria</th>
                                    <th>Estoque</th>
                                    <th>Preço</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([['☕', 'Café Premium 500g', 'CAF-500', 'Alimentos', '184 un.', 'R$ 28,90', ''], ['🫙', 'Azeite extravirgem 500ml', 'AZE-500', 'Mercearia', '56 un.', 'R$ 42,50', ''], ['🍵', 'Chá verde orgânico 250g', 'CHA-250', 'Bebidas', '8 un.', 'R$ 19,90', 'warning'], ['🧴', 'Detergente neutro 500ml', 'DET-500', 'Limpeza', '0 un.', 'R$ 6,50', 'red']] as $item)
                                    <tr>
                                        <td>
                                            <div class="product"><span class="product-image">{{ $item[0] }}</span>{{ $item[1] }}
                                            </div>
                                        </td>
                                        <td style="color:var(--muted)">{{ $item[2] }}</td>
                                        <td>{{ $item[3] }}</td>
                                        <td><span class="tag {{ $item[6] }}">{{ $item[4] }}</span></td>
                                        <td><b>{{ $item[5] }}</b></td>
                                        <td class="more">•••</td>
                                </tr>@endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif($page === 'produto')
                    <div class="page-head">
                        <div>
                            <p class="eyebrow">Produtos / Catálogo</p>
                            <h1>Cadastrar produto</h1>
                            <p class="sub">Preencha as informações para incluir um novo item no catálogo.</p>
                        </div><a href="{{ route('catalogo') }}" class="button light">Cancelar</a>
                    </div>
                    <form class="form-layout">
                        <div class="card form-card">
                            <h2>Informações do produto</h2>
                            <p class="help">Os campos com * são obrigatórios.</p>
                            <div class="fields">
                                <div class="field full"><label>Nome do produto *</label><input
                                        placeholder="Ex.: Café Premium 500g"></div>
                                <div class="field"><label>SKU</label><input placeholder="Código interno"></div>
                                <div class="field"><label>Categoria *</label><select>
                                        <option>Selecione uma categoria</option>
                                        <option>Alimentos</option>
                                        <option>Bebidas</option>
                                    </select></div>
                                <div class="field"><label>Preço de venda *</label><input placeholder="R$ 0,00"></div>
                                <div class="field"><label>Estoque inicial *</label><input type="number" placeholder="0">
                                </div>
                                <div class="field"><label>Estoque mínimo</label><input type="number" placeholder="0"></div>
                                <div class="field"><label>Unidade de medida</label><select>
                                        <option>Unidade (un.)</option>
                                        <option>Quilograma (kg)</option>
                                    </select></div>
                                <div class="field full"><label>Descrição</label><textarea
                                        placeholder="Adicione uma breve descrição para facilitar a identificação."></textarea>
                                </div>
                                <div class="field full"><label>Imagem do produto</label>
                                    <div class="upload"><span style="font-size:28px">⇧</span><span><b>Clique para
                                                enviar</b><small>PNG, JPG ou WEBP · até 5 MB</small></span></div>
                                </div>
                            </div>
                        </div>
                        <aside>
                            <div class="card tip">
                                <h3>Dicas rápidas</h3>
                                <p>Um cadastro completo melhora a organização e reduz erros nas movimentações.</p>
                                <ul>
                                    <li>Use um SKU único.</li>
                                    <li>Defina o estoque mínimo.</li>
                                    <li>Organize por categoria.</li>
                                </ul>
                            </div>
                            <div class="card tip" style="margin-top:16px">
                                <div class="summary-row"><span>Status</span><strong>Rascunho</strong></div><button
                                    type="button" class="button"
                                    style="width:100%;justify-content:center;margin-top:16px">Salvar produto</button>
                            </div>
                        </aside>
                    </form>
                @elseif($page === 'inventario')
                    <div class="page-head">
                        <div>
                            <p class="eyebrow">Estoque</p>
                            <h1>Inventário</h1>
                            <p class="sub">Acompanhe a saúde do seu estoque e faça ajustes quando necessário.</p>
                        </div><button class="button">＋ Novo ajuste</button>
                    </div>
                    <section class="inventory-cards">
                        <div class="card inventory-card"><span>Valor total em estoque</span><b>R$ 128.450,00</b>
                            <div class="stock-bar"><i style="width:78%"></i></div>
                        </div>
                        <div class="card inventory-card"><span>Itens com estoque baixo</span><b style="color:#b87518">12
                                produtos</b>
                            <div class="stock-bar"><i style="width:28%;background:#f6a949"></i></div>
                        </div>
                        <div class="card inventory-card"><span>Produtos sem estoque</span><b style="color:#c25048">4
                                produtos</b>
                            <div class="stock-bar"><i style="width:12%;background:#da5b52"></i></div>
                        </div>
                    </section>
                    <div class="toolbar"><label class="search">⌕<input placeholder="Buscar produto"></label>
                        <div class="filter-group"><select class="select">
                                <option>Todos os status</option>
                            </select><button class="button light">Exportar</button></div>
                    </div>
                    <div class="card table-card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Em estoque</th>
                                    <th>Mínimo</th>
                                    <th>Última movimentação</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([['Café Premium 500g', '184 un.', '30 un.', 'Hoje, 09:42', 'Disponível', ''], ['Chá verde orgânico 250g', '8 un.', '15 un.', 'Ontem, 16:08', 'Estoque baixo', 'warning'], ['Detergente neutro 500ml', '0 un.', '20 un.', '28 set, 10:31', 'Sem estoque', 'red']] as $item)
                                    <tr>
                                        <td><b>{{ $item[0] }}</b></td>
                                        <td>{{ $item[1] }}</td>
                                        <td style="color:var(--muted)">{{ $item[2] }}</td>
                                        <td style="color:var(--muted)">{{ $item[3] }}</td>
                                        <td><span class="tag {{ $item[5] }}">{{ $item[4] }}</span></td>
                                        <td class="more">•••</td>
                                </tr>@endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="page-head">
                        <div>
                            <p class="eyebrow">Fiscal</p>
                            <h1>Saídas e NF-e</h1>
                            <p class="sub">Registre saídas e acompanhe a emissão das suas notas fiscais.</p>
                        </div><button class="button">＋ Nova saída</button>
                    </div>
                    <section class="stats" style="grid-template-columns:repeat(3,1fr)">
                        <div class="card stat">
                            <div class="stat-top">Saídas no mês <span class="stat-icon">↗</span></div>
                            <strong>327</strong><small><span class="trend">↑ 8,2%</span> vs. mês anterior</small>
                        </div>
                        <div class="card stat">
                            <div class="stat-top">Valor faturado <span class="stat-icon">R$</span></div><strong>R$
                                84.260</strong><small>período de outubro</small>
                        </div>
                        <div class="card stat">
                            <div class="stat-top">NF-e pendentes <span class="stat-icon"
                                    style="background:#fff2dc;color:#ad7418">!</span></div>
                            <strong>3</strong><small>aguardando emissão</small>
                        </div>
                    </section>
                    <div class="toolbar"><label class="search">⌕<input placeholder="Buscar por número ou cliente"></label>
                        <div class="filter-group"><select class="select">
                                <option>Todos os status</option>
                            </select><button class="button light">Exportar</button></div>
                    </div>
                    <div class="card table-card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NF-e</th>
                                    <th>Cliente</th>
                                    <th>Data</th>
                                    <th>Itens</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([['#000.342', 'Mercado Central LTDA', '03 out, 10:22', '12 itens', 'R$ 2.840,90', 'Emitida', ''], ['#000.341', 'Empório da Vila', '03 out, 09:46', '5 itens', 'R$ 1.126,40', 'Emitida', ''], ['Rascunho #34', 'Comercial São Jorge', '02 out, 16:18', '8 itens', 'R$ 980,00', 'Pendente', 'warning'], ['#000.340', 'Casa das Especiarias', '02 out, 14:30', '3 itens', 'R$ 495,60', 'Emitida', '']] as $item)
                                    <tr>
                                        <td><b>{{ $item[0] }}</b></td>
                                        <td>{{ $item[1] }}</td>
                                        <td style="color:var(--muted)">{{ $item[2] }}</td>
                                        <td>{{ $item[3] }}</td>
                                        <td><b>{{ $item[4] }}</b></td>
                                        <td><span class="tag {{ $item[6] }}">{{ $item[5] }}</span></td>
                                        <td class="more">•••</td>
                                </tr>@endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
</x-layouts.app>
