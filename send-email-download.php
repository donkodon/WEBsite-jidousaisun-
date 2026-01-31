<?php
// エラーレポート設定（本番環境ではコメントアウト推奨）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// フォームから送信されたデータを取得
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company = htmlspecialchars($_POST['company']);
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '未記入';
    
    // タイムスタンプ
    $timestamp = date('Y-m-d H:i:s');
    
    // ★★★ 資料PDFのURL（ここを実際のURLに変更してください）★★★
    $pdf_url = "https://yourdomain.com/downloads/relight-proposal.pdf";
    // または相対パス: $pdf_url = "downloads/relight-proposal.pdf";
    
    // ======================================
    // 1. ユーザーへ自動返信メール（PDFリンク付き）
    // ======================================
    
    $to_user = $email;
    $subject_user = "Relight サービス資料ダウンロード";
    $message_user = "
{$name} 様

この度は、Relightのサービス資料をダウンロードいただき、誠にありがとうございます。

━━━━━━━━━━━━━━━━━━━━━━━━━━
📄 資料ダウンロードリンク
━━━━━━━━━━━━━━━━━━━━━━━━━━

以下のリンクより資料をダウンロードいただけます：
{$pdf_url}

※リンクの有効期限は30日間です。


━━━━━━━━━━━━━━━━━━━━━━━━━━
📌 Relightの主な機能
━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ 白抜き加工：プロ級の白抜き画像を瞬時に生成
✓ 自動採寸：AIが正確な寸法を自動測定
✓ AIライティング：魅力的な商品説明文を自動生成
✓ 着用画像生成：モデル撮影不要でAIが着用画像を作成


━━━━━━━━━━━━━━━━━━━━━━━━━━
💡 次のステップ
━━━━━━━━━━━━━━━━━━━━━━━━━━

資料をご覧いただき、ご不明な点やご質問がございましたら、
お気軽にお問い合わせください。

デモのご希望や詳細なご相談も承っております。
担当者より追ってご連絡させていただく場合がございます。


━━━━━━━━━━━━━━━━━━━━━━━━━━

Relight（リライト）
代表：能登健二
〒224-0062 神奈川県横浜市都筑区葛が谷14-3
Email: info@relight-rl.com
Website: https://yourdomain.com

━━━━━━━━━━━━━━━━━━━━━━━━━━
";
    
    // メールヘッダー（UTF-8対応）
    $headers_user = "From: Relight <info@relight-rl.com>\r\n";
    $headers_user .= "Reply-To: info@relight-rl.com\r\n";
    $headers_user .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers_user .= "X-Mailer: PHP/" . phpversion();
    
    // ユーザーへメール送信
    $mail_sent_user = mail($to_user, $subject_user, $message_user, $headers_user);
    
    
    // ======================================
    // 2. 営業チームへ通知メール
    // ======================================
    
    $to_sales = "info@relight-rl.com"; // 営業チームのメールアドレス
    $subject_sales = "【新規】資料ダウンロード - {$company}";
    $message_sales = "
新規で資料ダウンロードがありました。

━━━━━━━━━━━━━━━━━━━━━━
📋 ダウンロード情報
━━━━━━━━━━━━━━━━━━━━━━

会社名:     {$company}
お名前:     {$name}
メール:     {$email}
電話番号:   {$phone}
日時:       {$timestamp}

━━━━━━━━━━━━━━━━━━━━━━

【対応アクション】
- フォローアップメールの送信を検討
- 3営業日以内に電話フォロー推奨
- CRMへの登録

━━━━━━━━━━━━━━━━━━━━━━
Relight 自動通知システム
━━━━━━━━━━━━━━━━━━━━━━
";
    
    $headers_sales = "From: Relight System <info@relight-rl.com>\r\n";
    $headers_sales .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // 営業チームへメール送信
    $mail_sent_sales = mail($to_sales, $subject_sales, $message_sales, $headers_sales);
    
    
    // ======================================
    // 3. CSVファイルに記録（オプション）
    // ======================================
    
    $csv_file = 'downloads.csv';
    
    // CSVファイルが存在しない場合、ヘッダー行を作成
    if (!file_exists($csv_file)) {
        $header = array('日時', '会社名', 'お名前', 'メールアドレス', '電話番号');
        $fp = fopen($csv_file, 'w');
        fputcsv($fp, $header);
        fclose($fp);
    }
    
    // データを追記
    $data = array($timestamp, $company, $name, $email, $phone);
    $fp = fopen($csv_file, 'a');
    fputcsv($fp, $data);
    fclose($fp);
    
    
    // ======================================
    // 4. サンクスページへリダイレクト
    // ======================================
    
    if ($mail_sent_user && $mail_sent_sales) {
        // 成功：サンクスページへ
        header("Location: download-thanks.html");
        exit;
    } else {
        // エラー：エラーメッセージ表示
        echo "<!DOCTYPE html>
<html lang='ja'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>エラー - Relight</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-50 flex items-center justify-center min-h-screen'>
    <div class='max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg'>
        <div class='text-center'>
            <div class='text-red-500 text-6xl mb-4'>
                <i class='fa-solid fa-circle-exclamation'></i>
            </div>
            <h1 class='text-2xl font-bold text-gray-900 mb-4'>送信エラー</h1>
            <p class='text-gray-600 mb-6'>
                メールの送信に失敗しました。<br>
                お手数ですが、もう一度お試しいただくか、<br>
                直接お問い合わせください。
            </p>
            <a href='download.html' class='inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-700 transition'>
                戻る
            </a>
        </div>
    </div>
</body>
</html>";
        exit;
    }
    
} else {
    // POSTリクエスト以外はダウンロードページへリダイレクト
    header("Location: download.html");
    exit;
}
?>
