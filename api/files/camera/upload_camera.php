<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['imagem'])) {
        http_response_code(400);
        exit;
    }

    $file = $_FILES['imagem'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        http_response_code(415);
        exit;
    }

    if ($file['size'] > 1000000) { // 1000 KB
        http_response_code(413);
        exit;
    }

    move_uploaded_file($file['tmp_name'], "files/camera/ultima.jpg");
    echo json_encode(["status" => "ok"]);
}
http_response_code(405);
exit;  