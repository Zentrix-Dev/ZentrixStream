<?php
header('Content-Type: application/json');
error_reporting(0);

$env = @parse_ini_file(__DIR__ . '/.env') ?: [];
$tmdbKey = $env['TMDB_API_KEY'] ?? '';

if (empty($tmdbKey)) {
    echo json_encode(['error' => 'API Key missing or not configured.']);
    exit;
}

$route = $_GET['route'] ?? '';
if (!$route) {
    echo json_encode(['error' => 'No route provided.']);
    exit;
}

$queryParams = $_GET;
unset($queryParams['route']);
$queryParams['api_key'] = $tmdbKey;

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

$queryString = http_build_query($queryParams);
$tmdbUrl = "https://api.themoviedb.org/3/$route?$queryString";
$cacheKey = md5($tmdbUrl);
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTime = 3600; // 1 hour cache

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tmdbUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    file_put_contents($cacheFile, $response);
    echo $response;
} else {
    echo json_encode(['error' => 'Failed to fetch data from TMDB', 'code' => $httpCode]);
}
?>