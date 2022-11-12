<template>
  <call
    v-for="(call, index) in calls"
    :key="'call-' + call.id"
    :class="{ 'mt-4': index !== 0 }"
    :call="call"
    :resource-name="resourceName"
  />
</template>
<script>
import Call from './View'
export default {
  components: { Call },
  props: {
    calls: Array,
    resourceName: {
      required: true,
      type: String,
    },
  },
}
</script>
