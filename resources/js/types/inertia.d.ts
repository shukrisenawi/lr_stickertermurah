/// <reference types="vite/client" />

declare module '*.css' {
  const content: Record<string, string>;
  export default content;
}

interface ImportMetaEnv {
  readonly VITE_APP_NAME: string;
  readonly VITE_APP_URL: string;
  readonly VITE_PUSHER_APP_KEY: string;
  readonly VITE_PUSHER_HOST: string;
  readonly VITE_PUSHER_PORT: string;
  readonly VITE_PUSHER_SCHEME: string;
  readonly VITE_PUSHER_APP_CLUSTER: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
  readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
}
