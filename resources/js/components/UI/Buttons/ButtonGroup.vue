<template>
  <span class="i-button-group relative z-0 inline-flex rounded-md shadow-sm">
    <slot></slot>
  </span>
</template>
<script>
export default {
  name: 'IButtonGroup',
}
</script>
<style>
.i-button-group > button:first-child,
.i-button-group .v-popper:first-child button {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  border-right: 0;
}

.i-button-group > button:last-child,
.i-button-group .v-popper:last-child > button {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

/* All children except first and last */
.i-button-group > button:nth-child(1n + 2):not(:last-child),
.i-button-group .v-popper:nth-child(1n + 2):not(:last-child) button {
  border-radius: 0;
  border-right: 0;
}
</style>
