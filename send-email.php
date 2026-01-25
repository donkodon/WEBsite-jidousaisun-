<?php
// 文字エンコード設定（最初に設定）
mb_language('Japanese');
mb_internal_encoding('UTF-8');

// CORS設定
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

// OPTIONSリクエストへの対応
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// POSTリクエストのみ受け付け
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// POSTデータを取得
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// 必須フィールドの確認
$required = ['company', 'name', 'email', 'inquiryType', 'message'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

// メール送信先
$to = 'kenji.noto@relight-rl.com';

// メール件名（エンコード）
$subject = mb_encode_mimeheader('【Relight】お問い合わせ: ' . $data['inquiryType'], 'UTF-8');

// メール本文
$message = "新しいお問い合わせがありました\n\n";
$message .= "【会社名】\n";
$message .= $data['company'] . "\n\n";
$message .= "【お名前】\n";
$message .= $data['name'] . "\n\n";
$message .= "【メールアドレス】\n";
$message .= $data['email'] . "\n\n";
$message .= "【電話番号】\n";
$message .= $data['phone'] . "\n\n";
$message .= "【お問い合わせ種別】\n";
$message .= $data['inquiryType'] . "\n\n";
$message .= "【お問い合わせ内容】\n";
$message .= $data['message'] . "\n\n";
$message .= "【送信日時】\n";
$message .= date('Y/m/d H:i:s');

// メールヘッダー
$headers = "From: " . mb_encode_mimeheader('Relight お問い合わせフォーム', 'UTF-8') . " <kenji.noto@relight-rl.com>\r\n";
$headers .= "Reply-To: " . $data['email'] . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit";

// 1. 管理者へのメール送信
$result1 = mb_send_mail($to, $subject, $message, $headers);

// 2. お客様への確認メール
$customer_subject = mb_encode_mimeheader('【Relight】お問い合わせを受け付けました', 'UTF-8');

$customer_message = $data['name'] . " 様\n\n";
$customer_message .= "この度は、Relightへお問い合わせいただき、誠にありがとうございます。\n";
$customer_message .= "以下の内容でお問い合わせを受け付けました。\n\n";
$customer_message .= "【会社名】\n";
$customer_message .= $data['company'] . "\n\n";
$customer_message .= "【お名前】\n";
$customer_message .= $data['name'] . "\n\n";
$customer_message .= "【メールアドレス】\n";
$customer_message .= $data['email'] . "\n\n";
$customer_message .= "【電話番号】\n";
$customer_message .= $data['phone'] . "\n\n";
$customer_message .= "【お問い合わせ種別】\n";
$customer_message .= $data['inquiryType'] . "\n\n";
$customer_message .= "【お問い合わせ内容】\n";
$customer_message .= $data['message'] . "\n\n";
$customer_message .= "【送信日時】\n";
$customer_message .= date('Y/m/d H:i:s') . "\n\n";
$customer_message .= "担当者より2営業日以内にご連絡させていただきます。\n";
$customer_message .= "今しばらくお待ちくださいませ。\n\n";
$customer_message .= "※このメールは自動送信されています。\n";
$customer_message .= "このメールに返信いただいても対応できかねますので、ご了承ください。\n\n";
$customer_message .= "─────────────────────────\n";
$customer_message .= "Relight - EC出品自動化ソリューション\n";
$customer_message .= "Email: kenji.noto@relight-rl.com\n";
$customer_message .= "Website: https://relight-rl.com\n";
$customer_message .= "─────────────────────────";

$customer_headers = "From: " . mb_encode_mimeheader('Relight', 'UTF-8') . " <kenji.noto@relight-rl.com>\r\n";
$customer_headers .= "Reply-To: kenji.noto@relight-rl.com\r\n";
$customer_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$customer_headers .= "Content-Transfer-Encoding: 8bit";

$result2 = mb_send_mail($data['email'], $customer_subject, $customer_message, $customer_headers);

// 両方のメール送信結果を確認
if ($result1 && $result2) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'お問い合わせを受け付けました。確認メールをお送りしました。',
        'recipient' => $to,
        'customer_email_sent' => true
    ]);
} elseif ($result1 && !$result2) {
    // 管理者へのメールは送信成功、お客様へのメールは失敗
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'お問い合わせを受け付けました',
        'recipient' => $to,
        'customer_email_sent' => false,
        'warning' => '確認メールの送信に失敗しました'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'メール送信に失敗しました'
    ]);
}
?>
