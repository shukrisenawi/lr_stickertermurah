import { type Config, type RouteParamsWithQueryOverload } from 'ziggy-js';

declare module 'ziggy-js' {
  export const Ziggy: Config;
}

interface RouteHelper {
  (name: string, params?: RouteParamsWithQueryOverload | Record<string, unknown>, absolute?: boolean, config?: Config): string;
  current: (name?: string) => string | false;
}

declare global {
  const route: RouteHelper;
}

export {};
