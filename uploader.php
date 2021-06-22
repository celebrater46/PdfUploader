<?php

namespace PdfUploader;

class Uploader {
    private $_pdfFileName;
    private $_fileType;

    public function upload() {
        try {
            // エラーチェック
            $this->_validateUpload();
            // ファイル形式をチェック
            $ext = $this->_validateImageType();
            // 保存
            $this->_save($ext);
            $_SESSION["success"] = "アップロード成功";
        } catch (\Exception $e) {
            $_SESSION["error"] = $e->getMessage();
        }
        // リダイレクトにはヘッダ命令
//        header("Location: http://" . $_SERVER["HTTP_HOST"]);
//        header("Location: https://localhost/myapps/PdfUploader/index.php"); // ローカルサーバーテスト用
        header("Location: http://" . $_SERVER["HTTP_HOST"] . "/test_php/pdf_uploader/index.php"); // テスト環境用
        exit;
    }

    public function getResults() {
        $success = null; // 初期化
        $error = null;
        if (isset($_SESSION["success"])) {
            $success = $_SESSION["success"];
            unset($_SESSION["success"]); // 変数に格納したので要らんからすぐに消す（残ってるとリロードした時に何度も出てくる）
        }
        if (isset($_SESSION["error"])) {
            $error = $_SESSION["error"];
            unset($_SESSION["error"]);
        }
        return [$success, $error];
    }

    public function getPdfFiles() {
        $pdfs = [];
        $fileNames = [];
        $fileDir = opendir(FILES_DIR);
        $test = 0; // テスト用
        while (false !== ($fileName = readdir($fileDir))) {
            if ($fileName === "." || $fileName === "..") {
                continue;
            }
            $fileNames[] = $fileName;
            $pdfs[] = basename(FILES_DIR) . "/" . $fileName;

            // 50回の制限を超えたらエラーに(無限ループ対策)
            $test++;
            if ($test > 50) {
                throw new \Exception("処理回数が50回を超えました");
            }
        }
        array_multisort($fileNames, SORT_DESC, $pdfs); // ファイルの逆順にイメージを並べる
        return $pdfs;
    }

    private function _save($ext) {
        $this->_pdfFileName = sprintf(
            '%s_%s.%s',
            time(), // UNIX Time Stamp
            sha1(uniqid(mt_rand(), true)),
            $ext
        );
        $savePath = FILES_DIR . '/' . $this->_pdfFileName;
        $res = move_uploaded_file($_FILES['pdf']['tmp_name'], $savePath);
        if ($res === false) {
            throw new \Exception('[ '. $_FILES['pdf']['tmp_name'] . ' ] はアップロードに失敗しました');
        }
        return $savePath;
    }

    private function _validateImageType() {
        $this->_fileType = mime_content_type($_FILES["pdf"]["tmp_name"]); // ファイルの種類を判別してくれる
        // PDF 以外はエラー
        if($this->_fileType !== "application/pdf") {
            throw new \Exception('ファイル形式 [ ' . $this->_fileType . ' ] はアップロードできません。');
        }
        return "pdf";
    }

    private function _validateUpload() {
        if (!isset($_FILES["pdf"]) || !isset($_FILES["pdf"]["error"])) { // "error" は改ざんされたフォームからのチェック
            // 変なファイル飛んできたらエスケープ
            throw new \Exception("ファイルサイズが大きすぎる（1MB以上）か、無効のファイルです。");
        }

        // エラーの種類に応じた処理
        switch ($_FILES["pdf"]["error"]) {
            case UPLOAD_ERR_OK: // うまくいった場合
                return true;
            case UPLOAD_ERR_INI_SIZE: // 既定のサイズを超えていた場合
            case UPLOAD_ERR_FORM_SIZE:
                $size = MAX_FILE_SIZE / 1024;
                throw new \Exception("ファイルサイズは " . $size . " KB までです。");
            default:
                throw new \Exception("原因不明のエラーが発生しました。" . $_FILES["pdf"]["error"]);
        }
    }
}