<template>
  <div
    :class="{ 'mt-3': index !== 0 }"
    v-for="(email, index) in emails"
    :key="email.id"
  >
    <email :email="email" :resource-name="resourceName" />
  </div>
</template>
<script>
import Email from './View'
export default {
  components: { Email },
  props: {
    emails: Array,
    resourceName: {
      required: true,
      type: String,
    },
  },
}
</script>
