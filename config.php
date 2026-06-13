<?php
$configFile = __DIR__ . '/config.ini';

define('DEFAULT_TASK_LIMIT', 4);
define('DEFAULT_NOTIFY_MODE', 'loop');

$filesToCheck = [
    '01/tasks.json',
    '1/tasks.json',
    '02/tasks.json',
    '2/tasks.json',
    '03/tasks.json',
    '3/tasks.json',
    '04/tasks.json',
    '4/tasks.json',
    '05/tasks.json',
    '5/tasks.json',
    '06/tasks.json',
    '6/tasks.json',
    '07/tasks.json',
    '7/tasks.json',
];

function loadConfig() {
    global $configFile;
    if (file_exists($configFile)) {
        return parse_ini_file($configFile);
    }
    return [];
}

function saveConfig(array $config) {
    global $configFile;
    $lines = [];
    foreach ($config as $key => $val) {
        $lines[] = "$key = $val";
    }
    file_put_contents($configFile, implode("\n", $lines));
}

function getNotifyMode() {
    $config = loadConfig();
    return $config['notify_mode'] ?? DEFAULT_NOTIFY_MODE;
}

function setNotifyMode($mode) {
    $mode = $mode === 'once' ? 'once' : 'loop';
    $config = loadConfig();
    $config['notify_mode'] = $mode;
    saveConfig($config);
}

function getTaskCountLimit() {
    $config = loadConfig();
    return intval($config['task_limit'] ?? DEFAULT_TASK_LIMIT);
}

function setTaskCountLimit($count) {
    $count = max(1, min(50, intval($count)));
    $config = loadConfig();
    $config['task_limit'] = $count;
    saveConfig($config);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($_GET['set_mode']) && isset($data['mode'])) {
        setNotifyMode($data['mode']);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if (isset($_GET['set_limit']) && isset($data['limit'])) {
        setTaskCountLimit($data['limit']);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}
?>
