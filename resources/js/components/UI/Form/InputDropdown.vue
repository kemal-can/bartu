<template>
  <i-dropdown
    ref="dropdown"
    auto-size="min"
    popper-class="bg-white rounded-md dark:bg-neutral-800"
  >
    <template #toggle>
      <div
        :style="{ width: width }"
        :class="['relative', { 'pointer-events-none': disabled }]"
      >
        <!-- On mobile pointer events are disabled to not open the keyboard on touch,
        in this case, the user will be able to select only from the dropdown provided values -->
        <i-form-input
          @click="inputClicked"
          @blur="inputBlur"
          :id="inputId"
          v-bind="$attrs"
          autocomplete="off"
          :class="[
            'pointer-events-none pr-8',
            { 'sm:pointer-events-auto': !disabled },
          ]"
          ref="input"
          :disabled="disabled"
          v-model="selectedItem"
          :placeholder="placeholder"
        />
        <icon
          icon="X"
          class="absolute right-3 top-2.5 h-5 w-5 cursor-pointer text-neutral-400 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
          v-show="selectedItem"
          @click="clearSelected"
        />
      </div>
    </template>
    <div
      :style="[
        {
          height: height,
          'overflow-y': maxHeight ? 'scroll' : null,
          'max-height': maxHeight || 'auto',
        },
      ]"
    >
      <i-dropdown-item
        v-for="(item, index) in items"
        :key="index"
        :active="isSelected(item)"
        @click="itemPicked(item)"
        :text="item"
      />
    </div>
  </i-dropdown>
</template>
<script>
export default {
  inheritAttrs: false,
  emits: ['update:modelValue', 'blur', 'cleared', 'shown'],
  name: 'InputDropdown',
  props: {
    width: String,
    height: String,
    maxHeight: String,

    inputId: {
      type: String,
      default: 'input-dropdown',
    },

    modelValue: String,
    placeholder: String,
    items: Array,
    disabled: Boolean,
  },
  watch: {
    selectedItem: function (newVal, oldVal) {
      if (newVal !== this.modelValue) {
        this.$emit('update:modelValue', newVal)
      }
    },
    modelValue: function (newVal, oldVal) {
      if (newVal !== this.selectedItem) {
        this.selectedItem = newVal
      }
    },
  },
  data() {
    return {
      isOpen: false,
      selectedItem: '',
    }
  },
  methods: {
    inputClicked(e) {
      this.openIfNeeded()
    },

    openIfNeeded() {
      if (!this.isOpen) {
        this.$refs.dropdown.show()
        this.isOpen = true
        this.$emit('shown')
      }
    },

    inputBlur(e) {
      // Allow timeout as if user  clicks on the dropdown item to have
      // a selected value in case @blur event is checking the value
      setTimeout(() => this.$emit('blur'), 500)
    },

    itemPicked(item) {
      this.closePicker()
      this.selectedItem = item
    },

    closePicker() {
      this.$refs.dropdown.hide()
      this.isOpen = false
    },

    isSelected(item) {
      return item === this.selectedItem
    },

    clearSelected() {
      this.selectedItem = ''
      this.$emit('cleared')
      this.$refs.input.focus()
      this.openIfNeeded()
    },
  },
  created() {
    this.selectedItem = this.modelValue
  },
}
</script>
