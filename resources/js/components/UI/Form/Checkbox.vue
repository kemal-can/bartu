<template>
  <div class="flex items-start">
    <div class="flex h-5 items-center">
      <input
        :id="id"
        :name="name"
        type="checkbox"
        :disabled="disabled"
        :checked="isChecked"
        :value="value"
        @change="handleChangeEvent"
        class="form-check dark:bg-neutral-700"
      />
    </div>
    <div class="ml-2">
      <i-form-label :for="id">
        <slot>
          {{ label }}
        </slot>
      </i-form-label>
      <i-form-text :id="id + 'description'" v-if="description">
        {{ description }}
      </i-form-text>
    </div>
  </div>
</template>
<script>
import { randomString } from '@/utils'
import isArray from 'lodash/isArray'
import clone from 'lodash/clone'
import findIndex from 'lodash/findIndex'

export default {
  emits: ['update:checked', 'change'],
  props: {
    name: String,
    label: String,
    description: String,
    checked: [Array, String, Boolean, Number],
    value: {
      default: true,
    },
    id: {
      type: String,
      default() {
        return randomString(10)
      },
    },
    uncheckedValue: {
      default: false,
    },
    disabled: Boolean,
  },
  computed: {
    isChecked() {
      if (isArray(this.checked)) {
        return (
          Boolean(
            this.checked.filter(
              value => String(value) === String(this.value)
            )[0]
          ) || false
        )
      }

      return this.checked == this.value
    },
  },
  methods: {
    handleChangeEvent(e) {
      const checked = this.checked
      const isChecked = e.target.checked
      if (isArray(checked)) {
        let cloneChecked = clone(checked)

        if (isChecked) {
          cloneChecked.push(this.value)
        } else {
          cloneChecked.splice(
            findIndex(
              cloneChecked,
              value => String(value) === String(this.value)
            ),
            1
          )
        }

        this.$emit('update:checked', cloneChecked)
        this.$emit('change', cloneChecked)
      } else {
        let value = isChecked === true ? this.value : this.uncheckedValue
        this.$emit('update:checked', value)
        this.$emit('change', value)
      }
    },
  },
}
</script>
