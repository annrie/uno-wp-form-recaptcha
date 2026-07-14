# Uno WP Form reCAPTCHA

Uno WP Form reCAPTCHA adds Google reCAPTCHA with server-side verification and a honeypot field to Uno WP Form.

Uno WP Form reCAPTCHA は、Uno WP Form に Google reCAPTCHA（サーバー側検証つき）とハニーポットフィールドを追加するプラグインです。

## Requirements / 動作要件

- WordPress 6.0 or later
- Tested up to WordPress 7.0
- PHP 8.0 or later
- Uno WP Form

- WordPress 6.0 以上
- WordPress 7.0 で検証済み
- PHP 8.0 以上
- Uno WP Form

## What This Plugin Does / このプラグインが行うこと

- Displays Google reCAPTCHA before the submit button on Uno WP Form input screens.
- Disables the submit button until the reCAPTCHA challenge is completed.
- Reuses an existing manually written `<div class="g-recaptcha">` in the form when present.
- Inserts a reCAPTCHA container automatically when one is not present.
- Verifies the reCAPTCHA response on the server via Google's `siteverify` API when a Secret key is configured (since 1.1.0).
- Requires the verified session flag on the confirm-to-complete transition, blocking bots that post directly to the completion step (since 1.1.0).
- Injects a visually hidden honeypot field into every Uno WP Form and rejects submissions that fill it in (since 1.1.0).

- Uno WP Form の入力画面で、送信ボタンの前に Google reCAPTCHA を表示します。
- reCAPTCHA が完了するまで送信ボタンを無効化します。
- フォーム内に手書きの `<div class="g-recaptcha">` がある場合は、それを再利用します。
- 手書きの reCAPTCHA コンテナがない場合は、自動的にコンテナを挿入します。
- Secret key 設定時は、Google の `siteverify` API でサーバー側検証を行います（1.1.0以降）。
- 確認→送信の遷移時に検証済みセッションフラグを必須とし、送信ステップへ直接 POST する bot を遮断します（1.1.0以降）。
- すべての Uno WP Form に視覚的に隠されたハニーポットフィールドを挿入し、これを埋めた送信を拒否します（1.1.0以降）。

## Security Notes / セキュリティ上の注意

If the Secret key is left empty, server-side verification is skipped and only client-side display, submit-button control and the honeypot are active. Configure both keys for full protection.

Secret key が未設定の場合、サーバー側検証はスキップされ、クライアント側の表示・送信ボタン制御とハニーポットのみが有効になります。完全な保護には両方のキーを設定してください。

If Google's `siteverify` endpoint is unreachable (network error), verification fails open so that legitimate visitors are not locked out; explicit verification failures are rejected.

Google の `siteverify` エンドポイントに到達できない場合（ネットワークエラー）は、正規の訪問者を締め出さないようフェイルオープンで通過させます。検証が明示的に失敗した場合は拒否します。

## Key Setup / キーの設定

1. Open the Google reCAPTCHA admin console.
2. Create or select a reCAPTCHA v2 ("I'm not a robot") key.
3. Add the production domain to the allowed domains.
4. Add `localhost` only when local testing is needed.
5. Copy the Site key and the Secret key. For keys migrated to the Google Cloud console (reCAPTCHA Enterprise), open the key's Integration tab and use "Use legacy key" to get the legacy secret key.
6. Open `Uno WP Form reCAPTCHA` in the WordPress admin menu.
7. Paste the Site key and Secret key, then save changes.

1. Google reCAPTCHA 管理画面を開きます。
2. reCAPTCHA v2（「私はロボットではありません」）のキーを作成するか、既存のキーを選択します。
3. 本番ドメインを許可ドメインに追加します。
4. ローカルテストが必要な場合のみ `localhost` を追加します。
5. Site key と Secret key をコピーします。Google Cloud コンソール（reCAPTCHA Enterprise）に移行済みのキーの場合は、キーの「統合」タブの「レガシーキーを使用」からレガシー シークレットキーを取得します。
6. WordPress管理画面の `Uno WP Form reCAPTCHA` を開きます。
7. Site key と Secret key を貼り付けて保存します。

## Installation / インストール

1. Upload the `uno-wp-form-recaptcha` folder to `/wp-content/plugins/`.
2. Activate the plugin from the WordPress Plugins screen.
3. Make sure Uno WP Form is installed and active.
4. Configure the Site key and Secret key from the plugin settings screen.

1. `uno-wp-form-recaptcha` フォルダを `/wp-content/plugins/` へアップロードします。
2. WordPress管理画面の「プラグイン」から有効化します。
3. Uno WP Form がインストールされ、有効化されていることを確認します。
4. プラグイン設定画面で Site key と Secret key を設定します。

## Related Plugin / 関連プラグイン

Uno WP Form:

https://github.com/annrie/uno-wp-form

## License / ライセンス

GPLv2 or later.

GPLv2 以降。
