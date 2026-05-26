import { type Config, type RouteParamsWithQueryOverload } from 'ziggy-js';

declare module 'ziggy-js' {
  export const Ziggy: Config;
}

type RouteUrl = string;

interface Router {
  current(): string | undefined;
  current(name: string, params?: unknown): boolean;
}

interface RouteHelper {
  (): Router;
  (name: string, params?: RouteParamsWithQueryOverload | Record<string, unknown>, absolute?: boolean, config?: Config): RouteUrl;
}

declare global {
  const route: RouteHelper;
}

export {};
