<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$server = $_GET['server'] ?? '';
$port = $_GET['port'] ?? '80';
$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';

if (empty($server) || empty($username) || empty($password)) {
    die(json_encode(['error' => 'Parâmetros inválidos']));
}

// Testa vários endpoints
$endpoints = [
    "/player_api.php?username=$username&password=$password",
    "/api.php?username=$username&password=$password",
    "/get.php?username=$username&password=$password&type=m3u_plus"
];

foreach ($endpoints as $endpoint) {
    $url = "http://$server:$port$endpoint";
    
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5 // Timeout de 5 segundos
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response !== false) {
            if (strpos($response, '#EXTM3U') !== false) {
                die(json_encode([
                    'username' => $username,
                    'password' => $password,
                    'playlist' => $response
                ]));
            }
            
            $json = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json['user_info'])) {
                die(json_encode([
                    'user_info' => $json['user_info'],
                    'username' => $username,
                    'password' => $password
                ]));
            }
        }
    } catch (Exception $e) {
        continue;
    }
}

die(json_encode(['error' => 'Nenhum endpoint respondeu']));
?>