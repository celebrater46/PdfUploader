<?php

session_start(); // セッション宣言

ini_set('display_errors', 1); // エラーメッセージ出力（1で表示）
define('MAX_FILE_SIZE', 1 * 1024 * 1024); // ファイルサイズ制限（1MB）
define('FILES_DIR', __DIR__ . '/files'); // PDFファイルのディレクトリ（__DIR__ は現在のディレクトリ取得）


// さまざまな表示のためのエスケープ
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

require "uploader.php";

$uploader = new \PdfUploader\Uploader();

if ($_SERVER["REQUEST_METHOD"] === "POST") { // 定義済み変数。投稿、送信が行われたらの処理
    $uploader->upload();
}

list($success, $error) = $uploader->getResults();
$pdfFiles = $uploader->getPdfFiles();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>PDFアップローダー</title>
  <style>
    body {
      text-align: center;
      font-family: Arial, Helvetica, sans-serif;
    }

    ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    li {
      margin-bottom: 5px;
    }

    input[type=file] {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
      opacity: 0;
    }

    .btn {
      position: relative;
      display: inline-block;
      width: 300px;
      padding: 7px;
      border-radius: 5px;
      margin: 10px auto 20px;
      color: #fff;
      box-shadow: 0 4px #08c;
      background: #0af;
    }

    .btn:hover {
      opacity: 0.8;
    }

    .msg {
      margin: 10px auto 20px;
      width: 400px;
      font-weight: bold;
    }

    .msg.success {
      color: #4caf50;
    }

    .msg.error {
      color: #f44336;
    }
  </style>
</head>
<body>
  <div class="btn">
    PDFをアップロードします
    <form action="" method="post" enctype="multipart/form-data" id="my-form">
      <!-- ファイルの最大サイズの指定 -->
      <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo h(MAX_FILE_SIZE); ?>">
      <input type="file" name="pdf" id="my-file">
      <!-- <input type="submit" value="upload"> -->
    </form>
  </div>

  <?php if(isset($success)) : ?>
    <div class="msg success"><?php echo h($success); ?></div>
  <?php endif; ?>
  <?php if(isset($error)) : ?>
    <div class="msg error"><?php echo h($error); ?></div>
  <?php endif; ?>

  <ul>
    <?php foreach ($pdfFiles as $pdfFile) : ?>
      <li>
        <a href="<?php echo h(basename(FILES_DIR)) . "/" . h(basename($pdfFile)); // basename() はパスからファイル名を取得 ?>">
          <p><?php echo h($pdfFile); ?></p>
        </a>
      </li>  
      <?php endforeach; ?> 
  </ul>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script>
    $(function() {
      $(".msg").fadeOut(3000);
      $("#my-file").on("change", function() { // ファイルが変更されたら自動的に submit
        $("#my-form").submit();
      });
    });
  </script>
</body>
</html>