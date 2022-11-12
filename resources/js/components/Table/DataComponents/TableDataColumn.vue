<template>
  <span v-if="!column.asHtml" v-text="formatted" />
  <span v-else v-html="formatted" />
</template>
<script>
import TableData from './TableData'
export default {
  mixins: [TableData],
}
</script>
