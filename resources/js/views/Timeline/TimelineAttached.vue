<template>
  <timeline-entry :resource-name="resourceName" :log="log" icon="Plus">
    <template #heading>
      <i18n-t
        scope="global"
        :keypath="log.properties.lang.key"
        :user="log.causer_name"
        v-once
      >
        <template #user>
          <span class="font-medium" v-text="log.causer_name"></span>
        </template>
      </i18n-t>
      <router-link
        class="link ml-1 font-medium"
        v-once
        :to="log.properties.path"
      >
        {{ log.properties.name }}
      </router-link>
    </template>
  </timeline-entry>
</template>

<script>
import TimelineEntry from './TimelineEntry'
export default {
  mixins: [TimelineEntry],
}
</script>
