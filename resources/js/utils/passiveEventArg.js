/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
/**
 * Get the passive third arguemtn and check whether the browser supports it
 *
 * @return {Boolen|Object}
 */
function passiveEventArg() {
  // Cache checks
  if (window.hasOwnProperty('__passiveEvt')) {
    return window.__passiveEvt
  }

  let result = false

  try {
    const arg = Object.defineProperty({}, 'passive', {
      get() {
        result = {
          passive: true,
        }
        return true
      },
    })

    window.addEventListener('testpassive', arg, arg)
    window.remove('testpassive', arg, arg)
  } catch (e) {
    /* */
  }

  window.__passiveEvt = result

  return result
}

export default passiveEventArg
