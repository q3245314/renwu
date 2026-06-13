<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>供养提示</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: flex-start;
            padding-top: 30vh;
        }
        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            text-align: center;
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-content h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        .modal-content p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 10px 50px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="modal" id="myModal">
        <div class="modal-content">
            <h2>温馨提示</h2>
            <p>此捐款渠道，默认用途为供养三宝，如有特定捐款需求，请到客堂找师父
            <br>
            或添加财务微信：13888888888
            </p>
            <button class="btn" id="confirmBtn">确定</button>
        </div>
    </div>

    <script>
        // 页面加载完成后显示弹窗
        window.onload = function() {
            var modal = document.getElementById('myModal');
            modal.style.display = 'flex';
        };

        // 点击确定按钮后跳转到指定链接
        document.getElementById('confirmBtn').addEventListener('click', function() {
            window.location.href = 'https://qr.95516.com/01052400/CCB997105000889991220Y105000889991220000110282623';
        });
    </script>
</body>
</html>