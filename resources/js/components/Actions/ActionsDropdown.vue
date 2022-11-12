<template>
  <dropdown-select
    :items="actions"
    v-model="selectedAction"
    placement="bottom-end"
    @change="run"
    :label="$t('actions.actions')"
    label-key="name"
    value-key="uriKey"
  />
</template>
<script>
import RunsAction from './RunsAction'
export default {
  emits: ['run'],
  mixins: [RunsAction],
}
</script>
