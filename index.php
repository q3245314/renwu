<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>任务状态查询</title>
    <link rel="icon" href="https://cdn.weixinss.com/css/logo.svg" type="image/svg+xml" />
    <script src="https://cdn.weixinss.com/css/css/3.4.16.js"></script>
    <link href="https://cdn.weixinss.com/css/css/font-awesome.min.css" rel="stylesheet" />

    <?php require_once 'config.php'; ?>
    <script>
        const initialNotifyMode = "<?php echo getNotifyMode(); ?>";
        const initialTaskLimit = <?php echo getTaskCountLimit(); ?>;
    </script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4387F5',
                        secondary: '#1E40AF',
                        neutral: '#F3F4F6',
                        success: '#10B981',
                        danger: '#EF4444',
                    },
                    fontFamily: {
                        inter: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .shadow-card {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
                    0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            .shadow-hover {
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
                    0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }
            .task-card {
                @apply bg-white rounded-xl shadow-card p-5 transition-all duration-200 hover:shadow-hover hover:-translate-y-0.5;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-inter text-gray-800">
    <div class="container mt-0 mb-8 mx-auto px-8 py-8 max-w-4xl">
        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-bold text-primary mb-2">任务状态查询</h1>
            <p class="text-gray-600 max-w-2xl mx-auto text-base md:text-lg">
                点击“任意位置”可确保激活声音通知
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php
            function generateFileCard($filePath) {
                $title = "错误：未找到任何任务";
                $lastModified = '<span class="text-danger">任务不存在</span>';

                if (file_exists($filePath)) {
                    $jsonContent = json_decode(file_get_contents($filePath), true);
                    $title = isset($jsonContent['systemTitle']) ? $jsonContent['systemTitle'] : "未设置系统标题";
                    $lastModifiedTimestamp = filemtime($filePath);
                    $lastModified = date('Y-m-d H:i:s', $lastModifiedTimestamp);
                }
                ?>
                <div class="task-card border-l-4 border-primary">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-2.5 rounded-lg mr-4">
                            <i class="fa fa-file-text text-primary text-2xl"></i>
                        </div>
                        <div>
                            <!--<a href="https://rw.wofosi.cn/<?php echo dirname($filePath); ?>" target="_blank">-->
                            <a href="<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . dirname($filePath); ?>" target="_blank">
                            <h2 class="text-lg md:text-xl font-semibold">
                                <?php echo $title; ?>
                            </h2>
                            </a>
                        </div>
                    </div>
                    
                        <div class="mt-4">
                            <div class="flex items-center justify-between p-2 bg-neutral rounded-lg">
                                <span class="text-gray-600 text-sm md:text-base">
                                    <i class="fa fa-clock-o text-base md:text-lg mr-2"></i>更新时间 
                                    (<?php echo dirname($filePath); ?>)
                                </span>
                                <span class="text-sm md:text-base text-gray-800">
                                    <?php echo $lastModified; ?>
                                </span>
                            </div>
                        </div>
                    
                </div>
                <?php
            }

            $filesWithTime = [];
            foreach ($filesToCheck as $file) {
                if (file_exists($file)) {
                    $filesWithTime[$file] = filemtime($file);
                }
            }

            arsort($filesWithTime);
            $topFiles = array_slice(array_keys($filesWithTime), 0, getTaskCountLimit());

            foreach ($topFiles as $file) {
                generateFileCard($file);
            }
            ?>
        </div>

        <div class="mt-6 mb-6 text-center">
            <p class="text-gray-600 m-0 mb-4 text-sm md:text-base">
                编辑下方数字，可设置显示任务数量 
                <!--<span id="checkTime" class="font-medium text-sm md:text-base"><?php echo date('Y-m-d H:i:s'); ?></span>-->
                
            </p>

            <button id="tongzhimoshi"
                class="px-6 py-2 text-white rounded-xl transition-colors text-base font-semibold shadow-md ml-4">
            </button>
            <button id="refreshBtn"
                class="px-6 py-2 bg-sky-500 text-white rounded-xl transition-colors text-base font-semibold shadow-md">
                <i class="fa fa-refresh mr-2"></i> 刷新数据
            </button>

            <div class="mt-4 text-center mx-auto">
            <input type="number" id="taskCountDisplay" 
                class="w-16 px-2 py-1 mx-auto text-center border rounded" 
                value="<?php echo getTaskCountLimit(); ?>">
            </div>

        </div>
    </div>

<script>
let isLoopNotification = initialNotifyMode === 'loop';
const notifyButton = document.getElementById('tongzhimoshi');

function updateNotifyStyle() {
    notifyButton.classList.remove('bg-sky-500', 'bg-gray-500');
    notifyButton.classList.add(isLoopNotification ? 'bg-sky-500' : 'bg-gray-500');
    notifyButton.innerHTML = isLoopNotification
        ? `<i class="fa fa-repeat mr-2"></i>循环通知`
        : `<i class="fa fa-pause-circle mr-2"></i>单次通知`;
}
updateNotifyStyle();

notifyButton.addEventListener('click', () => {
    isLoopNotification = !isLoopNotification;
    updateNotifyStyle();
    fetch('config.php?set_mode=1', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({mode: isLoopNotification ? 'loop' : 'once'})
    });
});

document.getElementById('refreshBtn').addEventListener('click', function () {
    location.reload();
});


// 修改任务显示数量并提交保存（延迟到失去焦点时刷新）
const taskCountInput = document.getElementById('taskCountDisplay');
function sanitizeAndSaveInput() {
    let val = parseInt(taskCountInput.value);
    if (isNaN(val)) return;

    val = Math.max(1, Math.min(val, 20)); // 限制范围在 1 到 20
    taskCountInput.value = val;

    fetch('config.php?set_limit=1', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ limit: val })
    }).then(() => location.reload());
}
// 限制输入合法性（即刻反馈，但不刷新）
taskCountInput.addEventListener('input', () => {
    let val = parseInt(taskCountInput.value);
    if (isNaN(val)) taskCountInput.value = '';
    else taskCountInput.value = Math.min(20, Math.max(1, val));
});
// 离开输入框时提交并刷新
taskCountInput.addEventListener('blur', sanitizeAndSaveInput);
// 回车键也提交
taskCountInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        taskCountInput.blur(); // 触发 blur，避免重复写刷新逻辑
    }
});


var initialFileTimes = <?php
    $fileTimes = [];
    foreach ($filesToCheck as $file) {
        if (file_exists($file)) {
            $fileTimes[$file] = date('Y-m-d H:i:s', filemtime($file));
        } else {
            $fileTimes[$file] = null;
        }
    }
    echo json_encode($fileTimes);
?>;

document.addEventListener('DOMContentLoaded', function() {
    const storedTimes = localStorage.getItem('fileLastModifiedTimes');
    if (!storedTimes) {
        localStorage.setItem('fileLastModifiedTimes', JSON.stringify(initialFileTimes));
    }
});

function checkFileModifications() {
    const storedTimes = JSON.parse(localStorage.getItem('fileLastModifiedTimes')) || {};
    const audio = window.notificationAudio ||= new Audio('/通知.mp3');
    audio.loop = isLoopNotification;

    Object.keys(storedTimes).forEach(filePath => {
        fetch(`get_modified_time.php?file=${encodeURIComponent(filePath)}`)
            .then(res => res.text())
            .then(currentTime => {
                if (currentTime !== storedTimes[filePath]) {
                    if (audio.paused) {
                        audio.currentTime = 0;
                        audio.play().catch(err => console.error('音频播放失败:', err));
                    }
                    storedTimes[filePath] = currentTime;
                    localStorage.setItem('fileLastModifiedTimes', JSON.stringify(storedTimes));
                }
            });
    });
}

function executeAndSchedule() {
    checkFileModifications();
    setTimeout(executeAndSchedule, 3000);
}
executeAndSchedule();
</script>
</body>
</html>
