<?php
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

// メール件名
$subject = '【Relight】お問い合わせ: ' . $data['inquiryType'];

// メール本文
$message = <<<EOT
新しいお問い合わせがありました

【会社名】
{$data['company']}

【お名前】
{$data['name']}

【メールアドレス】
{$data['email']}

【電話番号】
{$data['phone']}

【お問い合わせ種別】
{$data['inquiryType']}

【お問い合わせ内容】
{$data['message']}

【送信日時】
EOT;
$message .= "\n" . date('Y/m/d H:i:s');

// メールヘッダー
$headers = [
    'From: Relight お問い合わせフォーム <kenji.noto@relight-rl.com>',
    'Reply-To: ' . $data['email'],
    'Content-Type: text/plain; charset=UTF-8'
];

// 1. 管理者へのメール送信
$result1 = mb_send_mail($to, $subject, $message, implode("\r\n", $headers));

// 2. お客様への確認メール
$customer_subject = '【Relight】お問い合わせを受け付けました';
$customer_message = <<<EOT
{$data['name']} 様

この度は、Relightへお問い合わせいただき、誠にありがとうございます。
以下の内容でお問い合わせを受け付けました。

【会社名】
{$data['company']}

【お名前】
{$data['name']}

【メールアドレス】
{$data['email']}

【電話番号】
{$data['phone']}

【お問い合わせ種別】
{$data['inquiryType']}

【お問い合わせ内容】
{$data['message']}

【送信日時】
EOT;
$customer_message .= "\n" . date('Y/m/d H:i:s');
$customer_message .= <<<EOT


担当者より2営業日以内にご連絡させていただきます。
今しばらくお待ちくださいませ。

※このメールは自動送信されています。
このメールに返信いただいても対応できかねますので、ご了承ください。

─────────────────────────
Relight - EC出品自動化ソリューション
Email: kenji.noto@relight-rl.com
Website: https://relight-rl.com
─────────────────────────
EOT;

$customer_headers = [
    'From: Relight <kenji.noto@relight-rl.com>',
    'Reply-To: kenji.noto@relight-rl.com',
    'Content-Type: text/plain; charset=UTF-8'
];

$result2 = mb_send_mail($data['email'], $customer_subject, $customer_message, implode("\r\n", $customer_headers));

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
