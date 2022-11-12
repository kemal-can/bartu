<template>
  <component
    :field="section.field"
    :form="form"
    view="internal"
    :is="section.field.component"
  />
</template>
<script>
import Section from './Section'
export default {
  mixins: [Section],
}
</script>
