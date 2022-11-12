<template>
  <div :class="['field-col', colClass]">
    <i-form-group
      :description="!isToggleable && displayHelpAsText ? field.helpText : null"
    >
      <a
        href="#"
        @click.prevent="toggleIsVisible = true"
        class="link flex text-sm"
        v-if="isToggleable"
      >
        <icon icon="Plus" class="h4 mr-1 w-4" /> {{ field.label }}
      </a>

      <div v-show="!isToggleable">
        <i-form-label
          :for="fieldId"
          class="mb-1 inline-flex items-center"
          v-if="field.label"
          :required="field.isRequired"
        >
          <icon
            icon="QuestionMarkCircle"
            class="mr-1 h-4 w-4 text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
            v-if="displayHelpAsIcon"
            v-i-tooltip="field.helpText"
          />
          <span v-html="field.label"></span>
        </i-form-label>

        <slot></slot>
      </div>
      <form-error v-if="form" :form="form" :field="field.attribute" />
    </i-form-group>
    <!-- Teleport usage -->
    <div :id="'after-' + field.attribute + '-field'"></div>
  </div>
</template>
<script>
import { isValueEmpty } from '@/utils'

export default {
  props: {
    form: Object,
    fieldId: String,
    field: {
      required: true,
      type: Object,
    },
  },
  data: () => ({
    toggleIsVisible: false,
  }),
  computed: {
    /**
     * Indicates whether the field is toggleable
     *
     * @return {Boolean}
     */
    isToggleable() {
      return this.field.toggleable && !this.toggleIsVisible && !this.hasValue
    },

    /**
     * Get the field col class
     *
     * @return {Array}
     */
    colClass() {
      return [
        this.colWidth,
        {
          hidden: this.field.displayNone,
        },
      ]
    },

    /**
     * Check whether to display the help text as icon with tooltip
     *
     * @return {Boolean}
     */
    displayHelpAsIcon() {
      return this.field.helpText && this.field.helpTextDisplay === 'icon'
    },

    /**
     * Check whether to display the help text as text with tooltip
     *
     * @return {Boolean}
     */
    displayHelpAsText() {
      return this.field.helpText && this.field.helpTextDisplay === 'text'
    },

    /**
     * Get the column wrapper width
     *
     * @return {Number|null}
     */
    colWidth() {
      if (this.field.colClass === false) {
        return
      }

      return this.field.colClass || 'col-span-12'
    },

    /**
     * Indicates whether the field has default value
     *
     * @return {Boolean}
     */
    hasValue() {
      return !isValueEmpty(this.field.value)
    },
  },
}
</script>
