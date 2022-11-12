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
    /**
     * Determine if the user is using 12-hour time
     *
     * @return {Boolean}
     */
    usesTwelveHourTime() {
      return this.currentTimeFormat.indexOf('H:i') === -1
    },

    /**
     * Get the user's local timezone.
     */
    userTimezone() {
      return this.currentUser && this.currentUser.timezone
        ? this.currentUser.timezone
        : moment.tz.guess()
    },

    /**
     * Determine the application current time format
     * @return {String}
     */
    currentTimeFormat() {
      return this.currentUser
        ? this.currentUser.time_format
        : this.setting('time_format')
    },

    /**
     * Determine the application current date format
     * @return {String}
     */
    currentDateFormat() {
      return this.currentUser
        ? this.currentUser.date_format
        : this.setting('date_format')
    },

    /**
     * Converts the PHP options date format to moment compatible
     * @return {String}
     */
    dateTimeFormatForMoment() {
      return moment().PHPconvertFormat(
        this.currentDateFormat + ' ' + this.currentTimeFormat
      )
    },

    /**
     * Converts the PHP options date format to moment compatible
     * @return {String}
     */
    dateFormatForMoment() {
      return moment().PHPconvertFormat(this.currentDateFormat)
    },
  },
  methods: {
    /**
     * Convert the given localized date time string to the application's timezone.
     */
    dateToAppTimezone(value, format = 'YYYY-MM-DD HH:mm:ss') {
      return value
        ? moment
            .tz(value, this.userTimezone)
            .clone()
            .tz(Innoclapps.config.timezone)
            .format(format)
        : value
    },

    /**
     * Convert the given application timezone date time string to the local timezone.
     */
    dateFromAppTimezone(value, format = 'YYYY-MM-DD HH:mm:ss') {
      if (!value) {
        return value
      }

      return this.appMoment(value).clone().tz(this.userTimezone).format(format)
    },

    /**
     * Get the localized date by UTC/App default datetime
     */
    localizedDateTime(value, format) {
      if (!value) {
        return value
      }

      return this.appMoment(value)
        .clone()
        .tz(this.userTimezone)
        .format(format || this.dateTimeFormatForMoment)
    },

    /**
     * Get the localized date by UTC/App default date
     */
    localizedDate(value, format) {
      if (!value) {
        return value
      }

      return this.appMoment(value)
        .clone()
        .tz(this.userTimezone)
        .format(format || this.dateFormatForMoment)
    },

    /**
     * Get app date now with app timezone set
     * @return {Object}
     */
    appMoment(value) {
      return moment.tz(value, Innoclapps.config.timezone)
    },

    /**
     * Get current application time and date
     * @param  {String} format
     * @return {string}
     */
    appDate(format = 'YYYY-MM-DD HH:mm:ss') {
      return this.appMoment().format(format)
    },
  },
}
