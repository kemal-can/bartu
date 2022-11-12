<template>
  <router-link
    :key="'row-' + column.attribute + '-' + index"
    :to="presentable.path"
    v-for="(presentable, index) in presentables"
  >
    <i-badge variant="neutral" v-text="presentable.display_name"> </i-badge>
  </router-link>
</template>
<script>
import TableData from './TableData'
import castArray from 'lodash/castArray'
export default {
  mixins: [TableData],
  computed: {
    presentables() {
      return castArray(this.row[this.column.attribute] || [])
    },
  },
}
</script>
