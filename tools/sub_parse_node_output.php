<?php

// 兼容 PHP < 8.1 的 array_is_list() 实现
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool {
        $i = 0;
        foreach ($array as $k => $_) {
            if ($k !== $i++) return false;
        }
        return true;
    }
}

function decode_subscription($url) {
    $raw = @file_get_contents($url);
    if (!$raw) return [];

    $decoded = base64_decode($raw);
    $lines = explode("\n", $decoded);

    $nodes = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (!$line) continue;

        if (strpos($line, 'ss://') === 0) {
            $nodes[] = parse_ss($line);
        } elseif (strpos($line, 'trojan://') === 0) {
            $nodes[] = parse_trojan($line);
        } elseif (strpos($line, 'vmess://') === 0) {
            $nodes[] = parse_vmess($line);
        } elseif (strpos($line, 'vless://') === 0) {
            $nodes[] = parse_vless($line);
        } elseif (strpos($line, 'hysteria2://') === 0 || strpos($line, 'hy2://') === 0) {
            $nodes[] = parse_hysteria($line);
        }
    }

    return array_filter($nodes);
}

// --- Protocol parsers ---

function parse_ss($url) {
    $tag = '';
    if (strpos($url, '#') !== false) {
        [$url, $tag] = explode('#', $url, 2);
        $tag = urldecode($tag);
    }

    $url = substr($url, 5); // remove ss://

    // detect new format ss://base64(method:passwd@host:port)
    if (strpos($url, '@') === false) {
        $decoded = base64_decode($url);
        if (!$decoded) return null;
        [$method_pass, $server_port] = explode('@', $decoded);
        [$method, $password] = explode(':', $method_pass);
        [$server, $port] = explode(':', $server_port);
    } else {
        $parts = parse_url("ss://$url");
        $server = $parts['host'];
        $port = $parts['port'];
        $userpass = base64_decode($parts['user']);
        [$method, $password] = explode(':', $userpass);
    }

    return [
        'name' => $tag ?: "{$server}:{$port}",
        'type' => 'ss',
        'server' => $server,
        'port' => (int)$port,
        'cipher' => $method,
        'password' => $password,
        'udp' => true,
    ];
}

function parse_trojan($url) {
    $tag = '';
    if (strpos($url, '#') !== false) {
        [$url, $tag] = explode('#', $url, 2);
        $tag = urldecode($tag);
    }

    $parts = parse_url($url);
    if (!$parts) return null;

    return [
        'name' => $tag ?: $parts['host'],
        'type' => 'trojan',
        'server' => $parts['host'],
        'port' => (int)($parts['port'] ?? 443),
        'password' => $parts['user'],
        'udp' => true,
        'skip-cert-verify' => true
    ];
}

function parse_vmess($url) {
    $json = json_decode(base64_decode(substr($url, 8)), true);
    if (!$json) return null;

    return [
        'name' => $json['ps'] ?? $json['add'],
        'type' => 'vmess',
        'server' => $json['add'],
        'port' => (int)$json['port'],
        'uuid' => $json['id'],
        'alterId' => (int)($json['aid'] ?? 0),
        'cipher' => 'auto',
        'udp' => true,
        'network' => $json['net'],
        'ws-opts' => [
            'path' => $json['path'] ?? '/',
            'headers' => [ 'Host' => $json['host'] ?? $json['add'] ]
        ]
    ];
}

function parse_vless($url) {
    $parts = parse_url($url);
    if (!$parts) return null;

    parse_str($parts['query'] ?? '', $query);

    return [
        'name' => urldecode($parts['fragment'] ?? $parts['host']),
        'type' => 'vless',
        'server' => $parts['host'],
        'port' => (int)($parts['port'] ?? 443),
        'uuid' => $parts['user'],
        'udp' => true,
        'network' => $query['type'] ?? 'ws',
    ];
}

function parse_hysteria($url) {
    $parts = parse_url($url);
    if (!$parts || empty($parts['host'])) return null;

    parse_str($parts['query'] ?? '', $query);

    return [
        'name' => urldecode($parts['fragment'] ?? 'Unnamed'),
        'type' => 'hysteria2',
        'server' => $parts['host'],
        'port' => isset($parts['port']) ? (int)$parts['port'] : 443,
        'password' => $parts['user'] ?? '',                         // 主密码
        'obfs' => $query['obfs'] ?? '',                             // 混淆类型
        'obfs-password' => $query['obfs-password'] ?? '',          // 混淆密码
        'udp' => true,
        'sni' => $query['sni'] ?? '',
        'skip-cert-verify' => ($query['insecure'] ?? '0') === '1'
    ];
}



// --- Output formatting ---

function format_yaml(array $nodes): string {
    $lines = ["proxies:"];
    foreach ($nodes as $node) {
        $parts = [];
        foreach ($node as $key => $value) {
            $parts[] = format_yaml_kv($key, $value);
        }
        $lines[] = "- { " . implode(', ', $parts) . " }";
    }
    return implode("\n", $lines);
}

function format_yaml_kv($key, $value): string {
    if (is_array($value)) {
        $innerParts = [];
        foreach ($value as $k => $v) {
            $innerParts[] = format_yaml_kv($k, $v);
        }
        return "$key: { " . implode(', ', $innerParts) . " }";
    } elseif (is_bool($value)) {
        return "$key: " . ($value ? "true" : "false");
    } elseif (is_numeric($value)) {
        return "$key: $value";
    } else {
        // 检查是否需要加引号（包含空格、冒号、特殊字符等）
        $needsQuotes = preg_match('/[^a-zA-Z0-9._-]/', $value);
        $safeValue = addslashes($value);
        return $needsQuotes ? "$key: '$safeValue'" : "$key: $safeValue";
    }
}




// --- Main execution ---

$proxies = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = $_POST['url'];
    $proxies = decode_subscription($url);
}

?>
<?php
// 保持原有 PHP 逻辑不变
// ...（你的 PHP 代码保留不变）...
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>节点解析结果 - Random Cloud</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 40px 20px;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #a0f0ed, #81d8d0);
      color: #00332e;
      min-height: 100vh;
      box-sizing: border-box;
    }

    h2 {
      font-size: 1.8em;
      margin-top: 40px;
      margin-bottom: 10px;
      text-align: center;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    .container {
      max-width: 1000px;
      margin: 0 auto;
    }

    textarea {
      width: 100%;
      height: 300px;
      padding: 12px;
      font-size: 14px;
      font-family: monospace;
      border: 2px solid #81d8d0;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 10px;
      resize: vertical;
    }

    button {
      display: block;
      width: 200px;
      margin: 10px auto 40px auto;
      padding: 12px;
      font-size: 1em;
      background: linear-gradient(90deg, #81d8d0, #7ec9f9);
      border: none;
      border-radius: 8px;
      color: #00332e;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    button:hover {
      background: linear-gradient(90deg, #7ec9f9, #81d8d0);
      transform: translateY(-2px);
    }

    @media (max-width: 600px) {
      textarea { height: 250px; font-size: 13px; }
      button { width: 100%; }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>节点解析结果（Clash JSON 格式）</h2>
    <textarea id="json"><?php echo htmlspecialchars(json_encode(['proxies' => $proxies], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></textarea>
    <button onclick="copyText('json')">复制 JSON</button>

    <h2>节点解析结果（Clash YAML 格式）</h2>
    <textarea id="yaml"><?php echo htmlspecialchars(format_yaml($proxies)); ?></textarea>
    <button onclick="copyText('yaml')">复制 YAML</button>
  </div>

  <script>
    function copyText(id) {
      const textarea = document.getElementById(id);
      textarea.select();
      document.execCommand('copy');
      alert("已复制！");
    }
  </script>
</body>
</html>
