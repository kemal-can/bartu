<template>
  <div class="mb-3">
    <slot name="label">
      <i-form-label
        :for="labelFor"
        :label="label"
        :required="required"
        class="mb-1"
        v-if="!optional"
      />
      <div class="flex" v-else>
        <i-form-label class="mb-1 grow" :for="labelFor" :label="label" />

        <span
          class="mb-px self-end text-xs text-neutral-600 dark:text-neutral-200"
          v-t="'app.optional'"
        />
      </div>
    </slot>
    <slot></slot>
    <i-form-text v-if="description" v-text="description"></i-form-text>
  </div>
</template>
<script>
export default {
  props: {
    label: String,
    labelFor: String,
    description: String,
    optional: { default: false, type: Boolean },
    required: { default: false, type: Boolean },
  },
}
</script>
