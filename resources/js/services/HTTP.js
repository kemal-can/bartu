/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import axios from 'axios'
import router from '@/router'

const instance = axios.create()

instance.defaults.withCredentials = true
instance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
instance.defaults.headers.common['Content-Type'] = 'application/json'

instance.interceptors.response.use(
  response => {
    return response
  },
  error => {
    if (axios.isCancel(error)) {
      return error
    }

    const status = error.response.status

    if (status === 404) {
      // 404 not found
      router.replace({
        name: '404',
      })
    } else if (status === 403) {
      // Forbidden
      router.replace({
        name: '403',
        params: { errorMessage: error.response.data.message },
      })
    } else if (status === 401) {
      // Session timeout / Logged out
      window.location.href = Innoclapps.config.url + '/login'
    } else if (status === 409) {
      // Conflicts
      Innoclapps.$emit('conflict', error.response.data.message)
    } else if (status === 419) {
      // Handle expired CSRF token
      Innoclapps.$emit('token-expired', error)
    } else if (status === 422) {
      // Emit form validation errors event
      Innoclapps.$emit('form-validation-errors', error.response.data.errors)
    } else if (status === 429) {
      // Handle throttle errors
      Innoclapps.$emit('too-many-requests', error)
    } else if (status === 503) {
      Innoclapps.$emit('maintenance-mode', error.response.data.message)
    } else if (status >= 500) {
      // 500 errors
      Innoclapps.$emit('error', error.response.data.message)
    }

    // Do something with response error
    return Promise.reject(error)
  }
)

export default instance
export const CancelToken = axios.CancelToken
