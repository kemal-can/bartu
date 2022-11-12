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
  props: {
    /**
     * Sets the id of the input element.
     */
    inputId: String,

    /**
     * Set the tabindex for the input field.
     */
    tabindex: Number,

    /**
     * Value of the 'autocomplete' field of the input element.
     */
    autocomplete: {
      type: String,
      default: 'off',
    },

    /**
     * Equivalent to the `placeholder` attribute on an `<input>`.
     */
    placeholder: {
      type: String,
      default: '',
    },
  },
}
