@props([
    'name' => '',
    'email' => '',
    'username' => '@peduarte',
])

<x-dialogs.dialog
    title="Editar perfil"
    description="Atualize os dados do seu perfil. Salve quando terminar."
    :show-actions="false"
    {{ $attributes }}>
    <form class="jemp-profile-form" method="post">
        @csrf
        <label><span>Nome</span><input name="name" placeholder="Seu nome" value="{{ $name ?? '' }}"></label>
        <label><span>E-mail</span><input type="email" name="email" placeholder="voce@exemplo.com" value="{{ $email ?? '' }}"></label>
        <label><span>Usuário</span><input name="username" placeholder="@usuario" value="{{ $username ?? '@peduarte' }}"></label>
        <div class="jemp-profile-form__footer"><button type="submit">Salvar alterações</button></div>
    </form>
</x-dialogs.dialog>

<style>
    .jemp-profile-form {
        display: grid;
        gap: var(--spacing-md, 16px);
        font-family: var(--font-family, sans-serif);
    }

    .jemp-profile-form label {
        display: grid;
        grid-template-columns: minmax(82px, .55fr) minmax(0, 1.6fr);
        align-items: center;
        gap: var(--spacing-sm, 8px);
        color: var(--color-secondary, #1b4041);
        font-size: var(--font-size-label, 14px);
    }

    .jemp-profile-form input {
        min-width: 0;
        box-sizing: border-box;
        border: 1px solid var(--color-subheading, #8cb7b8);
        border-radius: 8px;
        background: white;
        padding: 10px 12px;
        color: var(--color-secondary, #1b4041);
        font: inherit;
    }

    .jemp-profile-form input:focus {
        outline: 2px solid color-mix(in srgb, var(--color-primary, #0a9680) 25%, transparent);
        border-color: var(--color-primary, #0a9680);
    }

    .jemp-profile-form__footer {
        display: flex;
        justify-content: flex-end;
        margin-top: var(--spacing-xs, 4px);
    }

    .jemp-profile-form__footer button {
        min-height: 42px;
        border: 0;
        border-radius: 5px;
        background: var(--color-primary, #0a9680);
        padding: 8px 18px;
        color: white;
        font: inherit;
        cursor: pointer;
    }

    @media (max-width: 520px) {
        .jemp-profile-form label {
            grid-template-columns: 1fr;
        }
    }
</style>
