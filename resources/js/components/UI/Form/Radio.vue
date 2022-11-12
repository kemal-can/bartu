<template>
  <div class="flex items-start">
    <input
      :id="id"
      :name="name"
      type="radio"
      v-model="localModelValue"
      :value="value"
      :disabled="disabled"
      @change="handleChangeEvent"
      class="form-radio dark:bg-neutral-700"
    />
    <i-form-label :for="id" class="ml-2 -mt-0.5">
      <slot>{{ label }}</slot>
    </i-form-label>
    <i-form-text :id="id + 'description'" v-if="description">
      {{ description }}
    </i-form-text>
  </div>
</template>
<script>
import { randomString } from '@/utils'
import isArray from 'lodash/isArray'
import clone from 'lodash/clone'
import findIndex from 'lodash/findIndex'

export default {
  emits: ['update:modelValue', 'change'],
  props: {
    name: String,
    label: String,
    description: String,
    modelValue: {},
    value: {},
    disabled: Boolean,
    id: {
      type: [String, Number],
      default() {
        return randomString(10)
      },
    },
  },
  data: () => ({
    localModelValue: null,
  }),
  watch: {
    modelValue: function (newVal, oldVal) {
      this.localModelValue = newVal
    },
  },
  methods: {
    handleChangeEvent(e) {
      this.$emit('update:modelValue', e.target.value)
      this.$emit('change', e.target.value)
    },
  },
  mounted() {
    this.localModelValue = this.modelValue
  },
}
</script>
