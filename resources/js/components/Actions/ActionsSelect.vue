<template>
  <i-custom-select
    @option:selected="run"
    v-model="selectedAction"
    :searchable="false"
    :clearable="false"
    :loading="actionRunInProgress"
    :options="actions"
    :placeholder="placeholder"
    label="name"
  />
</template>
<script>
import RunsAction from './RunsAction'
export default {
  emits: ['run'],
  mixins: [RunsAction],
  computed: {
    /**
     * Get the number of total selected records
     *
     * @return {Number}
     */
    totalRecords() {
      return this.actionIds.length
    },

    /**
     * Select placeholder
     *
     * @return {String}
     */
    placeholder() {
      return (
        this.$t('actions.select') +
        ' (' +
        this.$t('actions.records_count', { count: this.totalRecords }) +
        ')'
      )
    },
  },
}
</script>
