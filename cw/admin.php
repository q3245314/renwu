<?php
$jsonFile = __DIR__ . '/sj.json';
// 开启 Session 以保存筛选状态
session_start();
// 如果点击了重置按钮，清除 Session 并跳转
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    unset($_SESSION['admin_filters']);
    header('Location: admin.php');
    exit;
}
// 定义获取筛选值的逻辑：POST(提交查询) > SESSION(历史记录) > DEFAULT(默认值)
function getFilterValue($key, $default)
{
    if (isset($_POST[$key])) {
        $_SESSION['admin_filters'][$key] = $_POST[$key]; // 更新 Session
        return $_POST[$key];
    }
    return isset($_SESSION['admin_filters'][$key]) ? $_SESSION['admin_filters'][$key] : $default;
}
// 初始化变量
$startDate = getFilterValue('start_date', date('Y-m-d'));
$endDate = getFilterValue('end_date', date('Y-m-d'));
$paymentMethod = getFilterValue('payment_method', 'all');
$donationReason = getFilterValue('donation_reason', 'all');

$data = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
$receipts = $data['receipts'] ?? [];
// 获取所有支付方式
$paymentMethods = [];
foreach ($receipts as $receipt) {
    if (isset($receipt['paymentMethod']) && !empty($receipt['paymentMethod'])) {
        $paymentMethods[] = $receipt['paymentMethod'];
    }
}
$paymentMethods = array_unique($paymentMethods);
sort($paymentMethods);
// 获取所有善款用途
$donationReasons = [];
foreach ($receipts as $receipt) {
    if (isset($receipt['reason']) && !empty($receipt['reason'])) {
        if (preg_match_all('/([^：，]+)：([\d.]+)元/u', $receipt['reason'], $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $donationReasons[] = trim($match[1]);
            }
        }
    }
}
$donationReasons = array_unique($donationReasons);
sort($donationReasons);

$filteredReceipts = [];
$projectStats = [];
$totalAmount = 0;
$selectedReceipt = null;
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $receiptId = intval($_GET['id']);
    foreach ($receipts as $receipt) {
        if ($receipt['number'] == $receiptId) {
            $selectedReceipt = $receipt;
            break;
        }
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'void' && isset($_GET['id'])) {
    $receiptId = intval($_GET['id']);
    foreach ($data['receipts'] as &$receipt) {
        if ($receipt['number'] == $receiptId) {
            $receipt['void'] = true;
            break;
        }
    }
    file_put_contents($jsonFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: admin.php?action=view&id=' . $receiptId);
    exit;
}
if (isset($_GET['action']) && $_GET['action'] == 'restore' && isset($_GET['id'])) {
    $receiptId = intval($_GET['id']);
    foreach ($data['receipts'] as &$receipt) {
        if ($receipt['number'] == $receiptId) {
            unset($receipt['void']);
            break;
        }
    }
    file_put_contents($jsonFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: admin.php?action=view&id=' . $receiptId);
    exit;
}
if (!empty($startDate) && !empty($endDate)) {
    $startTimestamp = strtotime($startDate . ' 00:00:00');
    $endTimestamp = strtotime($endDate . ' 23:59:59');
    foreach ($receipts as $receipt) {
        $receiptDate = preg_replace('/年|月/', '-', $receipt['date']);
        $receiptDate = preg_replace('/日.*/', '', $receiptDate);
        $receiptTimestamp = strtotime($receiptDate);
        if ($receiptTimestamp >= $startTimestamp && $receiptTimestamp <= $endTimestamp) {
            $receiptPaymentMethod = $receipt['paymentMethod'] ?? '';
            if ($paymentMethod === 'all' || $paymentMethod === $receiptPaymentMethod) {
                $matchReason = true;
                if ($donationReason !== 'all' && isset($receipt['reason'])) {
                    $matchReason = false;
                    if (preg_match_all('/([^：，]+)：([\d.]+)元/u', $receipt['reason'], $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $match) {
                            if (trim($match[1]) === $donationReason) {
                                $matchReason = true;
                                break;
                            }
                        }
                    }
                }
                if ($matchReason) {
                    $filteredReceipts[] = $receipt;
                    if (!isset($receipt['void'])) {
                        $amount = 0;
                        if (isset($receipt['amount']) && is_numeric($receipt['amount'])) {
                            $amount = floatval($receipt['amount']);
                        } else if (preg_match('/¥\s*([\d,.]+)/', $receipt['amountText'], $matches)) {
                            $amount = floatval(str_replace(',', '', $matches[1]));
                        }
                        $totalAmount += $amount;
                        if (preg_match_all('/([^：，]+)：([\d.]+)元/u', $receipt['reason'], $matches, PREG_SET_ORDER)) {
                            foreach ($matches as $match) {
                                $projectName = trim($match[1]);
                                $projectAmount = floatval($match[2]);
                                if (!isset($projectStats[$projectName])) $projectStats[$projectName] = 0;
                                $projectStats[$projectName] += $projectAmount;
                            }
                        }
                    }
                }
            }
        }
    }
} else {
    $filteredReceipts = $receipts;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>后台管理统计</title>
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-light: #818cf8;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-sub: #6b7280;
            --border-color: #e5e7eb;
        }
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:var(--bg-color);color:var(--text-main);line-height:1.5;padding-bottom:40px}
.container{max-width:1050px;margin:0 auto;padding:15px}
.nav-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;background:var(--card-bg);padding:15px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.back-link{color:var(--primary-color);text-decoration:none;font-weight:600;font-size:.9rem;display:flex;align-items:center}
.page-title{font-size:1.25rem;font-weight:700;color:var(--text-main)}
.filter-card{background:var(--card-bg);padding:20px;border-radius:12px;margin-bottom:20px;box-shadow:0 4px 6px rgba(0,0,0,.05)}
.filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:15px}
.form-group{display:flex;flex-direction:column}
.form-group label{font-size:.85rem;color:var(--text-sub);margin-bottom:6px;font-weight:500}
.form-control{padding:10px;border:1px solid var(--border-color);border-radius:8px;font-size:.95rem;background:#f9fafb;transition:border .3s;width:100%;outline:0}
.form-control:focus{border-color:var(--primary-color);background:#fff}
.btn-group{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:5px}
.btn{padding:10px 20px;border:none;border-radius:8px;font-weight:600;cursor:pointer;text-align:center;font-size:.95rem;transition:opacity .2s;text-decoration:none;display:inline-block}
.btn:active{opacity:.8;transform:scale(.98)}
.btn-primary{background:var(--primary-color);color:#fff}
.btn-secondary{background:#9ca3af;color:#fff}
.btn-danger{background:var(--danger-color);color:#fff}
.btn-success{background:var(--success-color);color:#fff}
.stats-summary{margin-bottom:20px}
.stat-card{background:linear-gradient(135deg,var(--primary-color),var(--primary-light));color:#fff;padding:25px;border-radius:16px;box-shadow:0 10px 15px -3px rgba(79,70,229,.3);text-align:center}
.stat-card h3{font-size:.9rem;opacity:.9;margin-bottom:5px;font-weight:400}
.stat-card .amount{font-size:2.5rem;font-weight:800;margin:10px 0}
.stat-card .meta{font-size:.85rem;opacity:.8;background:rgba(255,255,255,.2);display:inline-block;padding:4px 12px;border-radius:20px}
.section-title{font-size:1.1rem;font-weight:700;margin:25px 0 15px;color:var(--text-main);display:flex;align-items:center}
.section-title::before{content:'';width:4px;height:18px;background:var(--primary-color);margin-right:10px;border-radius:2px}
.table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:12px;box-shadow:0 2px 5px rgba(0,0,0,.05);background:var(--card-bg)}
.simple-table{width:100%;border-collapse:collapse;min-width:100%}
.simple-table td,.simple-table th{padding:12px 15px;text-align:left;border-bottom:1px solid var(--border-color);white-space:nowrap}
.simple-table th{background:#f8fafc;font-weight:600;color:var(--text-sub);font-size:.85rem}
.simple-table tr:last-child td{border-bottom:none}
.simple-table .total-row{background:#fff7ed;font-weight:700}
.text-right{text-align:right}
.text-green{color:var(--success-color);font-weight:600}
.receipt-list{display:flex;flex-direction:column;gap:15px}
@media (min-width:768px){.receipt-list{display:block}
.data-table{width:100%;border-collapse:collapse;background:var(--card-bg);border-radius:12px;overflow:hidden;box-shadow:0 2px 5px rgba(0,0,0,.05)}
.data-table thead{display:table-header-group;background:#f8fafc}
.data-table td,.data-table th{padding:15px 12px;text-align:left;border-bottom:1px solid var(--border-color);display:table-cell;white-space:nowrap;vertical-align:middle}
.data-table td:nth-child(6),.data-table th:nth-child(6){white-space:normal;min-width:180px;line-height:1.4;text-align:right}
.data-table th{font-weight:600;color:var(--text-sub)}
.data-table tr{display:table-row;margin-bottom:0;box-shadow:none;border-radius:0;background:0 0}
.data-table tr:hover{background-color:#f9fafb}
.mobile-label{display:none}
}
@media (max-width:767px){.data-table,.data-table tbody,.data-table td,.data-table tr{display:block;width:100%}
.data-table thead{display:none}
.data-table tr{background:var(--card-bg);margin-bottom:15px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);padding:15px;position:relative}
.data-table td{padding:5px 0;border:none;display:flex;justify-content:space-between;align-items:flex-start;font-size:.95rem}
.data-table td:nth-child(4){font-size:1.2rem;font-weight:700;color:var(--primary-color);margin:5px 0;order:-1}
.data-table td:nth-child(3){font-size:1.1rem;font-weight:600;color:var(--text-main);order:-2;margin-bottom:5px}
.data-table td:last-child{margin-top:10px;padding-top:10px;border-top:1px solid #f3f4f6;justify-content:flex-end}
.mobile-label{font-weight:400;color:var(--text-sub);font-size:.85rem;margin-right:10px;white-space:nowrap}
}
.detail-view{background:var(--card-bg);border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);margin:0 auto;max-width:600px;position:relative}
.detail-header{background:var(--primary-color);color:#fff;padding:30px 20px;text-align:center;position:relative}
.detail-header h1{font-size:1.5rem;margin-bottom:5px}
.detail-header .receipt-no{font-family:monospace;opacity:.8;font-size:.9rem}
.detail-body{padding:30px 20px}
.detail-row{display:flex;margin-bottom:15px;border-bottom:1px solid #f3f4f6;padding-bottom:12px;align-items:baseline}
.detail-row:last-child{border-bottom:none}
.detail-label{width:90px;color:var(--text-sub);font-size:.9rem;flex-shrink:0}
.detail-value{flex:1;color:var(--text-main);font-weight:500;font-size:1rem;text-align:right}
.detail-amount{font-size:1.5rem;color:var(--primary-color);font-weight:700}
.detail-footer{padding:20px;background:#f9fafb;display:flex;gap:10px;justify-content:center}
.status-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:600}
.status-valid{background:#d1fae5;color:#065f46}
.status-void{background:#fee2e2;color:#991b1b}
.stamp-void{position:absolute;top:15px;right:15px;border:3px solid #ef4444;color:#ef4444;font-weight:700;font-size:24px;padding:5px 10px;border-radius:8px;transform:rotate(-15deg);opacity:.8;pointer-events:none}
.no-data{text-align:center;padding:40px 20px;color:var(--text-sub)}
    </style>
</head>

<body>
    <div class="container">
        <!-- 头部导航 -->
        <div class="nav-header">
            <a href="index.php" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                返回
            </a>
            <div class="page-title">
                <?php echo $selectedReceipt ? '收据详情' : '财务统计'; ?>
            </div>
            <div style="width: 60px;"></div> <!-- 占位 -->
        </div>

        <?php if ($selectedReceipt): ?>
            <!-- 详情视图 -->
            <div class="detail-view">
                <div class="detail-header">
                    <h1>电子收据</h1>
                    <div class="receipt-no">NO. <?php echo htmlspecialchars($selectedReceipt['formattedNumber']); ?></div>
                    <?php if (isset($selectedReceipt['void'])): ?>
                        <div class="stamp-void">作废</div>
                    <?php endif; ?>
                </div>
                <div class="detail-body">
                    <div class="detail-row">
                        <span class="detail-label">供养人</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedReceipt['name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">捐赠金额</span>
                        <span class="detail-value detail-amount"><?php echo htmlspecialchars($selectedReceipt['amountText']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">支付方式</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedReceipt['paymentMethod']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">善款用途</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedReceipt['reason']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">开票日期</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedReceipt['date']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">经办人</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedReceipt['operator']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">收款方</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedReceipt['receiver']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">当前状态</span>
                        <span class="detail-value">
                            <?php if (isset($selectedReceipt['void'])): ?>
                                <span class="status-badge status-void">作废</span>
                            <?php else: ?>
                                <span class="status-badge status-valid">有效</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <div class="detail-footer">
                    <?php if (!isset($selectedReceipt['void'])): ?>
                        <a href="admin.php?action=void&id=<?php echo $selectedReceipt['number']; ?>" class="btn btn-danger">作废收据</a>
                    <?php else: ?>
                        <a href="admin.php?action=restore&id=<?php echo $selectedReceipt['number']; ?>" class="btn btn-success">恢复收据</a>
                    <?php endif; ?>
                    <a href="admin.php" class="btn btn-secondary">返回列表</a>
                </div>
            </div>

        <?php else: ?>

            <!-- 筛选区域 -->
            <form method="POST" action="" class="filter-card">
                <div class="filter-grid">
                    <div class="form-group">
                        <label>开始日期</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>
                    <div class="form-group">
                        <label>结束日期</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                    <div class="form-group">
                        <label>支付方式</label>
                        <select name="payment_method" class="form-control">
                            <option value="all" <?php echo $paymentMethod === 'all' ? 'selected' : ''; ?>>全部方式</option>
                            <?php foreach ($paymentMethods as $method): ?>
                                <option value="<?php echo htmlspecialchars($method); ?>" <?php echo $paymentMethod === $method ? 'selected' : ''; ?>><?php echo htmlspecialchars($method); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>善款用途</label>
                        <select name="donation_reason" class="form-control">
                            <option value="all" <?php echo $donationReason === 'all' ? 'selected' : ''; ?>>全部用途</option>
                            <?php foreach ($donationReasons as $reason): ?>
                                <option value="<?php echo htmlspecialchars($reason); ?>" <?php echo $donationReason === $reason ? 'selected' : ''; ?>><?php echo htmlspecialchars($reason); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="btn-group">
                    <a href="admin.php?action=reset" class="btn btn-secondary">重置条件</a>
                    <button type="submit" class="btn btn-primary">查询统计</button>
                </div>
            </form>

            <?php if (!empty($startDate) && !empty($endDate)): ?>
                <!-- 统计卡片 -->
                <div class="stats-summary">
                    <div class="stat-card">
                        <h3>总金额</h3>
                        <div class="amount">¥ <?php echo number_format($totalAmount, $totalAmount == (int)$totalAmount ? 0 : 1); ?></div>
                        <div class="meta"><?php echo count($filteredReceipts); ?> 笔有效收据</div>
                    </div>
                </div>

                <!-- 项目统计表 -->
                <?php if (!empty($projectStats)): ?>
                    <div class="section-title">用途统计</div>
                    <div class="table-responsive">
                        <table class="simple-table">
                            <thead>
                                <tr>
                                    <th>项目名称</th>
                                    <th class="text-right">金额</th>
                                    <th class="text-right">占比</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projectStats as $project => $amount): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($project); ?></td>
                                        <td class="text-right text-green">¥ <?php echo number_format($amount, 2); ?></td>
                                        <td class="text-right"><?php $percentage = $totalAmount > 0 ? ($amount / $totalAmount) * 100 : 0;
                                                                echo $percentage < 1 ? '<1%' : number_format($percentage, 0) . '%'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td>合计</td>
                                    <td class="text-right"><?php echo number_format($totalAmount, 2); ?></td>
                                    <td class="text-right">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- 收据列表 (自适应：PC是表格，手机是卡片) -->
            <div class="section-title">收据明细</div>
            <?php if (!empty($filteredReceipts)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>编号</th>
                            <th>日期</th>
                            <th>供养人</th>
                            <th>金额</th>
                            <th>支付方式</th>
                            <th>事由</th>
                            <th>经办人</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredReceipts as $receipt): ?>
                            <tr>
                                <td><span class="mobile-label">编号:</span><?php echo htmlspecialchars($receipt['formattedNumber']); ?></td>
                                <td><span class="mobile-label">日期:</span><?php echo htmlspecialchars($receipt['date']); ?></td>
                                <td><span class="mobile-label">供养人:</span><?php echo htmlspecialchars($receipt['name']); ?></td>
                                <td style="font-weight:bold; color:var(--success-color);">
                                    <span class="mobile-label">金额:</span>
                                    <?php
                                    $amountText = $receipt['amountText'];
                                    // 修改：去掉了 ¥ 后面的空格，确保不换行
                                    if (preg_match('/¥\s*([\d,.]+)/', $amountText, $matches)) {
                                        echo htmlspecialchars($matches[1]);
                                    } else {
                                        echo htmlspecialchars($amountText);
                                    }
                                    ?>
                                </td>
                                <td><span class="mobile-label">方式:</span><?php echo htmlspecialchars($receipt['paymentMethod'] ?? ''); ?></td>
                                <td>
                                    <span class="mobile-label">用途:</span>
                                    <span style="flex: 1; text-align: right; word-break: break-word; line-height: 1.4;"><?php echo htmlspecialchars($receipt['reason']); ?></span>
                                </td>
                                <td><span class="mobile-label">经办:</span><?php echo htmlspecialchars($receipt['operator']); ?></td>
                                <td>
                                    <span class="mobile-label">状态:</span>
                                    <?php if (isset($receipt['void'])): ?>
                                        <span style="color:var(--danger-color);font-weight:bold;">作废</span>
                                    <?php else: ?>
                                        <span style="color:var(--success-color); font-weight:500;">有效</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="admin.php?action=view&id=<?php echo $receipt['number']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; width: auto; display: inline-block; white-space: nowrap;">查看详情</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>当前筛选条件下暂无数据</p>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>

</html>