<template>
  <i-layout>
    <resource-trashed-table :resource-name="$route.params.resourceName" />
  </i-layout>
</template>
<script>
import ResourceTrashedTable from '@/components/Table/Trashed'
export default {
  components: { ResourceTrashedTable },
}
</script>
