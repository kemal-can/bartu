<template>
  <timeline-entry
    :resource-name="resourceName"
    :log="log"
    icon="MenuAlt2"
    :heading="$t('form.submission')"
    heading-class="font-medium"
  >
    <i-card class="mt-2" v-once>
      <div class="space-y-2">
        <div v-for="(property, index) in log.properties" :key="index">
          <div
            class="inline text-sm font-semibold text-neutral-800 dark:text-neutral-200"
          >
            {{ resources[property.resourceName].singularLabel }} /
            <span class="inline-block font-medium" v-html="property.label" />
          </div>
          <div class="text-sm text-neutral-600 dark:text-neutral-400">
            <span v-if="property.value === null" v-text="'/'" />
            <span v-else v-text="maybeFormatDateValue(property.value)" />
          </div>
        </div>
      </div>
    </i-card>
  </timeline-entry>
</template>
<script>
import TimelineEntry from './TimelineEntry'
export default {
  mixins: [TimelineEntry],
  data: () => ({
    resources: Innoclapps.config.resources,
  }),
}
</script>
