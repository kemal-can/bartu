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
  computed: {
    draggableOptions() {
      return {
        delay: 15,
        delayOnTouchOnly: true,
        animation: 0,
        disabled: false,
        ghostClass: 'drag-ghost',
      }
    },
    scrollableDraggableOptions() {
      return {
        scroll: true,
        scrollSpeed: 50,
        forceFallback: true,
        ...this.draggableOptions,
      }
    },
  },
}
