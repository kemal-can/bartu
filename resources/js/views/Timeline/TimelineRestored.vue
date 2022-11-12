<template>
  <timeline-entry :resource-name="resourceName" :log="log" icon="Refresh">
    <template #heading>
      <i18n-t
        scope="global"
        :keypath="resourceSingular + '.timeline.restored'"
        v-once
      >
        <template #causer>
          <span class="font-medium" v-text="log.causer_name"></span>
        </template>
      </i18n-t>
    </template>
  </timeline-entry>
</template>
<script>
import TimelineEntry from './TimelineEntry'
export default {
  mixins: [TimelineEntry],
}
</script>
