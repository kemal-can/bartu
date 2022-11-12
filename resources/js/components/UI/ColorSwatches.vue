<template>
  <div class="inline-flex flex-wrap">
    <button
      type="button"
      v-for="color in localWatches"
      :key="color"
      @click="selectColor(color)"
      :class="{ 'ring ring-offset-1': color === modelValueLowerCase }"
      class="mr-1 mb-2 flex h-8 w-8 items-center justify-center rounded"
      :style="{
        backgroundColor: color,
      }"
    >
      <icon
        icon="Check"
        v-if="color === modelValueLowerCase"
        class="h-5 w-5"
        :style="{ color: getContrast(color) }"
      />
    </button>
    <i-popover placement="top">
      <button
        type="button"
        class="mr-1 mb-2 flex h-8 w-8 items-center justify-center rounded border border-neutral-300 bg-neutral-100 hover:bg-neutral-300"
        v-show="allowCustom"
        @click="removeRequested"
      >
        <icon
          icon="ColorSwatch"
          class="h-5 w-5"
          :style="{ color: isCustomColorSelected ? modelValue : null }"
        />
      </button>
      <template #popper>
        <div class="w-60 py-2">
          <i-form-input type="color" @input="customColorInputEventHandler" />
        </div>
      </template>
    </i-popover>
    <button
      type="button"
      class="mr-1 mb-2 flex h-8 w-8 items-center justify-center rounded border border-neutral-300 bg-neutral-100 hover:bg-neutral-300"
      v-show="allowRemove && modelValue"
      @click="removeRequested"
    >
      <icon icon="X" class="h-5 w-5" />
    </button>
  </div>
</template>
<script>
import { getContrast } from '@/utils'
import debounce from 'lodash/debounce'
import map from 'lodash/map'
export default {
  emits: ['update:modelValue', 'input', 'remove-requested'],
  props: {
    modelValue: String,
    allowRemove: {
      default: true,
      type: Boolean,
    },
    allowCustom: {
      default: true,
      type: Boolean,
    },
    swatches: {
      type: Array,
    },
  },
  computed: {
    localWatches() {
      return map(this.swatches, color => color.toLowerCase())
    },
    modelValueLowerCase() {
      if (!this.modelValue) {
        return null
      }

      return this.modelValue.toLowerCase()
    },
    /**
     * Indicates whether custom color is selected from the picker
     *
     * @return {Boolean}
     */
    isCustomColorSelected() {
      if (!this.modelValue || !this.allowCustom) {
        return false
      }

      return (
        this.localWatches.filter(color => color === this.modelValueLowerCase)
          .length === 0
      )
    },
  },
  methods: {
    getContrast,
    customColorInputEventHandler: debounce(function (value) {
      this.selectColor(value)
    }, 500),

    /**
     * Select the given color
     *
     * @param  {String|null} value
     *
     * @return {Void}
     */
    selectColor(value) {
      this.$emit('update:modelValue', value)
      this.$emit('input', value)
    },

    /**
     * Hande remove color request
     *
     * @return {Void}
     */
    removeRequested() {
      this.selectColor(null)
      this.$emit('remove-requested')
    },
  },
}
</script>
