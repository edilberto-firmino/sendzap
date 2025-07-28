<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Sistema')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            background-color: #343a40;
            padding-top: 60px;
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .sidebar a {
            color: white;
            padding: 10px 20px;
            display: block;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .sidebar-collapsed {
            transform: translateX(-250px);
        }
        .content-expanded {
            margin-left: 0;
        }
        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
        }
    </style>
</head>
<body>
    {{-- Botão para mostrar/ocultar a sidebar --}}
    <button class="btn btn-secondary toggle-btn" id="toggleSidebar">☰</button>

    {{-- Sidebar --}}
    <div class="sidebar" id="sidebar">
        <h4 class="text-center">SendZap</h4>
        <div class="dropdown">
            <a class="dropdown-toggle d-block px-3 py-2 text-white text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                📇 Contatos
            </a>
            <ul class="dropdown-menu dropdown-menu-dark">
                <li><a class="dropdown-item" href="{{ route('contacts.index') }}">Listar Contatos</a></li>
                <li><a class="dropdown-item" href="{{ route('contacts.create') }}">Novo Contato</a></li>
            </ul>
        </div>
        {{-- Adicione outros links conforme necessário --}}
    </div>

    {{-- Conteúdo principal --}}
    <div class="content" id="content">
        @yield('content')
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-collapsed');
            content.classList.toggle('content-expanded');
        });
    </script>
</body>
</html>
