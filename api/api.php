<?php
header('Content-Type: application/json; charset=utf-8');

// --- Caminho base dos ficheiros ---
$base_dir = __DIR__ . "/files/";

// --- Função auxiliar: guardar histórico ---
function guardar_historico($nome, $valor) {
    global $base_dir;
    $hora = date("Y-m-d H:i:s");
    $ficheiro = $base_dir . "$nome/log.txt"; // mantive log.txt
    $linha = "$hora;$valor\n";
    file_put_contents($ficheiro, $linha, FILE_APPEND);
}

// --- GET: Obter dados ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['nome'])) {
        $nome = $_GET['nome'];
        $valor_file = "$base_dir$nome/valor.txt";
        $hora_file = "$base_dir$nome/hora.txt";
        $nome_file = "$base_dir$nome/nome.txt";

        if (file_exists($valor_file) && file_exists($hora_file)) {
            $valor = trim(file_get_contents($valor_file));
            $hora = trim(file_get_contents($hora_file));
            $nome_disp = file_exists($nome_file) ? trim(file_get_contents($nome_file)) : ucfirst($nome);

            echo json_encode(["nome" => $nome_disp, "valor" => $valor, "hora" => $hora]);
            exit;
        } else {
            http_response_code(404);
            echo json_encode(["erro" => "Dispositivo não encontrado"]);
            exit;
        }
    }

    // Histórico de um sensor
    if (isset($_GET['log'])) {
        $nome = $_GET['log'];
        $ficheiro = "$base_dir$nome/log.txt";
        if (file_exists($ficheiro)) {
            $linhas = file($ficheiro, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $dados = [];
            foreach ($linhas as $linha) {
                [$hora, $valor] = explode(";", $linha);
                $dados[] = ["hora" => $hora, "valor" => $valor]; // valor agora é texto
            }
            echo json_encode($dados);
        } else {
            echo json_encode([]);
        }
        exit;
    }
}

// --- POST: Atualizar dados (Apenas para administradores)---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

session_start();
    if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? 'guest') !== 'admin') {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(["erro" => "Acesso negado. Apenas administradores podem alterar o estado dos dispositivos."]);
        exit;
    }

    $nome = $_POST['nome'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $hora = $_POST['hora'] ?? date("Y-m-d H:i:s");
    
    $dispositivos_validos = ['luz', 'aquecedor', 'portao', 'temperatura', 'presenca', 'luminosidade', 'humidade'];
        if (!in_array($nome, $dispositivos_validos)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(["erro" => "Dispositivo inválido."]);
            exit;
        }
    if ($nome && $valor !== '') {
        $dir = $base_dir . "$nome/";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        // Converte 1/0 em texto amigável
        if ($nome === 'portao') {
            $valor_texto = $valor === '1' ? 'Aberto' : 'Fechado';
        } elseif ($nome === 'luz' || $nome === 'aquecedor') {
            $valor_texto = $valor === '1' ? 'Ligado' : 'Desligado';
        } else {
            $valor_texto = $valor; // sensores continuam como estão
        }

        file_put_contents($dir . "valor.txt", $valor_texto);
        file_put_contents($dir . "hora.txt", $hora);
        file_put_contents($dir . "nome.txt", ucfirst($nome));

        guardar_historico($nome, $valor_texto);

        echo json_encode(["status" => "ok", "msg" => "Dados atualizados com sucesso"]);
    } else {
        http_response_code(400);
        echo json_encode(["erro" => "Parâmetros inválidos"]);
    }
    exit;
}

http_response_code(400);
echo json_encode(["erro" => "Requisição inválida"]);
?>

