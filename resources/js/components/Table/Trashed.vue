<template>
  <i-alert class="mb-6 border border-info-200">
    {{ $t('app.soft_deletes.trashed_pruning_info', { total: prune_after }) }}
  </i-alert>
  <resource-table
    :url-path="`/${resourceName}/table`"
    :table-id="`${resourceName}-trashed`"
    :data-request-query-string="{ trashed: 1 }"
    :action-request-query-string="{ trashed: 1 }"
    :resource-name="resourceName"
  />
</template>
<script>
import ResourceTable from './Table'
export default {
  components: { ResourceTable },
  data: () => ({
    prune_after: Innoclapps.config.soft_deletes.prune_after,
  }),
  props: {
    resourceName: {
      required: true,
      type: String,
    },
  },
}
</script>
