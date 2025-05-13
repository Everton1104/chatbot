<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção Temporária</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e6e6e6;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .maintenance-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .gear-img {
            width: 120px;
            height: 120px;
            margin-bottom: 2rem;
        }
        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
        h1 {
            color: #343a40;
            margin-bottom: 1rem;
        }
        p {
            color: #6c757d;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="maintenance-container">
            <!-- Substitua o src abaixo pelo link da sua imagem de engrenagem -->
            <img src="https://cdn.pixabay.com/animation/2023/06/13/15/13/15-13-46-857_512.gif" alt="Engrenagem" class="gear-img">
            <h1 class="display-4">Manutenção em Andamento</h1>
            <p class="lead">Nosso site está passando por uma manutenção temporária para melhor atendê-lo. Pedimos desculpas pelo inconveniente e agradecemos sua paciência.</p>
            <p>Voltaremos em breve com novidades!</p>
            
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>