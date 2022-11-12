/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import { createApp } from 'vue'
import HTTP from '@/services/HTTP'
import mitt from 'mitt'
import router from '@/router'
import Mousetrap from 'mousetrap'

export default class Application {
  constructor(config) {
    this.bus = mitt()
    this.config = config
    this.bootingCallbacks = []
    this.axios = HTTP

    this.axios.defaults.baseURL = this.config.apiURL
  }

  /**
   * Start the application
   *
   * @return {Void}
   */
  start() {
    let self = this

    const data = {
      dialog: null,
    }

    Mousetrap.init()

    const app = createApp({
      data() {
        return data
      },
      mounted() {
        self.$on('conflict', message => {
          if (message) {
            self.info(message)
          }
        })

        self.$on('error', message => {
          if (message) {
            self.error(message)
          }
        })

        self.$on('too-many-requests', () => {
          self.error(this.$t('app.throttle_error'))
        })

        self.$on('token-expired', () => {
          self.error(
            this.$t('app.token_expired'),
            {
              action: {
                onClick: () => window.location.reload(),
                text: this.$t('app.reload'),
              },
            },
            30000
          )
        })

        self.$on('maintenance-mode', message => {
          self.info(
            message || 'Down for maintenance',
            {
              action: {
                onClick: () => window.location.reload(),
                text: this.$t('app.reload'),
              },
            },
            30000
          )
        })
      },
    })

    app.use(router)

    this.boot(app, router)

    this.app = app

    const vm = app.mount('#app')

    app.config.globalProperties.$dialog = {
      confirm(options) {
        // https://github.com/tailwindlabs/headlessui/issues/493
        const dialogIsOpen = document.querySelectorAll('.dialog')

        return new Promise((resolve, reject) => {
          vm.$data.dialog = Object.assign({}, options, {
            injectedInDialog: dialogIsOpen.length > 0,
            resolve: attrs => {
              resolve(attrs)
              vm.$data.dialog = null
            },
            reject: attrs => {
              reject(attrs)
              vm.$data.dialog = null
            },
          })
        })
      },
    }
  }

  /**
   * Get the application CSRF token
   *
   * @return {String|null}
   */
  csrfToken() {
    return this.config.csrfToken || null
  }

  /**
   * Register a callback to be called before the application starts
   */
  booting(callback) {
    this.bootingCallbacks.push(callback)
  }

  /**
   * Execute all of the booting callbacks.
   */
  boot(app, router) {
    this.bootingCallbacks.forEach(callback => callback(app, router))
    this.bootingCallbacks = []
  }

  /**
   * Helper request function
   * @param  {Object} options
   *
   * @return {Object}
   */
  request(options) {
    if (options !== undefined) {
      return this.axios(options)
    }

    return this.axios
  }

  /**
   * Register global event
   * @param  {mixed} args
   *
   * @return {Void}
   */
  $on(...args) {
    this.bus.on(...args)
  }

  /**
   * Deregister event
   * @param  {mixed} args
   *
   * @return {Void}
   */
  $off(...args) {
    this.bus.off(...args)
  }

  /**
   * Emit global event
   * @param  {mixed} args
   *
   * @return {Void}
   */
  $emit(...args) {
    this.bus.emit(...args)
  }

  /**
   * Show toasted success messages
   *
   * @param {String} message
   * @param {Object} options
   * @param {Number} duration
   *
   * @return {Void}
   */
  success(message, options, duration = 4000) {
    this.app.config.globalProperties.$notify(
      Object.assign({}, options, {
        text: message,
        type: 'success',
        group: 'app',
      }),
      duration
    )
  }

  /**
   * Show toasted info messages
   *
   * @param {String} message
   * @param {Object} options
   * @param {Number} duration
   *
   * @return {Void}
   */
  info(message, options, duration = 4000) {
    this.app.config.globalProperties.$notify(
      Object.assign({}, options, {
        text: message,
        type: 'info',
        group: 'app',
      }),
      duration
    )
  }

  /**
   * Show toasted error messages
   *
   * @param {String} message
   * @param {Object} options
   * @param {Number} duration
   *
   * @return {Void}
   */
  error(message, options, duration = 4000) {
    this.app.config.globalProperties.$notify(
      Object.assign({}, options, {
        text: message,
        type: 'error',
        group: 'app',
      }),
      duration
    )
  }

  /**
   * Add new a keyboard shortcut
   *
   * @return {Void}
   */
  addShortcut(keys, callback) {
    Mousetrap.bind(keys, callback)
  }

  /**
   * Disable keyboard shortcut
   *
   * @return {Void}
   */
  disableShortcut(keys) {
    Mousetrap.unbind(keys)
  }
}
