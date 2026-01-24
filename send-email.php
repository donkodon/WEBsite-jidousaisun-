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

// メール送信
$result = mb_send_mail($to, $subject, $message, implode("\r\n", $headers));

if ($result) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'お問い合わせを受け付けました',
        'recipient' => $to
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'メール送信に失敗しました'
    ]);
}
?>
