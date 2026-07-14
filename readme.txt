=== Uno WP Form reCAPTCHA ===
Tags: Uno WP Form, reCAPTCHA, contact form, spam protection
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds Google reCAPTCHA with server-side verification and a honeypot field to Uno WP Form.

Uno WP Form に Google reCAPTCHA（サーバー側検証つき）とハニーポットフィールドを追加します。

== Description ==

Uno WP Form reCAPTCHA inserts Google reCAPTCHA before the submit button on Uno WP Form input screens.

Uno WP Form reCAPTCHA は、Uno WP Form の入力画面で送信ボタンの前に Google reCAPTCHA を表示します。

This plugin requires Uno WP Form.

このプラグインを利用するには Uno WP Form が必要です。

The plugin renders Google reCAPTCHA using a Site key and disables the submit button until the reCAPTCHA challenge is completed.

このプラグインは Site key を使って Google reCAPTCHA を描画し、reCAPTCHA が完了するまで送信ボタンを無効化します。

When a Secret key is configured, the plugin verifies the reCAPTCHA response on the server via Google's `siteverify` API on the input-to-confirm transition, and requires the verified session flag on the confirm-to-complete transition. This blocks bots that skip JavaScript or post directly to the completion step.

Secret key を設定すると、入力→確認の遷移時に Google の `siteverify` API でサーバー側検証を行い、確認→送信の遷移時には検証済みセッションフラグを必須とします。これにより、JavaScript を実行しない bot や送信ステップへ直接 POST する bot を遮断します。

The plugin also injects a visually hidden honeypot field into every Uno WP Form and rejects submissions that fill it in. The honeypot works even without reCAPTCHA keys.

また、すべての Uno WP Form に視覚的に隠されたハニーポットフィールドを挿入し、これを埋めた送信を拒否します。ハニーポットは reCAPTCHA キー未設定でも動作します。

== Installation ==

1. Upload the `uno-wp-form-recaptcha` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the WordPress Plugins screen.
3. Make sure Uno WP Form is installed and active.
4. Open `Uno WP Form reCAPTCHA` from the admin menu.
5. Enter the Site key and Secret key from the Google reCAPTCHA admin console.
6. Save changes.

インストール:

1. `uno-wp-form-recaptcha` フォルダを `/wp-content/plugins/` ディレクトリへアップロードします。
2. WordPress管理画面の「プラグイン」から有効化します。
3. Uno WP Form がインストールされ、有効化されていることを確認します。
4. 管理メニューから `Uno WP Form reCAPTCHA` を開きます。
5. Google reCAPTCHA 管理画面で取得した Site key と Secret key を入力します。
6. 変更を保存します。

== Frequently Asked Questions ==

= Does this plugin require Uno WP Form? =

Yes. This plugin is designed for Uno WP Form and targets Uno WP Form's frontend classes.

= Uno WP Form は必要ですか？ =

はい。このプラグインは Uno WP Form 用で、Uno WP Form のフロントエンドクラスを対象にしています。

= Where do I enter the Site key? =

Open `Uno WP Form reCAPTCHA` in the WordPress admin menu and paste the Site key into the Site key field.

= Site key はどこに入力しますか？ =

WordPress管理画面の `Uno WP Form reCAPTCHA` を開き、Site key フィールドに貼り付けます。

= Does this plugin use the Secret key? =

Yes, since version 1.1.0. When the Secret key is set, the reCAPTCHA response is verified on the server via Google's `siteverify` API. If the Secret key is empty, only client-side display, submit-button control and the honeypot are active.

= Secret key は使いますか？ =

はい（バージョン 1.1.0 以降）。Secret key を設定すると、Google の `siteverify` API でサーバー側検証を行います。未設定の場合は、クライアント側の表示・送信ボタン制御とハニーポットのみが有効です。

= Can I test this on localhost? =

Yes. Add `localhost` to the allowed domains for your reCAPTCHA key in the Google reCAPTCHA admin console.

= localhost でテストできますか？ =

はい。Google reCAPTCHA 管理画面で対象キーの許可ドメインに `localhost` を追加してください。

== Screenshots ==

1. reCAPTCHA rendered before the submit button.
2. Site key settings screen.

スクリーンショット:

1. 送信ボタン前に表示された reCAPTCHA。
2. Site key 設定画面。

== Changelog ==

= 1.1.0 =
* Add: server-side verification of the reCAPTCHA response via Google's `siteverify` API (Secret key setting added).
* Add: session-flag check on the confirm-to-complete transition to block bots that post directly to the completion step.
* Add: visually hidden honeypot field injected into every Uno WP Form, validated on the server.
* Add: migrate the Secret key from the legacy `mw-wp-form-recaptcha` add-on when present.

= 1.0.0 =
* Initial release: reCAPTCHA display and submit-button control.
