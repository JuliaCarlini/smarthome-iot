<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("refresh:5;url=index.php");
    die("Acesso restrito");
}

$user_role = $_SESSION['role'] ?? 'guest';

// Função para ler valor e hora de um dispositivo com validação
function lerValor($dispositivo) {
    // Sanitizar dispositivo
    $dispositivo = htmlspecialchars($dispositivo, ENT_QUOTES, 'UTF-8');
    $valor_arquivo = "api/files/$dispositivo/valor.txt";
    $hora_arquivo = "api/files/$dispositivo/hora.txt";
    $valor = file_exists($valor_arquivo) ? trim(file_get_contents($valor_arquivo)) : "—";
    $hora = file_exists($hora_arquivo) ? trim(file_get_contents($hora_arquivo)) : "—";
    return [$valor, $hora];
}

// Ler dados dos sensores
$sensores = ['temperatura', 'presenca', 'luminosidade', 'humidade'];
foreach ($sensores as $s) {
    [${"valor_$s"}, ${"hora_$s"}] = lerValor($s);
}

// Ler dados dos atuadores e definir imagens/botões
[$valor_luz, ] = lerValor('luz');
[$valor_aquecedor, ] = lerValor('aquecedor');
[$valor_portao, ] = lerValor('portao');

$luz_img = ($valor_luz === "Ligado") ? "img/light-on.png" : "img/light-off.png";
$luz_btn = ($valor_luz === "Ligado") ? "Desligar" : "Ligar";

$aquecedor_img = ($valor_aquecedor === "Ligado") ? "img/aquecedor-on.png" : "img/aquecedor-off.png";
$aquecedor_btn = ($valor_aquecedor === "Ligado") ? "Desligar" : "Ligar";

$portao_img = ($valor_portao === "Aberto") ? "img/portao-on.png" : "img/portao-off.png";
$portao_btn = ($valor_portao === "Aberto") ? "Fechar" : "Abrir";

// Definir estados de alerta com validação
function calcularEstado($tipo, $valor) {
    if ($valor === "—") return ['texto' => '—', 'cor' => 'secondary'];
    switch ($tipo) {
        case 'temperatura':
            if ($valor > 30) return ['texto' => 'Elevada', 'cor' => 'danger'];
            elseif ($valor >= 20) return ['texto' => 'Normal', 'cor' => 'warning'];
            else return ['texto' => 'Baixa', 'cor' => 'info'];
        case 'presenca':
            return ($valor === 'Detectada') ? ['texto' => 'Detectada', 'cor' => 'danger'] : ['texto' => 'Normal', 'cor' => 'success'];
        case 'luminosidade':
            if ($valor < 20) return ['texto' => 'Baixa', 'cor' => 'danger'];
            elseif ($valor <= 70) return ['texto' => 'Normal', 'cor' => 'warning'];
            else return ['texto' => 'Alta', 'cor' => 'success'];
        case 'humidade':
            if ($valor > 70) return ['texto' => 'Elevada', 'cor' => 'danger'];
            elseif ($valor >= 40) return ['texto' => 'Normal', 'cor' => 'warning'];
            else return ['texto' => 'Baixa', 'cor' => 'info'];  
    }
}

$estado_temperatura = calcularEstado('temperatura', $valor_temperatura);
$estado_presenca = calcularEstado('presenca', $valor_presenca);
$estado_luminosidade = calcularEstado('luminosidade', $valor_luminosidade);
$estado_humidade = calcularEstado('humidade', $valor_humidade);

?>

<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SmartHome Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Navbar principal -->
<nav class="navbar navbar-expand-lg shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">SmartHome</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="historico.php">Histórico</a></li>
      </ul>
      <a href="logout.php" class="btn btn-outline-dark">Logout</a>
    </div>
  </div>
</nav>

<!-- Cabeçalho personalizado -->
<header class="text-center my-4">
  <h2 class="fw-bold">Bem-vindo, <?= htmlspecialchars($_SESSION['username']) ?></h2>
  <p>Monitoriza e controla a tua casa inteligente em tempo real</p>
</header>

<div class="container">
  <!-- Seção de sensores -->
  <div class="row mt-4" id="sensores">

    <!-- Temperatura -->
    <div class="col-md-3">
      <div class="card text-center shadow">
        <div class="card-header sensor">Temperatura</div>
        <div class="card-body">
          <img src="img/<?= ($valor_temperatura > 30 ? 'temperatura-high.png' : 'temperatura-low.png') ?>" 
     class="img-fluid" style="max-height:120px;">
          <h4 id="temperatura_valor"><?= $valor_temperatura ?> ºC</h4>
        </div>
        <div class="card-footer"><small id="temperatura_hora">Atualização: <?= $hora_temperatura ?></small></div>
      </div>
    </div>

    <!-- Humidade -->
    <div class="col-md-3">
      <div class="card text-center shadow">
        <div class="card-header sensor">Humidade</div>
        <div class="card-body">
          <img src="img/<?= ($valor_humidade > 70 ? 'humidade-high.png' : 'humidade-low.png') ?>"
              class="img-fluid" style="max-height:120px;">
          <h4 id="humidade_valor"><?= $valor_humidade ?> %</h4>
        </div>
        <div class="card-footer">
          <small id="humidade_hora">Atualização: <?= $hora_humidade ?></small>
        </div>
      </div>
    </div>

    <!-- Presença -->
    <div class="col-md-3">
      <div class="card text-center shadow">
        <div class="card-header sensor">Presença</div>
        <div class="card-body">
          <img src="<?= ($valor_presenca === 'Detectada' ? 'img/presenca-on.png' : 'img/presenca-off.png') ?>" class="img-fluid" style="max-height:120px;">
          <h4 id="presenca_valor"><?= htmlspecialchars($valor_presenca) ?></h4>
        </div>
        <div class="card-footer"><small id="presenca_hora">Atualização: <?= $hora_presenca ?></small></div>
      </div>
    </div>

    <!-- Luminosidade -->
    <div class="col-md-3">
      <div class="card text-center shadow">
        <div class="card-header sensor">Luminosidade</div>
        <div class="card-body">
          <img src="<?= ($valor_luminosidade < 50 ? 'img/luminosidade-low.png' : 'img/luminosidade-high.png') ?>" class="img-fluid" style="max-height:120px;">
          <h4 id="luminosidade_valor"><?= $valor_luminosidade ?> %</h4>
        </div>
        <div class="card-footer"><small id="luminosidade_hora">Atualização: <?= $hora_luminosidade ?></small></div>
      </div>
    </div>
  </div>

  <hr class="my-4">

  <!-- Seção de atuadores -->
  <div class="row" id="atuadores">
    <div class="col-md-4">
      <div class="card text-center shadow">
        <div class="card-header atuador">Luzes</div>
        <div class="card-body">
          <img src="<?= $luz_img ?>" style="max-height:120px;">
          <button class="btn btn-toggle mt-3" id="btn_luz" onclick="toggleAtuador('luz')"><?= $luz_btn ?></button>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card text-center shadow">
        <div class="card-header atuador">Aquecedor</div>
        <div class="card-body">
          <img src="<?= $aquecedor_img ?>" style="max-height:120px;">
          <button class="btn btn-toggle mt-3" id="btn_aquecedor" onclick="toggleAtuador('aquecedor')"><?= $aquecedor_btn ?></button>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card text-center shadow">
        <div class="card-header atuador">Portão</div>
        <div class="card-body">
          <img src="<?= $portao_img ?>" style="max-height:120px;">
          <button class="btn btn-toggle mt-3" id="btn_portao" onclick="toggleAtuador('portao')"><?= $portao_btn ?></button>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabela de sensores -->
  <div class="container mt-5">
    <div class="card">
      <div class="card-header"><strong>Tabela de Sensores</strong></div>
      <div class="card-body">
        <table class="table">
          <thead>
            <tr>
              <th>Tipo de Dispositivo IoT</th>
              <th>Valor</th>
              <th>Data de Atualização</th>
              <th>Estado Alertas</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Temperatura</td>
              <td id="tabela_temperatura_valor"><?= $valor_temperatura ?>º</td>
              <td id="tabela_temperatura_hora"><?= $hora_temperatura ?></td>
              <td><span class="badge rounded-pill bg-<?= $estado_temperatura['cor'] ?>"><?= $estado_temperatura['texto'] ?></span></td>
            </tr>
            <tr>
              <td>Humidade</td>
              <td id="tabela_humidade_valor"><?= $valor_humidade ?>%</td>
              <td id="tabela_humidade_hora"><?= $hora_humidade ?></td>
              <td><span class="badge rounded-pill bg-<?= $estado_humidade['cor'] ?>"><?= $estado_humidade['texto'] ?></span></td>
            </tr>
            <tr>
              <td>Presença</td>
              <td id="tabela_presenca_valor"><?= $valor_presenca ?></td>
              <td id="tabela_presenca_hora"><?= $hora_presenca ?></td>
              <td><span class="badge rounded-pill bg-<?= $estado_presenca['cor'] ?>"><?= $estado_presenca['texto'] ?></span></td>
            </tr>
            <tr>
              <td>Luminosidade</td>
              <td id="tabela_luminosidade_valor"><?= $valor_luminosidade ?>%</td>
              <td id="tabela_luminosidade_hora"><?= $hora_luminosidade ?></td>
              <td><span class="badge rounded-pill bg-<?= $estado_luminosidade['cor'] ?>"><?= $estado_luminosidade['texto'] ?></span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>


<script>
// Função para toggle de atuadores
async function toggleAtuador(nome) {
  const btn = document.getElementById(`btn_${nome}`);
  const img = btn.closest('.card-body').querySelector('img');
  const valorAPI = (nome === "portao") ? (btn.innerText === "Abrir" ? "1" : "0") : (btn.innerText === "Ligar" ? "1" : "0");

  const formData = new FormData();
  formData.append("nome", nome);
  formData.append("valor", valorAPI);

  const response = await fetch("api/api.php", { method: "POST", body: formData });
  const result = await response.json();

  if (nome === "luz") {
    img.src = valorAPI === "1" ? "img/light-on.png" : "img/light-off.png";
    btn.innerText = valorAPI === "1" ? "Desligar" : "Ligar";
  } else if (nome === "aquecedor") {
    img.src = valorAPI === "1" ? "img/aquecedor-on.png" : "img/aquecedor-off.png";
    btn.innerText = valorAPI === "1" ? "Desligar" : "Ligar";
  } else if (nome === "portao") {
    img.src = valorAPI === "1" ? "img/portao-on.png" : "img/portao-off.png";
    btn.innerText = valorAPI === "1" ? "Fechar" : "Abrir";
  }
}


// Função para atualizar dados automaticamente
async function atualizarDados() {
  try {
    // Buscar dados de sensores via API
  const sensores = ['temperatura', 'presenca', 'luminosidade', 'humidade'];
    for (const sensor of sensores) {
      const response = await fetch(`api/api.php?nome=${sensor}`);
      const data = await response.json();
      if (data) {
     document.getElementById(`${sensor}_valor`).innerText =
        data.valor +
        (sensor === 'temperatura' ? ' ºC' :
        sensor === 'luminosidade' || sensor === 'humidade' ? ' %' : '');
      document.getElementById(`tabela_${sensor}_valor`).innerText =
        data.valor +
        (sensor === 'temperatura' ? 'º' :
        sensor === 'luminosidade' || sensor === 'humidade' ? '%' : '');
        document.getElementById(`tabela_${sensor}_hora`).innerText = data.hora;
      }
    }
  } catch (error) {
    console.error('Erro ao atualizar dados:', error);
  }
}

// Atualizar a cada 5 segundos
setInterval(() => {
  atualizarDados();
}, 5000);

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>