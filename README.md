# employee

## ローカルデバッグ環境

### 1. 前提

- Docker Desktop
- VS Code
- 拡張機能 `PHP Debug`（`xdebug.php-debug`）
- `mkcert`（ローカル証明書生成）

### 2. ローカル設定ファイル作成

以下をコピーして作成します。

```bash
cp conf/systemConst.local.php.example conf/systemConst.local.php
cp conf/DBConst.local.php.example conf/DBConst.local.php
cp admin/conf/systemConst.local.php.example admin/conf/systemConst.local.php
```

必要に応じて DB 接続情報を `conf/DBConst.local.php` で調整してください。

### 3. コンテナ起動

初回のみ、ローカル証明書を作成します。

```bash
mkcert -install
mkcert -key-file docker/certs/localhost-key.pem -cert-file docker/certs/localhost.pem localhost 127.0.0.1 ::1
```

`mkcert -install` は管理者パスワード入力が必要です。実行後にブラウザ再起動すると証明書警告なしでアクセスできます。

```bash
docker compose up -d --build
```

- フロント(HTTPS): `https://localhost:8080`
- フロント(HTTP): `http://localhost:8082`
- 管理画面: `http://localhost:8081`
- MySQL: `localhost:33060`

### 4. VS Code でデバッグ開始

1. VS Code の「実行とデバッグ」で `Listen for Xdebug` を選択
2. `htdocs` または `admin` 配下にブレークポイントを設定
3. ブラウザで `https://localhost:8080` または `http://localhost:8081` にアクセス

Xdebug は `docker/php/php.ini` で `start_with_request=yes` に設定済みです。

### 5. 停止

```bash
docker compose down
```