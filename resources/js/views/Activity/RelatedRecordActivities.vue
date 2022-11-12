<template>
  <div
    v-for="(activity, index) in activities"
    :class="{ 'mt-4': index !== 0 }"
    :key="activity.id"
  >
    <activity :activity="activity" :resource-name="resourceName" />
  </div>
</template>
<script>
import Activity from './RelatedRecordView'
export default {
  components: { Activity },
  props: {
    activities: Array,
    resourceName: { type: String, required: true },
  },
}
</script>
