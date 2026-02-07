# attendance-management-app(勤怠管理アプリ)

## 概要

Laravelを使用した勤怠管理アプリケーションです。
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

※ MacのM1・M2チップPCをご利用の場合、
no matching manifest for linux/arm64/v8 in the manifest list entries のメッセージが表示されビルドができないことがあります。
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

### 管理者アカウント（Admin）

全ての管理者機能（ユーザー一覧、打刻修正承認など）を確認できます。
・メールアドレス：admin@example.com
・パスワード：password

### 一般ユーザーアカウント(General)

打刻機能、修正申請機能を確認できます。
・メールアドレス：user@example.com
・パスワード：password

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
| role_id           | int          |     |     |    ○     |     |
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
| :------------ | :-------- | :-: | :-: | :------: | :-------------- | --- |
| id            | bigint    |  ○  |     |    ○     |                 |
| attendance_id | bigint    |     |     |    ○     | attendances(id) |
| start_time    | time      |     |     |    ○     |                 |
| end_time      | time      |     |     |          |                 |     |
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

![ER図](src/attendance-management-app.drawio.svg)
