# Xserver デプロイガイド

## 📦 デプロイするファイル

以下のファイルをXserverにアップロードしてください：

### 必須ファイル
```
/
├── index.html           # トップページ
├── contact.html         # お問い合わせページ
├── send-email.php       # メール送信スクリプト（新規作成）
└── images/              # 画像フォルダ
    ├── RELIGHT_icon.png
    ├── ai_measure_phone.png
    ├── ai_model_white_shirt_clean.png
    ├── image_editing_interface_tshirt.png
    ├── hero_bg.png
    └── laurel_wreath_transparent.png
```

### 不要なファイル（アップロード不要）
- `send-email.js` (Node.js版、PHPに置き換え済み)
- `node_modules/`
- `package.json`
- `package-lock.json`
- `.git/`
- `README.md`
- `DEPLOY_GUIDE.md`

---

## 🚀 デプロイ手順

### 1. Xserver サーバーパネルにログイン

URL: https://www.xserver.ne.jp/login_server.php

### 2. FTPアカウント情報を確認

**サーバーパネル** → **FTPアカウント設定**

必要な情報：
- FTPホスト名: `sv16714.xserver.jp`
- FTPユーザー名: `xs319839`（またはあなたのアカウント）
- FTPパスワード: 設定したパスワード

### 3. ファイルをアップロード

#### 方法A: FileZilla（推奨）

1. **FileZillaをダウンロード**: https://filezilla-project.org/
2. **接続設定**:
   - ホスト: `sv16714.xserver.jp`
   - ユーザー名: `xs319839`
   - パスワード: あなたのFTPパスワード
   - ポート: 21
3. **アップロード先**: `/public_html/relight-rl.com/` （またはドメインのディレクトリ）
4. **ファイルをドラッグ&ドロップ**

#### 方法B: Xserver ファイルマネージャー

1. **サーバーパネル** → **ファイルマネージャー**
2. **ログイン**
3. **public_html/relight-rl.com/** に移動
4. **アップロード**ボタンでファイルを選択

### 4. パーミッション設定

`send-email.php` のパーミッションを確認：
- **推奨**: `644` または `755`
- FileZillaで右クリック → **ファイルのパーミッション**

### 5. PHPバージョン確認

**サーバーパネル** → **PHP Ver.切替**
- 推奨: **PHP 8.x** 以上

### 6. メール送信テスト

デプロイ後、Webサイトにアクセスしてお問い合わせフォームをテスト：

```
https://relight-rl.com/contact.html
```

フォームを送信して、kenji.noto@relight-rl.com にメールが届くか確認。

---

## 🔧 トラブルシューティング

### メールが届かない場合

#### 1. PHPのエラーログを確認

**サーバーパネル** → **エラーログ**

#### 2. send-email.php のパーミッション確認

`644` または `755` に設定されているか確認

#### 3. mb_send_mail が有効か確認

Xserverでは標準で有効ですが、念のため確認：
- サーバーパネル → **PHP設定**

#### 4. CORS エラーの場合

`.htaccess` に以下を追加：

```apache
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "POST, GET, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type"
</IfModule>
```

### 500 Internal Server Error

1. PHPのバージョンを確認（PHP 7.4以上推奨）
2. `send-email.php` の構文エラーを確認
3. エラーログを確認

---

## 📧 メール送信の仕組み

### デプロイ後の動作フロー

```
ユーザー（ブラウザ）
    ↓
contact.html（フォーム送信）
    ↓
send-email.php（Xserver内部）
    ↓
mb_send_mail()（PHPの標準関数）
    ↓
Xserver SMTP（ローカル接続）
    ↓
kenji.noto@relight-rl.com（受信）
```

**ポイント**:
- ✅ Xserverサーバー内部で完結
- ✅ 外部SMTP制限の影響を受けない
- ✅ 追加設定不要

---

## 🎯 デプロイ後の確認リスト

- [ ] index.html が表示される
- [ ] contact.html が表示される
- [ ] 画像が正しく表示される
- [ ] ロゴ（RELIGHT_icon.png）が表示される
- [ ] お問い合わせフォームが動作する
- [ ] テストメールが kenji.noto@relight-rl.com に届く

---

## 🌐 デプロイ先URL

**本番サイト**: https://relight-rl.com
**お問い合わせ**: https://relight-rl.com/contact.html

---

## 📞 サポート

問題が発生した場合は、Xserverサポートに問い合わせ：
- サポート: https://www.xserver.ne.jp/support/
- 電話: 06-6147-2580

---

## ✅ デプロイ完了後

デプロイが完了したら、必ずテスト送信を行ってください！

1. お問い合わせフォームに入力
2. 送信ボタンをクリック
3. 成功メッセージが表示されるか確認
4. kenji.noto@relight-rl.com にメールが届くか確認

お疲れさまでした！ 🎉
