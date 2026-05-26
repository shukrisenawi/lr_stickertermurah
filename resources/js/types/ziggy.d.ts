import { type Config, type RouteParamsWithQueryOverload } from 'ziggy-js';

declare module 'ziggy-js' {
  export const Ziggy: Config;
}

declare global {
  function route(
    name?: string,
    params?: RouteParamsWithQueryOverload | Record<string, unknown>,
    absolute?: boolean,
    config?: Config
  ): string;
}

export {};
