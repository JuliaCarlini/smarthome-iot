<?php
session_start();

// 1. Tentar ler as credenciais a partir da Variável de Ambiente do Render
$users_json = getenv('APP_USERS') ?: $_ENV['APP_USERS'] ?? $_SERVER['APP_USERS'] ?? null;
$error_msg = "";

if ($users_json) {
    $users = json_decode($users_json, true);
    
    // Alerta de diagnóstico caso o JSON inserido no Render tenha algum erro de sintaxe
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error_msg = "<div class='alert alert-warning text-center mt-2'>Erro no formato do JSON: " . json_last_error_msg() . "</div>";
    }
} else {
        $error_msg = "<div class='alert alert-danger text-center mt-2'>Username ou password incorretos!</div>";
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
    $password = $_POST['password'] ?? '';

    // Validar se o utilizador existe, se tem a chave password e se o hash coincide
    if (isset($users[$username]) && isset($users[$username]['password']) && password_verify($password, $users[$username]['password'])) {
        session_regenerate_id(true); // Prevenção contra ataques de Session Fixation
        $_SESSION['username'] = $username;
        $_SESSION['role']     = $users[$username]['role'] ?? 'guest'; // Guarda a função (admin ou guest)
        header("Location: dashboard.php");
        exit;
    } else {
        $error_msg = "<div class='alert alert-danger text-center mt-2'>Username ou password incorretos!</div>";
    }
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - SmartHome</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">

    <!-- Formulário de login -->
    <form method="post" class="AulaForm col-12 col-md-4 bg-white p-4 rounded shadow">
      <div class="text-center mb-3">
        <img src="img/estg_h.png" alt="ESTG Logo" class="img-fluid" style="max-width: 150px;">
        <h4 class="mt-3">SmartHome</h4>
      </div>
      <!-- Exibir erro se houver -->
      <?= $error_msg ?>
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input class="form-control" id="username" name="username" type="text" placeholder="Insira o seu username" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" placeholder="Insira a sua password" class="form-control" id="password" required>
      </div>
      <button type="submit" class="btn btn-success w-100">Entrar</button>
  <hr class="text-muted">
      
      <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="preencherGuest()">
        Acesso Rápido (Demo Guest)
      </button>
    </form>

  </div>
</div>

<script>
function preencherGuest() {
    document.getElementById('username').value = 'guest';
    document.getElementById('password').value = 'guest';
}
</script>

</body>
</html>