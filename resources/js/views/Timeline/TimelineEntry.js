/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import { singularize, isISODate, isStandardDateTime, isDate } from '@/utils'

// remove TimelineEntryPin
import TimelineEntryPin from './TimelineEntryPin'
import TimelineEntry from './TimelineTemplate'

export default {
  components: {
    TimelineEntry,
    TimelineEntryPin,
  },
  props: {
    log: {
      type: Object,
      required: true,
    },
    resourceName: {
      type: String,
      required: true,
    },
    resourceRecord: {
      type: Object,
      required: true,
    },
  },
  computed: {
    /**
     * Get the resource singular name
     *
     * @return {String}
     */
    resourceSingular() {
      return singularize(this.resourceName)
    },
  },
  methods: {
    /**
     * If given value is date, format
     *
     * @param  {mixed} value
     *
     * @return {mixed}
     */
    maybeFormatDateValue(value) {
      if (isDate(value)) {
        return this.localizedDate(value)
      } else if (isStandardDateTime(value)) {
        return this.localizedDateTime(value)
      } else if (isISODate(value)) {
        // Timeline entry that should be formatted as date only
        // e.q. 2020-10-31T00:00:00.000000Z - expected close date on deal or custom fields of type date
        if (/T00:00:00.000000Z$/.test(value)) {
          return this.localizedDate(value)
        }

        return this.localizedDateTime(value)
      }

      return value
    },
  },
}
