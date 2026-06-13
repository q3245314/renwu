<?php
header('Content-Type: application/json');
$fileContent = file_get_contents('tasks.json');
if (empty($fileContent)) {
    // 初始化文件内容
    $initialData = [
        'version' => 1,
        'tasks' => [],
        'systemTitle' => "任务领取系统"
    ];
    file_put_contents('tasks.json', json_encode($initialData, JSON_UNESCAPED_UNICODE));
    echo json_encode($initialData, JSON_UNESCAPED_UNICODE);
} else {
    echo $fileContent;
}
?>