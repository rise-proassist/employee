# frontend

このディレクトリは、既存のPHPバックエンドを変更せずにフロントエンドをReactで再構築するためのプロジェクトです。

## 技術スタック

- React
- Vite
- Tailwind CSS

## 開発

```bash
npm install
npm run dev
```

### APIプロキシ設定（開発時）

郵便番号APIは Vite の `/api` プロキシ経由でバックエンドへ転送します。

1. `frontend/.env.development.example` を `frontend/.env.development` にコピー
2. `VITE_API_PROXY_TARGET` をローカルPHPの起動先に合わせる

例:

```env
VITE_API_PROXY_TARGET=http://localhost:8000
```

## 本番ビルド

```bash
npm run build
```

ビルド成果物は `../htdocs/assets/react` に出力されます。

## レンタルサーバへの配置

1. ローカルで `npm run build` を実行する
2. `backend/htdocs/assets/react` 配下の生成物をサーバへアップロードする
3. PHPテンプレート側から ` /assets/react/ ` 配下のファイルを読み込む

`vite.config.js` で `base` は `/assets/react/` に設定済みです。
