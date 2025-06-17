<?php // xrayr_node_config.php ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>XrayR 节点配置生成器</title>
  <style>
    body {
      background-color: #0f111a;
      color: #e0e0e0;
      font-family: "Segoe UI", Roboto, sans-serif;
      margin: 0;
      padding: 0 20px;
    }
    h2 {
      text-align: center;
      padding: 20px 0;
      color: #4fc3f7;
    }
    button {
      background-color: #4fc3f7;
      border: none;
      padding: 10px 18px;
      margin: 10px 5px;
      color: #000;
      font-weight: bold;
      cursor: pointer;
      border-radius: 6px;
      transition: background-color 0.3s;
    }
    button:hover {
      background-color: #29b6f6;
    }
    #nodeList div {
      background: #1c1f2b;
      border-left: 4px solid #4fc3f7;
      padding: 10px;
      margin: 10px 0;
      border-radius: 4px;
    }
    textarea {
      width: 100%;
      height: 400px;
      background: #1e1e2e;
      color: #00e676;
      border: 1px solid #555;
      padding: 10px;
      font-family: monospace;
      font-size: 14px;
      border-radius: 4px;
    }
    /* 模拟弹窗 */
    #modalOverlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: 999;
    }
    #modal {
      width: 600px;
      max-height: 80vh;
      overflow-y: auto;
      background: #ffffff;
      color: #000;
      padding: 20px;
      margin: 60px auto;
      border-radius: 8px;
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }
    #modal h3 {
      margin-top: 0;
    }
    #modal label {
      display: block;
      margin: 8px 0 4px;
      font-weight: bold;
      color: #222;
    }
    #modal input {
      width: 100%;
      padding: 8px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
    }
    #modal button {
      margin-top: 10px;
      background: #1976d2;
      color: #fff;
    }
    #modal button:hover {
      background: #0d47a1;
    }
  </style>
</head>
<body>
  <h2>XrayR 节点配置生成器</h2>
  <div style="text-align:center;">
    <button onclick="openModal()">添加节点</button>
    <button onclick="generateYaml()">开始生成</button>
    <button onclick="copyOutput()">复制输出内容</button>
  </div>

  <div id="nodeList"></div>

  <h3>生成的 config.yml 内容：</h3>
  <textarea id="yamlOutput" readonly></textarea>

  <!-- 模拟弹窗结构 -->
  <div id="modalOverlay">
    <div id="modal">
      <h3>填写节点信息</h3>
      <form id="routeForm" method="POST">
        <input type="hidden" name="hiddenNode" id="hiddenNode">
        <div id="formContainer"></div>
        <button type="button" onclick="saveNode()">保存</button>
        <button type="button" onclick="closeModal()">取消</button>
      </form>
    </div>
  </div>
</body>

<script>
  const nodeData = [];

  // 字段定义
const fields = [
  ["PanelType", '默认: NewV2board，可选: SSpanel, NewV2board, PMpanel, Proxypanel, V2RaySocks, GoV2Panel', 'NewV2board'],
  ["ApiHost", '默认: https://rrr.sss.com，接口地址', 'https://rrr.sss.com'],
  ["ApiKey", '默认: 123，接口密钥', '123'],
  ["NodeID", '默认: 1，节点ID', '1'],
  ["NodeType", '默认: Shadowsocks，可选: V2ray, Vmess, Vless, Shadowsocks, Trojan, Shadowsocks-Plugin', 'Shadowsocks'],
  ["Timeout", '默认: 30，单位秒', '30'],
  ["EnableVless", '默认: false，是否启用 Vless (true/false)', 'false'],
  ["SpeedLimit", '默认: 0，Mbps，0 表示不限制', '0'],
  ["DeviceLimit", '默认: 0，0 表示不限制', '0'],
  ["RuleListPath", '默认空，例: /etc/XrayR/rulelist', ''],
  ["DisableCustomConfig", '默认: false，是否禁用自定义配置', 'false'],

  ["ListenIP", '默认: 0.0.0.0，监听地址', '0.0.0.0'],
  ["SendIP", '默认: 0.0.0.0，发送地址', '0.0.0.0'],
  ["UpdatePeriodic", '默认: 60，单位秒', '60'],
  ["DeviceOnlineMinTraffic", '默认: 100，单位 KB', '100'],
  ["EnableDNS", '默认: false，是否启用自定义 DNS', 'false'],
  ["DNSType", '默认: AsIs，可选: AsIs, UseIP, UseIPv4, UseIPv6', 'AsIs'],
  ["EnableProxyProtocol", '默认: false，仅 WebSocket/TCP 有效', 'false'],

  ["ASLimit", '默认: 0，单位 Mbps', '0'],
  ["ASWarnTimes", '默认: 0，连续超速警告次数', '0'],
  ["ASLimitSpeed", '默认: 0，限速后 Mbps', '0'],
  ["ASLimitDuration", '默认: 0，分钟', '0'],

  ["GDLEnable", '默认: false，是否启用设备总限制', 'false'],
  ["RedisNetwork", '默认: tcp，tcp 或 unix', 'tcp'],
  ["RedisAddr", '默认: 127.0.0.1:6379', '127.0.0.1:6379'],
  ["RedisUsername", '默认空', ''],
  ["RedisPassword", '默认: YOUR PASSWORD', 'YOUR PASSWORD'],
  ["RedisDB", '默认: 0', '0'],
  ["RedisTimeout", '默认: 5', '5'],
  ["RedisExpiry", '默认: 60', '60'],

  ["EnableFallback", '默认: false', 'false'],
  ["FallbackSNI", '默认空', ''],
  ["FallbackAlpn", '默认空', ''],
  ["FallbackPath", '默认空', ''],
  ["FallbackDest", '默认: 80', '80'],
  ["FallbackProxyVer", '默认: 0', '0'],

  ["EnableREALITY", '默认: false', 'false'],
  ["DisableLocalREALITYConfig", '默认: false', 'false'],
  ["REALITYShow", '默认: false', 'false'],
  ["REALITYDest", '默认: m.media-amazon.com:443', 'm.media-amazon.com:443'],
  ["REALITYProxyVer", '默认: 0', '0'],
  ["REALITYServerNames", '默认: m.media-amazon.com，多个用逗号', 'm.media-amazon.com'],
  ["REALITYPrivateKey", '可选', ''],
  ["REALITYMinVer", '可选，x.y.z', ''],
  ["REALITYMaxVer", '可选，x.y.z', ''],
  ["REALITYMaxDiff", '默认: 0，毫秒', '0'],
  ["REALITYShortIds", '默认: ""，多个用逗号', '""'],

  ["CertMode", '默认: none，可选: none, file, http, tls, dns', 'none'],
  ["CertDomain", '默认: node1.test.com', 'node1.test.com'],
  ["CertFile", '默认: /etc/XrayR/cert/node1.test.com.cert', '/etc/XrayR/cert/node1.test.com.cert'],
  ["KeyFile", '默认: /etc/XrayR/cert/node1.test.com.key', '/etc/XrayR/cert/node1.test.com.key'],
  ["CertProvider", '默认: alidns', 'alidns'],
  ["CertEmail", '默认: test@me.com', 'test@me.com'],
  ["CertAccessKey", '默认: aaa', 'aaa'],
  ["CertSecretKey", '默认: bbb', 'bbb']
];


  // 初始化表单
function openModal() {
  const container = document.getElementById('formContainer');
  container.innerHTML = '';
  fields.forEach(([id, tip]) => {
    const label = document.createElement('label');
    label.innerHTML = `${id}: <input id="${id}" placeholder="${tip}"><br>`;
    container.appendChild(label);
  });
  document.getElementById('modalOverlay').style.display = 'block';
}

function closeModal() {
  document.getElementById('modalOverlay').style.display = 'none';
}


function saveNode() {
  const node = {};
  fields.forEach(([id, tip, defaultVal]) => {
    const input = document.getElementById(id);
    const value = input ? input.value.trim() : '';
    node[id] = value !== '' ? value : defaultVal;
  });
  nodeData.push(node);
  renderNodes();
  closeModal();
}

function copyOutput() {
  const textarea = document.getElementById('yamlOutput');
  textarea.select();
  textarea.setSelectionRange(0, 99999); // 兼容移动端
  document.execCommand("copy");
  alert("内容已复制到剪贴板！");
}


  function renderNodes() {
    const list = document.getElementById('nodeList');
    list.innerHTML = '';
    nodeData.forEach((n, i) => {
      const div = document.createElement('div');
      div.innerHTML = `节点 ${i + 1}：${n.PanelType} - ${n.ApiHost} 
        <button onclick="deleteNode(${i})">删除</button><br>`;
      list.appendChild(div);
    });
  }

  function deleteNode(i) {
    nodeData.splice(i, 1);
    renderNodes();
  }

  function generateYaml() {
    if (nodeData.length === 0) return;
    const yaml = nodeData.map(n => {
      const sn = s => s.trim() || '""';
      const list = (val, indent = '          - ') =>
        (val || '').split(',').map(v => indent + sn(v)).join('\n');

      return `  - PanelType: "${n.PanelType}" # Panel type: SSpanel, NewV2board, PMpanel, Proxypanel, V2RaySocks, GoV2Panel
    ApiConfig:
      ApiHost: "${n.ApiHost}"
      ApiKey: "${n.ApiKey}"
      NodeID: ${n.NodeID}
      NodeType: ${n.NodeType} # Node type: V2ray, Vmess, Vless, Shadowsocks, Trojan, Shadowsocks-Plugin
      Timeout: ${n.Timeout} # Timeout for the api request
      EnableVless: ${n.EnableVless} # Enable Vless for V2ray Type
      SpeedLimit: ${n.SpeedLimit} # Mbps, Local settings will replace remote settings, 0 means disable
      DeviceLimit: ${n.DeviceLimit} # Local settings will replace remote settings, 0 means disable
      RuleListPath: ${n.RuleListPath} # /etc/XrayR/rulelist Path to local rulelist file
      DisableCustomConfig: ${n.DisableCustomConfig} # disable custom config for sspanel
    ControllerConfig:
      ListenIP: ${n.ListenIP} # IP address you want to listen
      SendIP: ${n.SendIP} # IP address you want to send pacakage
      UpdatePeriodic: ${n.UpdatePeriodic} # Time to update the nodeinfo, how many sec.
      DeviceOnlineMinTraffic: ${n.DeviceOnlineMinTraffic} # V2board面板设备数限制统计阈值，大于此流量时上报设备数在线，单位kB，不填则默认上报
      EnableDNS: ${n.EnableDNS} # Use custom DNS config, Please ensure that you set the dns.json well
      DNSType: ${n.DNSType} # AsIs, UseIP, UseIPv4, UseIPv6, DNS strategy
      EnableProxyProtocol: ${n.EnableProxyProtocol} # Only works for WebSocket and TCP
      AutoSpeedLimitConfig:
        Limit: ${n.ASLimit} # Warned speed. Set to 0 to disable AutoSpeedLimit (mbps)
        WarnTimes: ${n.ASWarnTimes} # After (WarnTimes) consecutive warnings, the user will be limited. Set to 0 to punish overspeed user immediately.
        LimitSpeed: ${n.ASLimitSpeed} # The speedlimit of a limited user (unit: mbps)
        LimitDuration: ${n.ASLimitDuration} # How many minutes will the limiting last (unit: minute)
      GlobalDeviceLimitConfig:
        Enable: ${n.GDLEnable} # Enable the global device limit of a user
        RedisNetwork: ${n.RedisNetwork} # Redis protocol, tcp or unix
        RedisAddr: ${n.RedisAddr} # Redis server address, or unix socket path
        RedisUsername: ${n.RedisUsername} # Redis username
        RedisPassword: ${n.RedisPassword} # Redis password
        RedisDB: ${n.RedisDB} # Redis DB
        Timeout: ${n.RedisTimeout} # Timeout for redis request
        Expiry: ${n.RedisExpiry} # Expiry time (second)
      EnableFallback: ${n.EnableFallback} # Only support for Trojan and Vless
      FallBackConfigs:  # Support multiple fallbacks
        - SNI: ${n.FallbackSNI} # TLS SNI(Server Name Indication), Empty for any
          Alpn: ${n.FallbackAlpn} # Alpn, Empty for any
          Path: ${n.FallbackPath} # HTTP PATH, Empty for any
          Dest: ${n.FallbackDest} # Required, Destination of fallback, check https://xtls.github.io/config/features/fallback.html for details.
          ProxyProtocolVer: ${n.FallbackProxyVer} # Send PROXY protocol version, 0 for disable
      EnableREALITY: ${n.EnableREALITY} # 是否开启 REALITY
      DisableLocalREALITYConfig: ${n.DisableLocalREALITYConfig}  # 是否忽略本地 REALITY 配置
      REALITYConfigs: # 本地 REALITY 配置
        Show: ${n.REALITYShow} # Show REALITY debug
        Dest: ${n.REALITYDest} # REALITY 目标地址
        ProxyProtocolVer: ${n.REALITYProxyVer} # Send PROXY protocol version, 0 for disable
        ServerNames: # Required, list of available serverNames for the client, * wildcard is not supported at the moment.
${list(n.REALITYServerNames)}
        PrivateKey: ${n.REALITYPrivateKey} # 可不填
        MinClientVer: ${n.REALITYMinVer} # Optional, minimum version of Xray client, format is x.y.z.
        MaxClientVer: ${n.REALITYMaxVer} # Optional, maximum version of Xray client, format is x.y.z.
        MaxTimeDiff: ${n.REALITYMaxDiff} # Optional, maximum allowed time difference, unit is in milliseconds.
        ShortIds: # 可不填
${list(n.REALITYShortIds)}
      CertConfig:
        CertMode: ${n.CertMode} # Option about how to get certificate: none, file, http, tls, dns. Choose "none" will forcedly disable the tls config.
        CertDomain: "${n.CertDomain}" # Domain to cert
        CertFile: ${n.CertFile} # Provided if the CertMode is file
        KeyFile: ${n.KeyFile}
        Provider: ${n.CertProvider} # DNS cert provider, Get the full support list here: https://go-acme.github.io/lego/dns/
        Email: ${n.CertEmail}
        DNSEnv: # DNS ENV option used by DNS provider
          ALICLOUD_ACCESS_KEY: ${n.CertAccessKey}
          ALICLOUD_SECRET_KEY: ${n.CertSecretKey}`;
    }).join('\n\n');
    document.getElementById('yamlOutput').value = 'Nodes:\n' + yaml;
  }
</script>
</body>
</html>
