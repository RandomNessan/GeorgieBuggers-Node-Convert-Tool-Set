<?php
$routeJson = '';
$rawJson = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawJson = $_POST['custom_outbound'] ?? '';
    $inboundPorts = $_POST['inbound_ports'] ?? [];

    $decoded = json_decode($rawJson, true);
    $rules = [];

    foreach ($decoded as $entry) {
        if (
            isset($entry['tag']) &&
            isset($entry['protocol']) &&
            isset($inboundPorts[$entry['tag']])
        ) {
            $port = intval($inboundPorts[$entry['tag']]);
            $protoCap = ucfirst(strtolower($entry['protocol']));
            $rules[] = [
                'type' => 'field',
                'inboundTag' => ["{$protoCap}_0.0.0.0_$port"],
                'outboundTag' => $entry['tag']
            ];
        }
    }

    $routeJson = json_encode([
        'domainStrategy' => 'IPOnDemand',
        'rules' => $rules
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>custom_outbound 转 route 配置生成</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 40px 20px;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #a0f0ed, #81d8d0);
            color: #00332e;
            box-sizing: border-box;
        }

        h2, h3 {
            text-align: center;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        textarea {
            width: 100%;
            height: 250px;
            padding: 12px;
            font-size: 14px;
            font-family: monospace;
            border: 2px solid #81d8d0;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            resize: vertical;
            background: #ffffffcc;
        }

        button {
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            color: #00332e;
            background: linear-gradient(90deg, #81d8d0, #7ec9f9);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            margin: 10px auto;
            display: block;
        }

        button:hover {
            background: linear-gradient(90deg, #7ec9f9, #81d8d0);
            transform: translateY(-2px);
        }

        .port-input {
            width: 100px;
            margin-bottom: 10px;
        }

        #modalOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
        }

        #modal {
            background: white;
            width: 500px;
            max-width: 90%;
            margin: 10% auto;
            padding: 25px;
            border-radius: 10px;
            position: relative;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        #modal h3 {
            margin-top: 0;
        }

        #modal label {
            display: inline-block;
            width: 240px;
            margin-top: 8px;
        }

        @media (max-width: 600px) {
            textarea { height: 200px; }
            button { width: 100%; }
            #modal { width: 95%; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>输入 custom_outbound.json 内容</h2>
    <textarea id="custom_outbound" name="custom_outbound" placeholder="粘贴你的 custom_outbound.json 内容"><?= htmlspecialchars($rawJson) ?></textarea>
    <button onclick="parseTags()">解析节点</button>

    <!-- 模拟弹窗 -->
    <div id="modalOverlay">
        <div id="modal">
            <h3>请为每个节点填写入站端口：</h3>
            <form method="POST" id="routeForm">
                <input type="hidden" name="custom_outbound" id="hiddenOutbound">
                <div id="portInputs"></div>
                <button type="button" onclick="confirmInput()">确认</button>
                <button type="button" onclick="closeModal()">取消</button>
            </form>
        </div>
    </div>

    <?php if ($routeJson): ?>
        <h3>生成的 route.json 配置</h3>
        <textarea id="routeJsonOutput"><?= htmlspecialchars($routeJson) ?></textarea>
        <button onclick="copyToClipboard('routeJsonOutput')">复制 route.json</button>
    <?php endif; ?>
</div>

<script>
    function parseTags() {
        const input = document.getElementById('custom_outbound').value;
        let data;
        try {
            data = JSON.parse(input);
        } catch (e) {
            alert('无效的 JSON 格式');
            return;
        }

        const container = document.getElementById('portInputs');
        container.innerHTML = '';
        let hasTag = false;

        data.forEach(item => {
            if (item.tag && item.protocol) {
                hasTag = true;
                const label = document.createElement('label');
                label.innerText = `请为 [${item.tag}] 入站端口：`;
                const input = document.createElement('input');
                input.type = 'number';
                input.name = `inbound_ports[${item.tag}]`;
                input.className = 'port-input';
                input.required = true;
                container.appendChild(label);
                container.appendChild(input);
                container.appendChild(document.createElement('br'));
            }
        });

        if (hasTag) {
            document.getElementById('modalOverlay').style.display = 'block';
            document.getElementById('hiddenOutbound').value = input;
        } else {
            alert('没有检测到节点 tag');
        }
    }

    function closeModal() {
        document.getElementById('modalOverlay').style.display = 'none';
    }

    function confirmInput() {
        const inputs = document.querySelectorAll('#portInputs input');
        for (let input of inputs) {
            if (!input.value) {
                alert("请填写所有端口");
                return;
            }
        }
        document.getElementById('routeForm').submit();
    }

    function copyToClipboard(id) {
        const ta = document.getElementById(id);
        ta.select();
        ta.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert("已复制到剪贴板");
    }
</script>
</body>
</html>
