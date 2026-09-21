<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <title>
        @yield('title', 'Sistema de Chamados')
    </title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 100%;
            padding: 40px 0;
        }

        .email {
            width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }

        .content {
            padding: 30px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="email">

        @include('emails.components.chamados.header')

        <div class="content">
            @yield('content')
        </div>

        @include('emails.components.chamados.footer')

    </div>

</div>

</body>
</html>