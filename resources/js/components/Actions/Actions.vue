<template>
  <component
    v-if="hasActionsAvailable"
    :is="'action-' + type"
    :action-request-query-string="actionRequestQueryString"
    :actions="filteredActions"
    :ids="ids"
    :view="view"
    @run="$emit('run', $event)"
    :resource-name="resourceName"
  />
</template>
<script>
import ActionSelect from './ActionsSelect'
import ActionDropdown from './ActionsDropdown'
import reject from 'lodash/reject'
import { props } from './RunsAction'
export default {
  emits: ['run'],
  components: {
    ActionSelect,
    ActionDropdown,
  },
  props: {
    ...props,
    type: {
      required: true,
      type: String,
    },
  },
  computed: {
    /**
     * Indicates whether there are actions available
     *
     * @return {Boolean}
     */
    hasActionsAvailable() {
      return this.filteredActions.length > 0
    },

    /**
     * Filtered actions for the view
     *
     * @return {Array}
     */
    filteredActions() {
      return reject(this.actions, action => {
        if (this.view === 'update' && action.hideOnUpdate === true) {
          return true
        } else if (this.view === 'index' && action.hideOnIndex === true) {
          return true
        }
        return false
      })
    },
  },
}
</script>
