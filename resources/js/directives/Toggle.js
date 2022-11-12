/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
export default {
  beforeMount: function (el, binding, vnode) {
    el._toggle = e => {
      const toggleElement = document.getElementById(binding.value)
      if (
        toggleElement.style.display === 'none' ||
        toggleElement.classList.contains('hidden')
      ) {
        toggleElement.style.display = 'block'
        toggleElement.classList.remove('hidden')
      } else {
        toggleElement.style.display = 'none'
      }
    }

    el.addEventListener('click', el._toggle)
  },
  unmounted: function (el, binding, vnode) {
    el.removeEventListener('click', el._toggle)
  },
}
