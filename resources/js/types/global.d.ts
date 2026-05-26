declare global {
  interface Window {
    axios: import('axios').AxiosStatic;
  }
}

export {};
