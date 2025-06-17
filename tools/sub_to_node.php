<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>订阅链接解析器</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: "Segoe UI", Roboto, sans-serif;
      background-color: #0f111a;
      color: #e0e0e0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    h2 {
      font-size: 2em;
      margin-bottom: 30px;
      color: #4fc3f7;
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 90%;
      max-width: 700px;
      background: #1c1f2b;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 30px rgba(0, 255, 255, 0.1);
    }

    input[type="text"] {
      width: 100%;
      padding: 14px;
      font-size: 16px;
      border: 1px solid #4fc3f7;
      border-radius: 6px;
      background: #10131e;
      color: #00e676;
      outline: none;
      margin-bottom: 20px;
    }

    input[type="submit"] {
      background-color: #4fc3f7;
      color: #000;
      padding: 12px 30px;
      font-size: 1em;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    input[type="submit"]:hover {
      background-color: #29b6f6;
    }

    @media (max-width: 600px) {
      h2 {
        font-size: 1.5em;
      }
      form {
        padding: 20px;
      }
      input[type="text"] {
        font-size: 0.95em;
      }
      input[type="submit"] {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <h2>请输入订阅链接</h2>
  <form action="sub_parse_node_output.php" method="post">
    <input type="text" name="url" placeholder="https://example.com/subscribe?token=xxxxx" required>
    <input type="submit" value="解析节点信息">
  </form>
</body>
</html>
