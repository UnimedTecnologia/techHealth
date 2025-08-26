<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Ops! Erro de Conexão</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 15px;
        }
        .message {
            font-size: 16px;
            margin-bottom: 20px;
            color: #555;
        }
        .icon {
            font-size: 50px;
            color: #dc3545;
            margin-bottom: 15px;
        }
        .btn-home {
            background-color: #00995d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
        }
        .btn-home:hover {
            background-color:rgb(31, 160, 109);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <i class="bi bi-emoji-frown"></i>
        </div>
        <div class="title">Ops! Algo deu errado...</div>
        <div class="message">
            Ocorreu um erro ao tentar se conectar ao banco de dados. <br>
            Por favor, entre em contato com a equipe de suporte técnico.
        </div>
        <div class="text-center mb-5">
            <?php //session_start(); echo $_SESSION['errodb'] ?>
        </div>
        <a class="btn-home" href="../index.php">
            <i class="bi bi-house-door"></i> Voltar para a Página Inicial
        </a>
    </div>
</body>
</html>
