<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <title>Rando 工具主页</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 40px 20px;
      font-family: 'Poppins', sans-serif;
      background-color: #0f111a;
      color: #e0e0e0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      min-height: 100vh;
      box-sizing: border-box;
    }

    h1 {
      font-size: 2.5em;
      margin-bottom: 30px;
      margin-top: 40px;
      color: #4fc3f7;
      text-shadow: 1px 1px 3px rgba(0, 255, 255, 0.2);
      text-align: center;
    }

    ul {
      list-style: none;
      padding: 0;
      margin: 0;
      width: 100%;
      max-width: 400px;
    }

    li {
      margin: 20px 0;
      text-align: center;
    }

    a {
      text-decoration: none;
    }

    button {
      background: #4fc3f7;
      color: #0f111a;
      border: none;
      padding: 14px 28px;
      font-size: 1.1em;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      box-shadow: 0 4px 10px rgba(0, 255, 255, 0.2);
    }

    button:hover {
      background: #29b6f6;
      transform: translateY(-2px);
    }

    @media (max-width: 600px) {
      h1 {
        font-size: 2em;
      }

      button {
        font-size: 1em;
        padding: 12px 20px;
      }
    }
  </style>
</head>
<body>
  <h1>Rando 工具集</h1>
  <ul>
    <li><a href="tools/sub_to_node.php"><button>解析订阅链接</button></a></li>
    <li><a href="tools/node_to_outbound.php"><button>节点转 Outbound JSON</button></a></li>
    <li><a href="tools/outbound_node_route.php"><button>Outbound 路由生成</button></a></li>
    <li><a href="tools/xrayr_node_config.php"><button>XrayR Config 生成</button></a></li>
  </ul>
</body>
</html>
