<?php

// セッション宣言
// start_session();
session_start();

// アップロード時の変更箇所。IMAGES_DIR, THUMBNAIL_DIR, upload.php header
ini_set('display_errors', 1); // xampp みたいなエラーメッセージ出してくれる
define('MAX_FILE_SIZE', 1 * 1024 * 1024); // 1MB
define('THUMBNAIL_WIDTH', 400);
define('IMAGES_DIR', __DIR__ . '/img'); // 画像ファイルのディレクトリ（__DIR__ は現在のディレクトリ取得）
define('THUMBNAIL_DIR', __DIR__ . '/s'); // サムネイルディレクトリ
// define('IMAGES_DIR', 'http://localhost/PG/DotInstall/PHP/php7/img');
// define('THUMBNAIL_DIR', 'http://localhost/PG/DotInstall/PHP/php7/s');
// define('IMAGES_DIR', 'C:\xampp\htdocs\PG\DotInstall\PHP\php7\img'); // ローカル開発環境のディレクトリはこちらが正解
// define('THUMBNAIL_DIR', 'C:\xampp\htdocs\PG\DotInstall\PHP\php7\s');

// 画像処理には GD というプラグインが必要。入っているかどうかのチェック
if (!function_exists('imagecreatetruecolor')) {
  echo "GD が入ってへん！！";
  exit;
}

// さまざまな表示のためのエスケープ
function h($s) {
  // return htmlspecialchars($s, "ENT_QUOTES", "UTF-8");
  return htmlspecialchars($s, ENT_QUOTES, "UTF-8"); // ENT_QUOTES にはダブルコートつけない
}

require "uploader.php";

$uploader = new \Eningrad\ImgUploader();

if ($_SERVER["REQUEST_METHOD"] === "POST") { // 定義済み変数。投稿、送信が行われたらの処理
  $uploader->upload();
}

list($success, $error) = $uploader->getResults();
$images = $uploader->getImages();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>画像アップロード掲示板</title>
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
    アップロードやで！！
    <form action="" method="post" enctype="multipart/form-data" id="my-form">
      <!-- ファイルの最大サイズの指定 -->
      <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo h(MAX_FILE_SIZE); ?>">
      <input type="file" name="image" id="my-file">
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
    <?php foreach ($images as $image) : ?>
      <li>
        <a href="<?php echo h(basename(IMAGES_DIR)) . "/" . h(basename($image)); // basename() はパスからファイル名を取得 ?>">
          <img src="<?php echo h($image); ?>">
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