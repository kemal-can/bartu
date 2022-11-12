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
  data: () => ({
    mutableLoading: false,
    // Initialy when the table is loaded first time the loader is not shown
    initialDataLoaded: false,
  }),
  computed: {
    isLoading() {
      return this.mutableLoading === true
    },
  },
  methods: {
    /**
     * (Action) Set Loading State.
     * @param value @type {{*}}
     * @return void
     */
    loading(value = true) {
      this.mutableLoading = value
    },
  },
}
