<?php
// Ficheiro com Perfis de Acesso (RBAC)
return [
    'admin' => [
        'password' => '$2y$10$QlSftSSil269610QmBoN1.enuv4rr2P.3zVc1ycsY.QDWJG4lbSB.',
        'role'     => 'admin'
    ],
    'tecnico' => [
        'password' => '$2y$10$v4L/3y96wc5GXn9tG0lPtOH.C5V4idNPnTc1y7f7v78R89qH4WyFq',
        'role'     => 'admin' // Técnicos também podem alterar dispositivos
    ],
    'guest' => [
        'password' => '$2y$10$b7.g/H0O/YyR9KbyW4j8UOnkGcl4W9tT7pM/H1Zf9gI.OaMxeR3U6',
        'role'     => 'guest' // Apenas leitura
    ]
];