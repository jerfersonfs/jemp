<x-layouts.app title="Entrar · JEMP">
    <main class="login-page">
        <section class="login-panel">
            <div class="login-card"><a href="{{ route('home') }}" class="brand"><span
                        class="brand-mark">J</span>JEMP</a>
                <h1>Bem-vindo de volta</h1>
                <p>Acesse sua operação e acompanhe tudo em um só lugar.</p>
                <form>
                    <div class="field"><label for="email">E-mail</label><input id="email" type="email"
                            placeholder="voce@empresa.com"></div>
                    <div class="field"><label for="senha">Senha</label><input id="senha" type="password"
                            placeholder="••••••••"></div><button class="button" type="button">Entrar na
                        plataforma</button>
                </form>
                <p class="login-note">Ambiente demonstrativo · sem autenticação ativa</p>
            </div>
        </section>
        <aside class="login-visual">
            <div class="visual-copy"><span class="badge">Gestão simples, crescimento real</span>
                <h2>Seu estoque, sob controle.</h2>
                <p>Organize produtos, inventário e saídas com uma visão clara do seu negócio.</p>
                <div class="visual-chart"><small>Valor em estoque</small><b>R$ 128.450,00</b>
                    <div class="bars"><i style="height:28px"></i><i></i><i></i><i></i><i></i></div>
                </div>
            </div>
        </aside>
    </main>
</x-layouts.app>