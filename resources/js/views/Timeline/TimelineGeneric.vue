<template>
  <timeline-entry
    :resource-name="resourceName"
    :log="log"
    :icon="log.properties.icon || 'User'"
    :heading="$t(log.properties.lang.key, langAttributes)"
  />
</template>

<script>
import TimelineEntry from './TimelineEntry'
import get from 'lodash/get'
export default {
  mixins: [TimelineEntry],
  computed: {
    langAttributes() {
      // Create new object of the attributes
      // because we are mutating the store below
      let attributes = this.log.properties.lang.attrs

      if (!attributes) {
        return null
      }

      // Automatically add causer_name in case user attr is
      // provided with null value or the lang key has :user attribute but
      // user attribute is not provided
      if (
        (get(
          this.$i18n.messages[this.$i18n.locale],
          this.log.properties.lang.key
        ).indexOf('{user}') > -1 &&
          Object.keys(attributes).indexOf('user') === -1) ||
        (Object.keys(attributes).indexOf('user') > -1 &&
          attributes['user'] === null)
      ) {
        // To avoid mutations errors, assign new object
        attributes = Object.assign({}, attributes, {
          user: this.log.causer_name,
        })
      }

      return attributes
    },
  },
}
</script>
