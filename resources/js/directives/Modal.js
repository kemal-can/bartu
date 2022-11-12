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
    el._showModal = () => {
      Innoclapps.$emit('modal-show', binding.value)
    }
    el.addEventListener('click', el._showModal)
  },
  unmounted: function (el, binding, vnode) {
    el.removeEventListener('click', el._showModal)
  },
}
