<template>
  <i-button @click="$emit('click', $event)" :icon="icon" v-bind="$attrs" />
</template>
<script>
export default {
  inheritAttrs: false,
  emits: ['click'],
  props: {
    icon: { default: 'X', type: String },
  },
}
</script>
