<?php
$data = json_decode(file_get_contents('php://input'), true);
$fileContent = file_get_contents('tasks.json');
$fileData = json_decode($fileContent, true);

if ($data['version'] < $fileData['version']) {
    // 版本冲突，拒绝保存
    http_response_code(409);
    echo json_encode(["status" => "error", "message" => "任务已被他人修改，请重新编辑"]);
    exit;
}

// 更新版本号
$data['version'] = $fileData['version'] + 1;

// 使用 JSON_PRETTY_PRINT 格式化输出并保存
file_put_contents('tasks.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(["status" => "ok", "newVersion" => $data['version']]);
?>