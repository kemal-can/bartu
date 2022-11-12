<template>
  <i-form-group
    v-if="placeholders && placeholders.length > 0"
    :label="$t('mail_template.placeholders.placeholders')"
  >
    <div
      class="rounded-md border border-neutral-200 bg-neutral-100 p-4 text-neutral-700 dark:border-neutral-500 dark:bg-neutral-700 dark:text-neutral-200"
    >
      <div class="max-h-96 overflow-y-auto">
        <p
          class="mb-1 text-sm"
          v-for="placeholder in placeholders"
          :key="placeholder.tag"
        >
          <span class="select-all font-semibold">
            {{ placeholder.interpolation_start }}
            {{ placeholder.tag }}
            {{ placeholder.interpolation_end }}
          </span>
          <span v-show="placeholder.description">
            - {{ placeholder.description }}</span
          >
        </p>
      </div>
    </div>
  </i-form-group>
</template>
<script>
export default {
  props: {
    placeholders: Array,
  },
}
</script>
