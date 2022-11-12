<template>
  <timeline-entry
    :resource-name="resourceName"
    :log="log"
    icon="Phone"
    :heading="$t('call.timeline.heading')"
    heading-class="font-medium"
  >
    <call class="mt-3" :call="log" :resource-name="resourceName" collapsable />
  </timeline-entry>
</template>
<script>
import TimelineEntry from './TimelineEntry'
import Call from '@/views/Calls/View'
export default {
  mixins: [TimelineEntry],
  components: { Call },
}
</script>
