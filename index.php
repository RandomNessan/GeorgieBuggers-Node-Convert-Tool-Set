<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <title>Random Cloud 工具主页</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #a0f0ed, #81d8d0);
      color: #00332e;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    h1 {
      font-size: 2.5em;
      margin-bottom: 30px;
      color: #004c46;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }

    ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    li {
      margin: 15px 0;
    }

    a {
      text-decoration: none;
    }

    button {
      background: linear-gradient(90deg, #81d8d0, #7ec9f9);
      color: #00332e;
      border: none;
      padding: 15px 30px;
      font-size: 1.1em;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    button:hover {
      background: linear-gradient(90deg, #7ec9f9, #81d8d0);
      transform: translateY(-2px);
    }

    @media (max-width: 600px) {
      h1 {
        font-size: 1.8em;
      }

      button {
        width: 90%;
        font-size: 1em;
      }
    }
  </style>
</head>
<body>
  <h1>Random Cloud 工具集</h1>
  <ul>
    <li><a href="tools/sub_to_node.php"><button>解析订阅链接</button></a></li>
    <li><a href="tools/node_to_outbound.php"><button>节点转 Outbound JSON</button></a></li>
    <li><a href="tools/outbound_node_route.php"><button>Outbound 路由生成</button></a></li>
  </ul>
</body>
</html>
