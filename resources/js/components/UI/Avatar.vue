<template>
  <img :class="computedClasses" :src="src" />
</template>
<script>
const sizeMaps = {
  xs: 'h-5 w-5',
  sm: 'h-8 w-8',
  lg: 'h-10 w-10',
}

export default {
  name: 'IAvatar',
  props: {
    src: {
      type: String,
      required: true,
    },
    size: {
      default: 'sm',
      type: String,
      validator(value) {
        return ['xs', 'sm', 'lg'].includes(value)
      },
    },
  },
  computed: {
    computedClasses() {
      return ['inline-block rounded-full', sizeMaps[this.size]]
    },
  },
}
</script>
