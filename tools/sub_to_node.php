<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>订阅链接解析器</title>
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

    h2 {
      font-size: 2em;
      margin-bottom: 30px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 90%;
      max-width: 800px;
    }

    input[type="text"] {
      width: 100%;
      padding: 12px;
      font-size: 1em;
      border: 2px solid #81d8d0;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    input[type="submit"] {
      margin-top: 20px;
      padding: 12px 30px;
      font-size: 1.1em;
      border: none;
      border-radius: 8px;
      background: linear-gradient(90deg, #81d8d0, #7ec9f9);
      color: #00332e;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s ease;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    input[type="submit"]:hover {
      background: linear-gradient(90deg, #7ec9f9, #81d8d0);
      transform: translateY(-2px);
    }

    @media (max-width: 600px) {
      h2 {
        font-size: 1.5em;
      }

      input[type="text"] {
        font-size: 0.9em;
      }

      input[type="submit"] {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <h2>请输入订阅链接：</h2>
  <form action="sub_parse_node_output.php" method="post">
    <input type="text" name="url" placeholder="https://example.com/subscribe?token=xxxxx" required>
    <input type="submit" value="解析节点信息">
  </form>
</body>
</html>
