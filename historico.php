<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Lista de dispositivos válidos
$validos = ['temperatura', 'presenca', 'luminosidade', 'luz', 'aquecedor', 'portao'];

// Dispositivo selecionado via GET ou primeiro da lista
$nome = $_GET['nome'] ?? $validos[0];

if (!in_array($nome, $validos)) {
    die("Dispositivo inválido.");
}

// Função para processar entradas do log com validação
function processarLog($arquivo) {
    $entries = [];
    if (file_exists($arquivo)) {
        $lines = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $ln) {
            $parts = explode(';', $ln);
            $entries[] = ['hora' => htmlspecialchars($parts[0] ?? '', ENT_QUOTES, 'UTF-8'), 'valor' => htmlspecialchars($parts[1] ?? '', ENT_QUOTES, 'UTF-8')];
        }
    }
    return $entries;
}

// Lê o log do dispositivo
$logfile = __DIR__ . "/api/files/$nome/log.txt";
$entries = processarLog($logfile);
?>

<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Histórico - <?= htmlspecialchars($nome) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="#">SmartHome</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link active" href="historico.php?nome=<?= urlencode($nome) ?>">Histórico</a></li>
        </ul>
        <a href="logout.php" class="btn btn-outline-dark">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <header>
      <h2 class="fw-bold">Histórico de <?= htmlspecialchars($nome) ?></h2>
      <p>Bem-vindo, <?= htmlspecialchars($_SESSION['username']) ?> | Registros em tempo real</p>
    </header>

    <a href="dashboard.php" class="btn btn-secondary mb-3">Voltar ao Dashboard</a>

    <!-- Dropdown de dispositivos -->
    <form method="get" class="mb-3">
      <label for="nome" class="form-label fw-bold">Escolha o dispositivo:</label>
      <select name="nome" id="nome" class="form-select" onchange="this.form.submit()">
        <?php foreach ($validos as $device): ?>
          <option value="<?= htmlspecialchars($device) ?>" <?= ($device === $nome) ? 'selected' : '' ?>>
            <?= ucfirst($device) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <div class="card">
      <div class="card-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Data/Hora</th>
              <th>Valor</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_reverse($entries) as $e): ?>
              <tr>
                <td><?= $e['hora'] ?></td>
                <td><?= $e['valor'] ?> <?= ($nome === 'temperatura') ? '°C' : (($nome === 'luminosidade') ? '%' : '') ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($entries)): ?>
              <tr>
                <td colspan="2" class="text-center text-muted">Sem registros disponíveis para este dispositivo.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
