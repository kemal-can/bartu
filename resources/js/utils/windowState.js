/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
class WindowState {
  constructor() {
    this.hasSupport = 'history' in window && 'pushState' in history
  }
  /**
   * Push in history state
   *
   * @param  {string|null} url
   * @param  {Object|null|string} state
   * @param  {null|string} title
   *
   * @return {void}
   */
  push(url, state = {}, title = null) {
    if (!this.hasSupport) {
      return
    }

    window.history.pushState(state, title, url)
  }

  /**
   * Replace history state
   *
   * @param  {string|null} url
   * @param  {Object|null|string} state
   * @param  {null|string} title
   *
   * @return {void}
   */
  replace(url, state = null, title = null) {
    if (!this.hasSupport) {
      return
    }

    window.history.replaceState(state || window.history.state, title, url)
  }

  /**
   * Clear state hash
   *
   * @param  {String} replaceWith
   *
   * @return {Void}
   */
  clearHash(replaceWith = ' ') {
    return this.replace(replaceWith)
  }
}

export default new WindowState()
