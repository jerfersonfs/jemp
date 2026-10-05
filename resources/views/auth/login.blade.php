<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>JEMP - Login</title>
</head>
<body>
    <h2>Entrar no JEMP</h2>

    <!-- Mostrar erros de validação -->
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulário apontando para a nossa rota POST -->
    <form method="POST" action="{{ route('login.post') }}">
        @csrf <!-- Proteção obrigatória do Laravel contra ataques CSRF -->

        <div>
            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <br>

        <div>
            <label>Senha:</label>
            <input type="password" name="password" required>
        </div>

        <br>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
