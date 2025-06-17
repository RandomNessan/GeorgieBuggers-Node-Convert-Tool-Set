<?php
$country_map = json_decode(file_get_contents('assets/json/countries/country_identify.json'), true);

function get_country_tag($name, &$counters, $country_map) {
    foreach ($country_map as $country => $code) {
        if (strpos($name, $country) !== false) {
            if (!isset($counters[$code])) $counters[$code] = 0;
            $counters[$code]++;
            return strtolower($code) . str_pad($counters[$code], 2, '0', STR_PAD_LEFT);
        }
    }
    return 'xx00';
}

function parse_node_line($line, &$counters, $country_map) {
    $line = trim($line);
    if (strpos($line, '- {') !== 0) return null;

    $body = trim(substr($line, 2), '{} ');
    $parts = preg_split('/,\s*(?=[a-z]+\s*:)/', $body);
    $data = [];
    foreach ($parts as $part) {
        [$k, $v] = array_map('trim', explode(':', $part, 2));
        $data[$k] = trim($v, " '\"");
    }
    if (!isset($data['name'], $data['server'], $data['port'])) return null;

    $tag = get_country_tag($data['name'], $counters, $country_map);
    $protocol = $data['type'] ?? 'ss';

    if ($protocol === 'ss') {
        return [
            'tag' => $tag,
            'protocol' => 'shadowsocks',
            'settings' => [
                'servers' => [[
                    'address'  => $data['server'],
                    'method'   => $data['cipher']  ?? 'aes-128-gcm',
                    'password' => $data['password'] ?? '',
                    'port'     => (int)$data['port'],
                ]]
            ]
        ];
    }

    return null;
}

$result = [];
$inputNodes = '';
$fullJson = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nodes'])) {
    $inputNodes = $_POST['nodes'];
    $lines = explode("\n", $inputNodes);
    $counters = [];

    foreach ($lines as $line) {
        $parsed = parse_node_line($line, $counters, $country_map);
        if ($parsed) $result[] = $parsed;
    }

    $base = [
        ['tag' => 'IPv4_out', 'protocol' => 'freedom', 'settings' => new stdClass()],
        ['protocol' => 'blackhole', 'tag' => 'block']
    ];
    $full = array_merge($base, $result);
    $fullJson = json_encode($full, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>节点转 Outbound JSON</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 40px 20px;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #a0f0ed, #81d8d0);
      color: #00332e;
    }

    .container {
      max-width: 1000px;
      margin: auto;
    }

    h2, h3 {
      text-align: center;
      font-weight: 600;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    textarea {
      width: 100%;
      height: 200px;
      padding: 12px;
      font-size: 14px;
      font-family: monospace;
      border: 2px solid #81d8d0;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      background: #ffffffdd;
      resize: vertical;
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

    @media (max-width: 600px) {
      textarea { height: 160px; }
      button { width: 100%; }
    }
  </style>
</head>
<body>
<div class="container">
  <h2>粘贴你的 Clash 节点列表</h2>
  <form method="POST">
    <textarea name="nodes" rows="10" placeholder="- { name: '🇭🇰 香港-01', type: ss, server: hk.cxk.com, port: 1234, cipher: aes-128-gcm, password: example }"><?= htmlspecialchars($inputNodes) ?></textarea>
    <button type="submit">生成 Outbound 节点</button>
  </form>

  <?php if (!empty($result)): ?>
    <h3>解析结果（仅节点数组）</h3>
    <textarea id="nodesJson" rows="10"><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea>
    <button onclick="copyText('nodesJson')">复制节点 JSON</button>

    <h3>完整 custom_outbound.json 文件内容</h3>
    <textarea id="fullJson" rows="20"><?= htmlspecialchars($fullJson) ?></textarea>
    <button onclick="copyText('fullJson')">复制完整配置</button>
  <?php endif; ?>
</div>

<script>
  function copyText(id) {
    const ta = document.getElementById(id);
    ta.select();
    ta.setSelectionRange(0, 99999);
    document.execCommand('copy');
    alert('已复制到剪贴板！');
  }
</script>
</body>
</html>
