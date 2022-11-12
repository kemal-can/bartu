<template>
  <timeline-entry
    :resource-name="resourceName"
    :log="log"
    :icon="log.type.icon"
    :heading="$t('activity.timeline.heading')"
    heading-class="font-medium"
  >
    <div class="mt-3">
      <activity :activity="log" :resource-name="resourceName" />
    </div>
  </timeline-entry>
</template>
<script>
import TimelineEntry from './TimelineEntry'
import Activity from '@/views/Activity/RelatedRecordView'
export default {
  mixins: [TimelineEntry],
  components: { Activity },
}
</script>
