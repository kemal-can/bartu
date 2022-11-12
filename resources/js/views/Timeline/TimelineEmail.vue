<template>
  <timeline-entry
    :resource-name="resourceName"
    :log="log"
    icon="Mail"
    :heading="$t('inbox.message')"
    heading-class="font-medium"
  >
    <template #date>
      {{ localizedDateTime(log.date) }}
    </template>
    <div class="mt-3">
      <email :email="log" :resource-name="resourceName" />
    </div>
  </timeline-entry>
</template>
<script>
import Email from '@/views/Emails/View'
import TimelineEntry from './TimelineEntry'
export default {
  mixins: [TimelineEntry],
  components: { Email },
}
</script>
