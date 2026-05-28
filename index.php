<?php
session_start();

// Carregar usuários do arquivo separado
$users_json = getenv('APP_USERS');
$error_msg = "";

if ($users_json) {
    $users = json_decode($users_json, true);
}

// Processar login se for POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
    $password = $_POST['password'] ?? '';

    // Validar se o utilizador existe e a password coincide (usando a nova estrutura com 'role')
    if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
        session_regenerate_id(true);
        $_SESSION['username'] = $username;
        $_SESSION['role']     = $users[$username]['role']; // Guarda se é admin ou guest
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
    </form>
  </div>
</div>
</body>
</html>