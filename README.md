# employee

PHPバックエンドとReactフロントエンドを分離した構成のプロジェクトです。

## ディレクトリ構成

```text
.github/
.context/
doc/
mysql/          # mysql scripts/data (docker)
backend/        # php backend
frontend/       # react frontend
docker-compose.yml
README.md
.env
LICENSE
```

## セットアップ

### 1) MySQL を起動

```bash
docker compose up -d mysql
```

データは `mysql/data/` に永続化されます。

### 1.5) DBマイグレーション実行（Python）

```bash
python3 mysql/db-migrate.py
```

### 2) React フロントエンドをインストール

```bash
cd frontend
npm install
```

## 開発

### React 開発サーバ起動

```bash
cd frontend
npm run dev
```

## 本番ビルド

```bash
cd frontend
npm run build
```

ビルド成果物は `backend/htdocs/assets/react/` に出力されます。

## 補足

- PHPバックエンドのソースは `backend/` 配下にあります。
- Reactアプリは `frontend/` 配下で管理します。