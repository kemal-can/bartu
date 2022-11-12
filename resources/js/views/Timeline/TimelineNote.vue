<template>
  <timeline-entry
    :resource-name="resourceName"
    :log="log"
    icon="PencilAlt"
    :heading="$t('note.timeline.heading')"
    heading-class="font-medium"
  >
    <note class="mt-3" :note="log" :resource-name="resourceName" collapsable />
  </timeline-entry>
</template>
<script>
import Note from '@/views/Notes/View'
import TimelineEntry from './TimelineEntry'
export default {
  mixins: [TimelineEntry],
  components: { Note },
}
</script>
