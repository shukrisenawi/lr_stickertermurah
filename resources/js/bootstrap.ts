/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';

const axiosInstance = axios.create();
(window as unknown as Record<string, unknown>).axios = axiosInstance;

axiosInstance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
