<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>JEMP - Criar Produto</title>
</head>
<body>
    <h2>Adicionar Novo Produto ao JEMP</h2>

    <!-- Mostrar erros caso a nossa validação bloqueie algo -->
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('produtos.store') }}">
        @csrf

        <div>
            <label>Nome do Produto:</label>
            <input type="text" name="product_name" required>
        </div>
        <br>
        <div>
            <label>Categoria:</label>
            <select name="category" required>
                <option value="PBR">PBR</option>
                <option value="Descartável">Descartável</option>
                <option value="Não Standard">Não Standard</option>
                <!-- Vamos colocar uma opção errada só para testar a segurança -->
                <option value="Invalida">Opção Inválida (Para Teste)</option>
            </select>
        </div>
        <br>
        <div>
            <label>Material:</label>
            <input type="text" name="material" required>
        </div>
        <br>
        <div>
            <label>Preço de Custo (R$):</label>
            <!-- Tente colocar um número negativo para ver o sistema a bloquear! -->
            <input type="number" step="0.01" name="cost_price" required>
        </div>
        <br>
        <div>
            <label>Comprimento (mm):</label>
            <input type="number" name="length_mm" required>
        </div>
        <br>
        <div>
            <label>Largura (mm):</label>
            <input type="number" name="width_mm" required>
        </div>
        <br>
        <button type="submit">Salvar Produto</button>
    </form>
</body>
</html>
