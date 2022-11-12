<template>
  <div
    class="table-responsive"
    :style="{ maxHeight: maxHeight }"
    :class="[
      wrapperClass,
      {
        'table-sticky-header': sticky,
      },
    ]"
  >
    <div
      v-bind="$attrs.style"
      :class="[
        $attrs.class,
        { shadow: shadow },
        {
          'border-x border-b border-neutral-200 dark:border-neutral-800':
            bordered,
        },
      ]"
    >
      <table
        class="table-primary"
        v-bind="tableAttrs"
        :class="{ 'border-separate': sticky }"
        :style="{ borderSpacing: sticky ? 0 : undefined }"
      >
        <slot></slot>
      </table>
    </div>
  </div>
</template>
<script>
export default {
  inheritAttrs: false,
  props: {
    maxHeight: String,
    wrapperClass: [String, Object, Array],
    shadow: { default: true, type: Boolean },
    bordered: { default: false, type: Boolean },
    sticky: { default: false, type: Boolean },
  },
  computed: {
    tableAttrs() {
      const result = { ...this.$attrs }
      delete result.class
      delete result.style
      return result
    },
  },
}
</script>
