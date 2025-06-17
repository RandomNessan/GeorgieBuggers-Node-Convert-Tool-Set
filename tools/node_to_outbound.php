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
  <title>节点转 Outbound JSON - Random Cloud</title>
  <style>
    body {
      margin: 0;
      padding: 40px 20px;
      font-family: "Segoe UI", Roboto, sans-serif;
      background-color: #0f111a;
      color: #e0e0e0;
      min-height: 100vh;
      box-sizing: border-box;
    }

    h2, h3 {
      font-size: 1.6em;
      margin: 30px 0 15px;
      text-align: center;
      color: #4fc3f7;
    }

    .container {
      max-width: 1000px;
      margin: 0 auto;
    }

    textarea {
      width: 100%;
      padding: 14px;
      font-size: 14px;
      font-family: monospace;
      background-color: #10131e;
      color: #00e676;
      border: 1px solid #4fc3f7;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 255, 255, 0.1);
      margin-bottom: 20px;
      resize: vertical;
    }

    button {
      display: block;
      width: 220px;
      margin: 12px auto 30px;
      padding: 12px;
      font-size: 1em;
      background-color: #4fc3f7;
      color: #000;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    button:hover {
      background-color: #29b6f6;
    }

    @media (max-width: 600px) {
      textarea { font-size: 13px; height: 180px; }
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
