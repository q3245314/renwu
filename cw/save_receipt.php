<?php
header('Content-Type: application/json');

$jsonFile = __DIR__ . '/sj.json';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => '无效的数据']);
    exit;
}

$data = file_exists($jsonFile) ? (json_decode(file_get_contents($jsonFile), true) ?: []) : [];

// 确保必要的数组键存在
$data['lastNumber'] = $data['lastNumber'] ?? 0;
$data['receipts'] = $data['receipts'] ?? [];

// 检查是否为重复数据
$isDuplicate = false;
foreach ($data['receipts'] as $receipt) {
    if ($receipt['date'] == $input['date'] &&
        $receipt['name'] == $input['name'] &&
        $receipt['paymentMethod'] == $input['paymentMethod'] &&
        $receipt['amount'] == $input['amount'] &&
        $receipt['reason'] == $input['reason'] &&
        $receipt['operator'] == $input['operator']) {
        $isDuplicate = true;
        break;
    }
}

if ($isDuplicate) {
    echo json_encode(['success' => true, 'message' => '数据已存在，直接下载图片', 'number' => $input['number'], 'formattedNumber' => $input['formattedNumber']]);
    exit;
}

// 生成新编号
$newNumber = $data['lastNumber'] + 1;
$newFormattedNumber = sprintf('%05d', $newNumber);

// 添加新收据数据
$data['receipts'][] = [
    'number' => $newNumber,
    'formattedNumber' => $newFormattedNumber,
    'date' => $input['date'],
    'name' => $input['name'],
    'paymentMethod' => $input['paymentMethod'],
    'amount' => $input['amount'],
    'amountText' => $input['amountText'],
    'reason' => $input['reason'],
    'operator' => $input['operator'],
    'receiver' => $input['receiver']
];

// 更新最后编号
$data['lastNumber'] = $newNumber;

file_put_contents($jsonFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => '保存成功', 'number' => $newNumber, 'formattedNumber' => $newFormattedNumber]);
