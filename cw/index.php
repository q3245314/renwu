<?php
$jsonFile = __DIR__ . '/sj.json';

$currentNumber = 1;

if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
    if ($data && isset($data['lastNumber'])) {
        $currentNumber = $data['lastNumber'] + 1;
    }
}

// 保持代码2的逻辑：5位编号
$formattedNumber = sprintf('%05d', $currentNumber); 
// 保持代码2的日期逻辑
$currentDate = date('Y年m月d日 H:i');
?>
<script>
    // 初始化编号变量
    window.currentNumber = <?php echo $currentNumber; ?>;
    window.formattedNumber = '<?php echo $formattedNumber; ?>';
</script>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>电子收据开具</title>
    <script src="html-to-image.min.js"></script>
<style>
/* --- 通用变量与重置 (来自代码2) --- */
:root {
    --primary-gold: #b8860b;
    --primary-color: #4f46e5;
    --primary-light: #fdf8e8;
    --text-main: #333333;
    --text-sub: #666666;
    --border-color: #eeeeee;
    --bg-color: #f7f8fa;
    --white: #ffffff;
    --card-bg: #f5f5f5;
    --radius: 12px
}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:var(--bg-color);color:var(--text-main);padding:1px;min-height:100vh}

/* --- 输入面板样式 (保持代码2) --- */
.nav-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;background:var(--card-bg);padding:15px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.back-link{color:var(--primary-color);text-decoration:none;font-weight:600;font-size:.9rem;display:flex;align-items:center}
.page-title{font-size:1.25rem;font-weight:700;color:var(--text-main)}
.input-panel h2{text-align:center;color:var(--primary-gold);margin-bottom:25px;font-size:1.4rem;font-weight:700;position:relative;padding-bottom:15px}
.input-panel h2::after{content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:40px;height:3px;background:var(--primary-gold);border-radius:2px;opacity:.6}
.input-row{margin-bottom:20px}
.input-row label{display:block;font-size:.95rem;color:var(--text-main);margin-bottom:8px;font-weight:600}
.form-control{width:100%;padding:12px 15px;font-size:1rem;border:1px solid #ddd;border-radius:10px;outline:0;background:#fafafa;transition:all .3s ease;appearance:none}
.form-control:focus{border-color:var(--primary-gold);background:#fff;box-shadow:0 0 0 3px rgba(184,134,11,.1)}
.donation-list-container{border:1px solid var(--border-color);border-radius:12px;overflow:hidden;background:#fff}
.donation-item{display:flex;align-items:center;padding:12px 15px;border-bottom:1px solid var(--border-color);transition:background .2s;position:relative}
.donation-item:last-child{border-bottom:none}
.donation-item:hover{background-color:#fcfcfc}
.donation-checkbox{width:20px;height:20px;margin-right:12px;accent-color:var(--primary-gold);cursor:pointer;flex-shrink:0}
.item-name{flex:1;font-size:1rem;font-weight:500;color:#444;cursor:pointer}
.item-amount{width:100px;padding:8px 10px;font-size:.95rem;border:1px solid #ddd;border-radius:8px;text-align:right;transition:all .2s;background:#f5f5f5;color:var(--primary-gold);font-weight:600}
.item-amount:focus{border-color:var(--primary-gold);background:#fff;outline:0}
.item-amount:disabled{background:0 0;border-color:transparent;color:transparent}
.donation-checkbox:checked~.item-amount{background:#fff;border-color:#ddd;color:var(--primary-gold)}
.donation-checkbox:checked~.item-amount:focus{border-color:var(--primary-gold)}
.donation-item.other-item{display:block}
.other-header{display:flex;align-items:center;width:100%}
.custom-reason{width:100%;margin-top:10px;padding:10px;font-size:.9rem;border:1px solid #ddd;border-radius:8px;display:none}
.donation-checkbox:checked~div .custom-reason,.donation-item:has(.donation-checkbox:checked) .custom-reason{display:block}
.total-section{background:var(--primary-light);padding:15px 20px;border-radius:12px;margin-top:20px;display:flex;justify-content:space-between;align-items:center;border:1px solid rgba(184,134,11,.2)}
.total-label{font-size:1rem;color:var(--text-sub)}
.total-amount-display{font-size:1.5rem;color:var(--primary-gold);font-weight:800}
.operator-select{display:flex;gap:10px;justify-content:space-between}
.operator-option{flex:1;position:relative}
.operator-option input{position:absolute;opacity:0;width:100%;height:100%;cursor:pointer}
.operator-option span{display:block;text-align:center;padding:10px 5px;background:#f0f0f0;border-radius:8px;color:#666;font-size:.95rem;border:1px solid transparent;transition:all .2s}
.operator-option input:checked+span{background:var(--primary-gold);color:#fff;box-shadow:0 4px 10px rgba(184,134,11,.3)}
.btn-generate{display:block;width:100%;background:linear-gradient(135deg,#b8860b 0,#d4af37 100%);color:#fff;border:none;padding:16px;font-size:1.1rem;font-weight:600;border-radius:50px;cursor:pointer;margin-top:30px;box-shadow:0 4px 15px rgba(184,134,11,.3);transition:transform .2s,box-shadow .2s}
.btn-generate:active{transform:scale(.98);box-shadow:0 2px 8px rgba(184,134,11,.2)}
.btn-group-action{display:flex;gap:15px;justify-content:center}
.btn-download{flex:1;background:var(--white);color:var(--primary-gold);border:1px solid var(--primary-gold);padding:12px;font-size:1rem;border-radius:8px;cursor:pointer;transition:all .2s}
.btn-download.primary{background:var(--primary-gold);color:#fff}
.btn-download:hover{opacity:.9}



/* 切换下面代码，可以实现收据默认显示与隐藏 */

.input-panel{background:var(--white);max-width:600px;margin:0 auto 20px;border-radius:16px;padding:25px 20px;box-shadow:0 4px 20px rgba(0,0,0,.05);position:relative}
.btn-container{display:none;max-width:600px;margin:20px auto;text-align:center}
.certificate{display:none;width:700px;margin:0 auto;background:#fff;padding:25px 35px;color:#000;font-family:SimSun,"Songti SC",STSong,"Times New Roman",serif;position:relative;box-shadow:0 0 15px rgba(0,0,0,.1);box-sizing:border-box}

/* .input-panel{display:none;background:var(--white);max-width:600px;margin:0 auto 20px;border-radius:16px;padding:25px 20px;box-shadow:0 4px 20px rgba(0,0,0,.05);position:relative}
.btn-container{display:block;max-width:600px;margin:20px auto;text-align:center}
.certificate{display:block;width:700px;margin:0 auto;background:#fff;padding:25px 35px;color:#000;font-family:SimSun,"Songti SC",STSong,"Times New Roman",serif;position:relative;box-shadow:0 0 15px rgba(0,0,0,.1);box-sizing:border-box} */


/* 头部：收据标题 + 编号 */
.rc-header {
    text-align: center;
    position: relative;
    margin-bottom: 5px;
    height: 60px;
}
.rc-title {
    font-size: 28px;
    font-weight: 650;
    display: inline-block;
    border-bottom: 4px double #000;
    color: #222;
    width: 160px;
    text-align: center;
}
.rc-no {
    position: absolute;
    right: 0;
    top: 15px;
    font-size: 18px;
    font-family: "Times New Roman", serif;
    color: #333;
}
.rc-no span {
    color: #d00;
    font-size: 16px;
    /* font-weight: bold; */
    letter-spacing: 1px;
    margin-left: 5px;
}
/* 日期行 (修改：适配代码2的长日期格式) */
.rc-date-row {
    text-align: center;
    font-size: 14px;
    margin-bottom: 5px;
    display: flex;
    justify-content: center;
    align-items: baseline;
    color: #333;
}
.rc-date-input {
    border: none;
    border-bottom: 1px solid transparent;
    text-align: center;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    background: transparent;
    color: #000;
    font-weight: 500;
    margin: 0 1px;
}
.rc-date-input:focus { border-bottom: 1px solid #b8860b; }

/* 核心表格盒子 */
.rc-box {
    border: 2.5px solid #333;
    display: flex;
    position: relative;
}

/* 右侧竖排文字 (存根联) */
.rc-sidebar {
    width: 45px;
    border-left: 1px solid #333;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 0;
    font-size: 12px;
    line-height: 1.3;
    color: #444;
}
.vertical-text {
    writing-mode: vertical-lr;
    letter-spacing: 4px;
}

/* 表格主体 */
.rc-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* 行样式 */
.rc-row {
    display: flex;
    border-bottom: 1px solid #333;
    min-height: 50px;
    align-items: stretch;
}
.rc-row:last-child {
    border-bottom: none;
}

.rc-cell {
    display: flex;
    align-items: center;
    padding: 0px 10px;
    position: relative;
}

/* 标签文字 */
.rc-label {
    white-space: nowrap; /* 强制文字单行显示，不换行 */
    font-size: 16px;     /* 字体大小17像素 */
    color: #444;         /* 文字颜色为深灰色 */
    padding-right: 0px;  /* 右侧内边距8像素 */
    font-family: "KaiTi", "楷体", "STKaiti", "华文楷体", "SimSun", "宋体", "Heiti TC", "黑体", sans-serif;
    display: flex;               /* 开启Flex布局 */
    align-items: center;         /* 垂直方向（交叉轴）居中对齐 */
    justify-content: center;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
/* 输入区域 */
.rc-input {
    flex: 1;
    border: none;
    font-size: 14px;
    font-family: "KaiTi", "楷体", "STKaiti", "华文楷体", "SimSun", "宋体", "Heiti TC", "黑体", sans-serif;
    color: #000;
    outline: none;
    padding: 0 5px;
    background: transparent;
    width: 100%;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* 第一行：交款单位 | 收款方式 */
.row-1 .unit-group {
    flex: 1;
    display: flex;
    align-items: center;
    border-right: 1px solid #333;
}
.row-1 .method-group {
    width: 230px;
    display: flex;
    align-items: center;
}

/* 第二行：大写 | 小写 */
.row-2 .chn-group {
    flex: 1;
    display: flex;
    align-items: center;
    border-right: 1px solid #333;
}
.row-2 .num-group {
    width: 230px;
    display: flex;
    align-items: center;
}
/* 金额标签 */
.num-prefix {
    font-size: 18px;
    margin-right: 5px;
    font-family: "Times New Roman", serif;
}
/* 金额输入 */
.amount-small {
    font-family: "Times New Roman", serif;
    font-size: 16px;
    /* font-weight: bold; */
}

/* 第三行：收款事由 (修改：支持Textarea高度自适应) */
.row-3 {
    /* 移除固定高度，允许内容撑开 */
    height: auto; 
    min-height: 60px;
}
.row-3 .rc-label {
    align-self: center;
}

/* 让所有 textarea（收款事由、大写金额）都支持自动居中和多行撑开 */
textarea.rc-input {
    resize: none;
    overflow: hidden;
    line-height: 25px;
    padding-top: 15px;
    padding-bottom: 15px;
    height: 55px;
    display: block;
    box-sizing: border-box;
    width: 100%;
}

/* 底部签名栏 */
.rc-footer {
    margin-top: 12px;
    display: flex;
    justify-content: space-between;
    font-size: 15px;
    padding: 0 10px;
    color: #444;
    font-family: "KaiTi", "楷体", "STKaiti", "华文楷体", "SimSun", "宋体", "Heiti TC", "黑体", sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
.footer-item {
    display: flex;
    align-items: center;
    width: 20%;
}
.footer-input {
    border: none;
    background: transparent;
    font-family: "KaiTi", "楷体", "STKaiti", "华文楷体", "SimSun", "宋体", "Heiti TC", "黑体", sans-serif;
    font-size: 16px;
    width: 70px;
    margin-left: 5px;
    outline: none;
    color: #000;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* 印章覆盖 */
.rc-seal {
    position: absolute;
    /* right: 85px; */
    left: 50px;
    bottom: -18px;
    width: 130px;
    height: 130px;
    pointer-events: none;
    opacity: 0.85;
    transform: rotate(-6deg);
    z-index: 10;
    mix-blend-mode: multiply;
}

@media print{body{background:#fff;padding:0}
.certificate{box-shadow:none;border:none;page-break-inside:avoid;display:block!important; margin:0;}
.btn-container,.input-panel{display:none!important}
}
</style>
</head>

<body>
    <!-- 输入面板 (来自代码2) -->
    <div class="input-panel">
        <div class="nav-header">
            <a href="admin.php" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                后台
            </a>
            <div class="page-title">
                电子收据开具
            </div>
            <div style="width: 60px;"></div>
        </div>
        
        <div class="input-row">
            <label>供养人姓名</label>
            <input type="text" id="inputName" class="form-control" placeholder="默认为三宝弟子">
        </div>

        <div class="input-row">
            <label>支付方式</label>
            <select id="inputPaymentMethod" class="form-control">
                <option>微信1</option>
                <option>微信2</option>
                <option>现金</option>
                <option>建行扫码</option>
                <option>交行扫码</option>
                <option>农行扫码</option>
                <option>工行转账</option>
            </select>
        </div>

        <div class="input-row">
            <label>善款用途（勾选并输入金额）</label>
            <div class="donation-list-container donation-items">
                <?php 
                $items = ['功德', '道粮', '供斋', '建寺', '建塔', '放生', '供僧', '弘法', '香火', '菜款', '素食', '大殿', '安居僧'];
                foreach($items as $item): ?>
                <div class="donation-item">
                    <input type="checkbox" class="donation-checkbox" data-reason="<?php echo $item; ?>">
                    <span class="item-name"><?php echo $item; ?></span>
                    <input type="number" class="item-amount" placeholder="0.00" min="0" step="1" disabled>
                </div>
                <?php endforeach; ?>
                <div class="donation-item other-item">
                    <div class="other-header">
                        <input type="checkbox" class="donation-checkbox" data-reason="其他">
                        <span class="item-name">其他</span>
                        <input type="number" class="item-amount" placeholder="0.00" min="0" step="1" disabled>
                    </div>
                    <div>
                        <input type="text" class="custom-reason" placeholder="可更改用途名称" disabled>
                    </div>
                </div>
            </div>
        </div>
        <div class="input-row">
            <label>备注信息</label>
            <input type="text" class="remark-input form-control" placeholder="可填写额外说明">
        </div>
        <div class="input-row">
            <label>经办人</label>
            <div class="operator-select">
                <label class="operator-option">
                    <input type="radio" name="operator" value="李" checked>
                    <span>李</span>
                </label>
                <label class="operator-option">
                    <input type="radio" name="operator" value="张">
                    <span>张</span>
                </label>
                <label class="operator-option">
                    <input type="radio" name="operator" value="贵">
                    <span>贵</span>
                </label>
                <label class="operator-option">
                    <input type="radio" name="operator" value="修">
                    <span>修</span>
                </label>
            </div>
        </div>
        <div class="total-section">
            <span class="total-label">合计金额</span>
            <div class="total-amount-display">
                ¥ <span id="totalAmount">0.00</span>
            </div>
        </div>
        <button class="btn-generate" onclick="generateReceipt()">生成电子收据</button>
    </div>

    <div class="btn-container">
        <div class="btn-group-action">
            <button class="btn-download" onclick="location.reload()">重新填写</button>
            <button class="btn-download primary" onclick="downloadReceipt()">提交并下载</button>
        </div>
        <p style="margin-top: 15px; color: #999; font-size: 0.8rem;">提示：点击图片中的文字可进行临时微调</p>
    </div>

    <!-- 
      === 新的收据结构 (UI来自代码1，但适配了代码2的功能) === 
    -->
    <div class="certificate">
        <!-- 标题区域 -->
        <div class="rc-header">
            <div class="rc-title">收<span style="width: 40px; display: inline-block;"></span>据</div>
            <div class="rc-no">No <span><?php echo $formattedNumber; ?></span></div>
        </div>

        <!-- 日期区域 (改为代码2的输入格式) -->
        <div class="rc-date-row">
            日期时间：
            <input type="text" class="rc-date-input" id="receiptDate" value="<?php echo $currentDate; ?>" onfocus="storeOriginalDate(this)" onblur="validateAndRevertDate(this)">
        </div>

        <!-- 表格主体 -->
        <div class="rc-box">
            <!-- 左侧内容 -->
            <div class="rc-content">
                <!-- 第一行 -->
                <div class="rc-row row-1">
                    <div class="rc-cell unit-group">
                        <span class="rc-label">交款单位：</span>
                        <input type="text" id="rcName" class="rc-input" value="">
                    </div>
                    <div class="rc-cell method-group">
                        <span class="rc-label">收款方式：</span>
                        <input type="text" id="rcMethod" class="rc-input" value="微信">
                    </div>
                </div>
                <!-- 第二行 -->
                <div class="rc-row row-2">
                    <div class="rc-cell chn-group">
                        <span class="rc-label">人民币(大写)：</span>
                        <textarea id="rcAmountBig" class="rc-input" disabled></textarea>
                    </div>
                    <div class="rc-cell num-group">
                        <span class="num-prefix">¥:</span>
                        <input type="text" id="rcAmountSmall" class="rc-input amount-small" value="" disabled>
                    </div>
                </div>
                <!-- 第三行 -->
                <div class="rc-row row-3">
                    <div class="rc-cell" style="flex:1">
                        <span class="rc-label">收款事由：</span>
                        <!-- 这里的input替换为textarea以支持代码2的功能 -->
                        <textarea id="rcReason" class="rc-input" disabled></textarea>
                    </div>
                </div>
            </div>

            <!-- 右侧竖排 -->
            <div class="rc-sidebar">
                <!-- <div class="vertical-text">（三）交给付款单位</div> -->
                <div class="vertical-text">交给付款单位</div>
            </div>
        </div>

        <!-- 底部签名 -->
        <div class="rc-footer">
            <div class="footer-item">财会主管：</div>
            <div class="footer-item">记账：</div>
            <div class="footer-item">出纳：</div>
            <div class="footer-item">审核：</div>
            <!-- 经办人ID从 code1 的 rcOperator 对应 code2 的逻辑 -->
            <div class="footer-item">经办：<input type="text" id="rcOperator" class="footer-input" value=""></div>
        </div>

        <!-- 印章 -->
        <div class="rc-seal">
            <svg width="100%" height="100%" viewBox="0 0 360 260">
                <ellipse cx="180" cy="130" rx="160" ry="105" fill="none" stroke="#c40000" stroke-width="6" />
                <path id="topArc" d="M 32 130 A 148 93 0 0 1 328 130" fill="none" />
                <path id="bottomArc" d="M 32 130 A 148 93 0 0 0 328 130" fill="none" />
                <text fill="#c40000" font-size="28" letter-spacing="36" dy="25">
                    <textPath href="#topArc" startOffset="55%" text-anchor="middle">通化市卧佛寺</textPath>
                </text>
                <text x="180" y="148" fill="#c40000" font-size="25" text-anchor="middle" letter-spacing="2">2205011313963</text>
                <text x="180" y="198" fill="#c40000" font-size="22" letter-spacing="6" text-anchor="middle">发票专用章</text>
            </svg>
        </div>
    </div>

<script>
    // --- 1. 日期修正功能 (来自代码2) ---
    function storeOriginalDate(input) {
        input.dataset.original = input.value;
    }
    function validateAndRevertDate(input) {
        const val = input.value.trim();
        const original = input.dataset.original; 
        const regex = /^(\d{4})\s*年\s*(\d{1,2})\s*月\s*(\d{1,2})\s*日\s+(\d{1,2})[:：](\d{1,2})$/;
        const match = val.match(regex);
        if (!match) {
            input.value = original;
            return;
        }
        const year = parseInt(match[1], 10);
        const month = parseInt(match[2], 10);
        const day = parseInt(match[3], 10);
        const hour = parseInt(match[4], 10);
        const minute = parseInt(match[5], 10);
        let isValid = true;
        if (month < 1 || month > 12) isValid = false;
        if (hour < 0 || hour > 23) isValid = false;
        if (minute < 0 || minute > 59) isValid = false;
        const daysInMonth = new Date(year, month, 0).getDate(); 
        if (day < 1 || day > daysInMonth) isValid = false;
        if (!isValid) {
            input.value = original;
        } else {
            const fmt = n => n < 10 ? '0' + n : n;
            input.value = `${year}年${fmt(month)}月${fmt(day)}日 ${fmt(hour)}:${fmt(minute)}`;
        }
    }

    // --- 2. 计算逻辑 (来自代码2) ---
    document.querySelector('.donation-items').addEventListener('change', function(e) {
        if (e.target.classList.contains('donation-checkbox')) {
            const amountInput = e.target.closest('.donation-item').querySelector('.item-amount');
            const customReasonInput = e.target.closest('.donation-item').querySelector('.custom-reason');
            if (e.target.checked) {
                amountInput.disabled = false;
                if (customReasonInput) {
                    customReasonInput.disabled = false;
                }
                amountInput.focus();
            } else {
                amountInput.disabled = true;
                amountInput.value = '';
                if (customReasonInput) {
                    customReasonInput.disabled = true;
                    customReasonInput.value = '';
                }
            }
            calculateTotal();
        }
    });

    document.querySelector('.donation-items').addEventListener('input', function(e) {
        if (e.target.classList.contains('item-amount')) {
            calculateTotal();
        }
    });

    function calculateTotal() {
        const total = Array.from(document.querySelectorAll('.donation-checkbox:checked')).reduce((sum, checkbox) => {
            const amountInput = checkbox.closest('.donation-item').querySelector('.item-amount');
            return sum + (parseFloat(amountInput.value) || 0);
        }, 0);
        document.getElementById('totalAmount').textContent = total.toFixed(2);
    }

    // --- 3. 生成逻辑 (融合代码) ---
    function generateReceipt() {
        // 获取输入
        const nameInput = document.getElementById('inputName').value;
        const paymentMethodInput = document.getElementById('inputPaymentMethod').value;
        const operatorInput = document.querySelector('input[name="operator"]:checked');
        const checkedBoxes = document.querySelectorAll('.donation-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('请至少选择一项善款用途');
            return;
        }
        for (const checkbox of checkedBoxes) {
            const amount = parseFloat(checkbox.closest('.donation-item').querySelector('.item-amount').value) || 0;
            if (amount <= 0) {
                alert('您选择的用途中，有金额未填写');
                return;
            }
        }

        // 构造事由 (Code 2 逻辑：带金额和换行)
        const checkedItems = Array.from(checkedBoxes).map(checkbox => {
            let reason = checkbox.closest('.donation-item').querySelector('.item-name').textContent;
            const customReasonInput = checkbox.closest('.donation-item').querySelector('.custom-reason');
            if (customReasonInput && customReasonInput.value.trim()) {
                reason = customReasonInput.value.trim();
            }
            const amount = parseFloat(checkbox.closest('.donation-item').querySelector('.item-amount').value) || 0;
            return reason + '：' + amount + '元';
        });

        const nameValue = nameInput.trim() || '三宝弟子';
        const remarkInput = document.querySelector('.remark-input');
        const remarkValue = remarkInput ? remarkInput.value.trim() : '';
        
        // 拼接事由
        let reasonText = checkedItems.join('，');
        if (remarkValue) {
            reasonText += '（备注：' + remarkValue + '）';
        }

        const totalAmount = parseFloat(document.getElementById('totalAmount').textContent);
        
        // --- 填充到新的收据UI (Code 1 样式) ---
        
        // 1. 交款单位
        document.getElementById('rcName').value = nameValue;
        
        // 2. 收款方式
        document.getElementById('rcMethod').value = paymentMethodInput;
        
        // 3. 金额填充 (Code 1 是分开的，我们需要拆分 Code 2 的逻辑)
        if (totalAmount > 0) {
            document.getElementById('rcAmountBig').value = numberToChinese(totalAmount); // 仅大写
            // 直接显示实际金额，不用显示.00
            document.getElementById('rcAmountSmall').value = totalAmount % 1 === 0 ? totalAmount.toString() : totalAmount.toFixed(2); // 仅小写
        } else {
             document.getElementById('rcAmountBig').value = '';
             document.getElementById('rcAmountSmall').value = '';
        }
        
        // 4. 收款事由 (Textarea)
        const reasonTextarea = document.getElementById('rcReason');
        reasonTextarea.value = reasonText;
        
        // 5. 经办人
        if (operatorInput) {
            document.getElementById('rcOperator').value = operatorInput.value;
        }
        
        // 切换显示
        document.querySelector('.input-panel').style.display = 'none';
        document.querySelector('.certificate').style.display = 'block';
        document.querySelector('.btn-container').style.display = 'block';

        // 触发textarea高度自适应
        reasonTextarea.dispatchEvent(new Event('input'));
        document.getElementById('rcAmountBig').dispatchEvent(new Event('input'));

        // 滚动顶部
        window.scrollTo(0, 0);
    }

    // --- 4. 工具函数 ---
// Textarea 自适应高度 (抽取为通用函数，支持多个文本框)
    function autoResizeTextarea(el) {
        el.style.height = '15px'; // 对应 CSS 中的初始高度
        const scrollHeight = el.scrollHeight;
        if (scrollHeight > 15) {
            el.style.height = `${scrollHeight}px`;
        }
    }
    // 监听收款事由
    document.getElementById('rcReason').addEventListener('input', function() {
        autoResizeTextarea(this);
    });
    // 监听大写金额
    document.getElementById('rcAmountBig').addEventListener('input', function() {
        autoResizeTextarea(this);
    });

    // 数字转中文 (Code 2)
    function numberToChinese(num) {
        if (typeof num !== 'number' || isNaN(num)) return '';
        const CN_NUM = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
        const CN_UNIT = ['', '拾', '佰', '仟'];
        const CN_GROUP = ['', '万', '亿', '兆'];
        const CN_DEC = ['角', '分'];
        if (num === 0) return '零元整';
        let integer = Math.floor(num);
        let decimal = Math.round((num - integer) * 100);
        function convertInt(n) {
            if (n === 0) return '零';
            let str = '';
            let groupIndex = 0;
            let needZero = false;
            while (n > 0) {
                let group = n % 10000;
                if (group === 0) {
                    if (str !== '') needZero = true;
                } else {
                    let groupStr = '';
                    let unitIndex = 0;
                    let zeroFlag = false;
                    while (group > 0) {
                        const digit = group % 10;
                        if (digit === 0) {
                            if (!zeroFlag && groupStr !== '') {
                                groupStr = '零' + groupStr;
                                zeroFlag = true;
                            }
                        } else {
                            groupStr = CN_NUM[digit] + CN_UNIT[unitIndex] + groupStr;
                            zeroFlag = false;
                        }
                        unitIndex++;
                        group = Math.floor(group / 10);
                    }
                    if (needZero) {
                        str = '零' + str;
                        needZero = false;
                    }
                    str = groupStr + CN_GROUP[groupIndex] + str;
                }
                groupIndex++;
                n = Math.floor(n / 10000);
            }
            return str;
        }
        function convertDec(n) {
            if (n === 0) return '整';
            let jiao = Math.floor(n / 10);
            let fen = n % 10;
            let res = '';
            if (jiao > 0) res += CN_NUM[jiao] + CN_DEC[0];
            if (fen > 0) res += CN_NUM[fen] + CN_DEC[1];
            return res;
        }
        return convertInt(integer) + '元' + convertDec(decimal);
    }

    // 保存数据 (Code 2)
    function saveReceiptData() {
        return new Promise((resolve, reject) => {
            const paymentMethodElement = document.getElementById('rcMethod'); // 改为获取新UI的ID
            const receiptData = {
                number: window.currentNumber,
                formattedNumber: window.formattedNumber,
                date: document.getElementById('receiptDate').value,
                name: document.getElementById('rcName').value,
                paymentMethod: paymentMethodElement.value,
                amount: document.getElementById('rcAmountSmall').value.replace(/,/g, ''),
                amountText: document.getElementById('rcAmountBig').value, 
                reason: document.getElementById('rcReason').value,
                operator: document.getElementById('rcOperator').value,
                receiver: '卧佛寺'
            };
            fetch('save_receipt.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(receiptData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('收据数据已保存');
                        // 更新编号
                        if (data.number && data.formattedNumber) {
                            window.currentNumber = data.number;
                            window.formattedNumber = data.formattedNumber;
                            document.querySelector('.rc-no span').textContent = data.formattedNumber;
                        }
                        resolve(data);
                    } else {
                        reject(data);
                    }
                })
                .catch(error => {
                    console.error('保存失败:', error);
                    reject(error);
                });
        });
    }

    let downloadCount = 0;
    async function downloadReceipt() {
        try {
            // 保存数据
            await saveReceiptData();
            
            // 下载图片
            downloadCount++;
            const node = document.querySelector('.certificate');
            
            // 截图时微调：移除阴影，确保纯净
            const originalShadow = node.style.boxShadow;
            node.style.boxShadow = 'none';

            htmlToImage.toPng(node, {
                    backgroundColor: '#ffffff',
                    pixelRatio: 2, // 提高清晰度
                    width: node.offsetWidth,
                    height: node.offsetHeight,
                    style: {
                        margin: '0',
                        transform: 'none'
                    }
                })
                .then(dataUrl => {
                    // 恢复阴影
                    node.style.boxShadow = originalShadow;
                    
                    const link = document.createElement('a');
                    link.download = '电子收据-' + window.formattedNumber + '-' + downloadCount + '.png';
                    link.href = dataUrl;
                    link.click();
                })
                .catch(error => {
                    console.error('导出失败：', error);
                    alert('导出失败，请检查浏览器控制台');
                });
        } catch (error) {
            console.error('操作失败:', error);
            if (error.message) {
                alert(error.message);
            } else {
                alert('操作失败，请重试');
            }
        }
    }
</script>
</body>
</html>