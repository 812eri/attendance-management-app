# attendance-management-app(勤怠管理アプリ)

## 概要

Laravelを使用した勤怠管理アプリケーションです。<br>
勤怠・退勤の打刻や、打刻修正リクエスト機能などを備えています。

## 機能一覧

- ユーザー登録・ログイン機能
- 出勤・退勤打刻機能
- 打刻修正リクエスト機能
- 管理者機能（ユーザー管理・打刻修正承認など）

## 環境構築

### Docker ビルド

1.  リポジトリをクローン

    ```bash
    git clone https://github.com/812eri/attendance-management-app.git
    ```

2.  DockerDesktop　アプリを立ち上げる

3.  コンテナをビルド・起動

    ```bash
    docker-compose up -d --build
    ```

※ MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

※ MacのM1・M2チップPCをご利用の場合、no matching manifest for linux/arm64/v8 in the manifest list entries のメッセージが表示されビルドができないことがあります。<br>
エラーが発生する場合は、docker-compose.ymlファイルの「mysql」内に「platform」の項目を追加で記載してください。

```YAML
mysql:
    platform: linux/x86_64 # ←ここに追加
    image: mysql:8.0.26
    environment:
```

### Laravel 環境構築

1. PHPコンテナに入る

   ```bash
   docker-compose exec php bash
   ```

2. 依存パッケージのインストール

   ```bash
   composer install
   ```

3. 環境変数の設定
   .env.example をコピーして .env を作成し、以下の設定を記述してください。

   ```bash
   cp .env.example .env
   ```

   ▼データーベース設定

   ```ini
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel_db
   DB_USERNAME=larabel_user
   DB_PASSWORD=laravel_pass
   ```

4. アプリケーションキーの作成

```bash
php artisan key:generate
```

5. マイグレーションとシーディングの実行

```bash
php artisan migrate:fresh --seed
```

6. PHPコンテナから出る

```bash
exit
```

## 機能確認方法

環境構築（シーディング）完了後、以下の初期アカウントでログインして機能をテストできます。

## URL

- ログイン画面（一般ユーザー）: http://localhost/login
- ログイン画面（管理者）: http://localhost/admin/login

### 管理者アカウント（Admin）

全ての管理者機能（ユーザー一覧、打刻修正承認など）を確認できます。<br>
・メールアドレス：admin@example.com<br>
・パスワード：password

### 一般ユーザーアカウント(General)

打刻機能、修正申請機能を確認できます。<br>
・メールアドレス：user@example.com<br>
・パスワード：password

### その他検証用アカウント（複数ユーザー確認用）

ユーザー一覧やページネーションの動作確認用に、以下のメールアドレスも使用可能です。<br>
※パスワードが異なりますのでご注意ください。

- **メールアドレス例**:
  - `yamada@example.com`
  - `nishi@example.com`
  - `masuda@example.com`
  - その他: `yamamoto`, `akita`, `nakanishi`（@example.com）
- **パスワード**: `password123`

## メール認証（MailHog）

開発環境では実際のメールアドレスには送信されず、**MailHog**（メール確認ツール）でキャッチされます。<br>
ユーザー登録時の認証メールなどは、以下の手順で確認してください。

### 1-1. 環境構築（インストール）

本アプリケーションでは、開発用のメールサーバーとして **MailHog** をDockerコンテナで構築しています。
個別のインストールは不要ですが、`docker-compose.yml` に以下の設定が含まれていることを前提としています。

```yaml
# docker-compose.yml の設定例
mailhog:
  image: mailhog/mailhog
  ports:
    - "1025:1025" # SMTPサーバー (Laravelからの送信先)
    - "8025:8025" # Web管理画面 (ブラウザでの確認用)
```

### 1-2. 設定確認（.env）

`.env`ファイルのメール設定が以下のようになっているか確認してください。<br>
※ Docker環境の場合、`MAIL_HOST`は`docker-compose.yml`のサービス名（通常`mailhog`）を指定します。<br>
※ `MAIL_FROM_NAME` に `${APP_NAME}` を設定すると、アプリ名が差出人として表示されます。

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. メールの閲覧

ブラウザで以下のURLにアクセスすると、送信されたメールを確認できます。<br>
・MailHog管理画面: http://localhost:8025/

## テスト（PHPUnit）

PHPUnitを使用した自動テストの実行手順です。

### 1. テスト環境について

Laravelのデフォルト設定 (phpunit.xml) に従い、テスト実行時は自動的にテスト用データベースが使用されます。<br>
※ .env の内容は変更せず、そのまま実行可能です。

### 2. テストの実行

Dockerコンテナ内に入り、以下のコマンドを実行してください。

#### 2-1. コンテナに入る

```bash
docker compose exec php bash
```

#### 2-2. テストを実行する

```bash
#全てのテストを実行
php artisan test
```

#### （オプション）特定のファイルのみテストする場合

```bash
#例：ログイン機能のテストのみ実行
php artisan test tests/Feature/Auth/LoginTest.php
```

## テーブル設計

<details>
<summary>Users テーブル</summary>

ユーザー情報を管理するテーブルです。

| カラム名          | 型           | PK  | UK  | Not Null | FK  |
| :---------------- | :----------- | :-: | :-: | :------: | :-- |
| id                | bigint       |  ○  |     |    ○     |     |
| name              | varchar(255) |     |     |    ○     |     |
| email             | varchar(255) |     |  ○  |    ○     |     |
| password          | varchar(255) |     |     |    ○     |     |
| role_id           | tinyint      |     |     |    ○     |     |
| email_verified_at | timestamp    |     |     |          |     |
| remember_token    | varchar(100) |     |     |          |     |
| created_at        | timestamp    |     |     |          |     |
| updated_at        | timestamp    |     |     |          |     |

</details>

<details>
<summary>Attendances テーブル</summary>

日々の打刻データを管理するテーブルです。

| カラム名   | 型        | PK  |   UK    | Not Null | FK        |
| :--------- | :-------- | :-: | :-----: | :------: | :-------- |
| id         | bigint    |  ○  |         |    ○     |           |
| user_id    | bigint    |     | ○(複合) |    ○     | users(id) |
| date       | date      |     | ○(複合) |    ○     |           |
| start_time | time      |     |         |    ○     |           |
| end_time   | time      |     |         |          |           |
| remarks    | text      |     |         |          |           |
| created_at | timestamp |     |         |          |           |
| updated_at | timestamp |     |         |          |           |

※ user_id と date の組み合わせでユニーク制約を設定

</details>

<details>
<summary>StampCorrectionRequests テーブル</summary>

打刻修正申請を管理するテーブルです。

| カラム名       | 型           | PK  | UK  | Not Null | FK              |
| :------------- | :----------- | :-: | :-: | :------: | :-------------- |
| id             | bigint       |  ○  |     |    ○     |                 |
| user_id        | bigint       |     |     |    ○     | users(id)       |
| attendance_id  | bigint       |     |     |    ○     | attendances(id) |
| new_start_time | time         |     |     |    ○     |                 |
| new_end_time   | time         |     |     |    ○     |                 |
| new_remarks    | text         |     |     |    ○     |                 |
| status         | varchar(255) |     |     |    ○     |                 |
| created_at     | timestamp    |     |     |          |                 |
| updated_at     | timestamp    |     |     |          |                 |

</details>

<details>
<summary>Rests テーブル</summary>

勤務中の休憩時間を管理するテーブルです。（1つの勤怠に対して複数の休憩が可能）

| カラム名      | 型        | PK  | UK  | Not Null | FK              |
| :------------ | :-------- | :-: | :-: | :------: | :-------------- |
| id            | bigint    |  ○  |     |    ○     |                 |
| attendance_id | bigint    |     |     |    ○     | attendances(id) |
| start_time    | time      |     |     |    ○     |                 |
| end_time      | time      |     |     |          |                 |
| created_at    | timestamp |     |     |          |                 |
| updated_at    | timestamp |     |     |          |                 |

</details>

<details>
<summary>StampCorrectionRequestRests テーブル</summary>

休憩時間の修正申請データを管理する中間テーブルです。

| カラム名                    | 型        | PK  | UK  | Not Null | FK                            |
| :-------------------------- | :-------- | :-: | :-: | :------: | :---------------------------- |
| id                          | bigint    |  ○  |     |    ○     |                               |
| stamp_correction_request_id | bigint    |     |     |    ○     | stamp_correction_requests(id) |
| new_break_start             | time      |     |     |    ○     |                               |
| new_break_end               | time      |     |     |    ○     |                               |
| created_at                  | timestamp |     |     |          |                               |
| updated_at                  | timestamp |     |     |          |                               |

</details>

## 使用技術

- php 8.1
- Laravel 8.x
- MySQL 8.0
- Docker/Docker Compose
- MailHog（メールサーバー等の仮想環境）

## ER図

![ER図](src/attendance-management-app.drawio.svg)
