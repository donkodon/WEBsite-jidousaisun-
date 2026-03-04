# Xserver デプロイガイド

## 重要な修正: 画像ファイル名を小文字に統一

すべての画像ファイル名は小文字に統一されています。
特に `relight_icon.png` (旧: RELIGHT_icon.png) に注意してください。

## アップロード手順

1. このフォルダ内のすべてのファイルを `public_html` にアップロード
2. imagesフォルダ内のすべての画像ファイルをアップロード
3. パーミッション設定:
   - HTMLファイル: 644
   - PHPファイル: 644
   - imagesフォルダ: 755
   - 画像ファイル: 644

## 画像ファイルの確認

アップロード後、以下のURLで画像が表示されることを確認:
- https://your-domain.com/images/relight_icon.png
- https://your-domain.com/images/laurel_wreath_transparent.png
- https://your-domain.com/images/image_editing_interface_tshirt.png

404エラーが出る場合:
1. ファイル名の大文字小文字を確認
2. imagesフォルダのパーミッションを755に設定
3. 画像ファイルのパーミッションを644に設定
