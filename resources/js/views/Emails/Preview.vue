<template>
  <div class="mail-text all-revert" v-once>
    <div class="font-sans text-sm dark:text-white">
      <div v-html="message.visible_text"></div>
      <hidden-text :text="message.hidden_text" />
    </div>
  </div>
</template>
<script>
import HiddenText from '@/views/Emails/HiddenText'
export default {
  components: { HiddenText },
  props: {
    message: { type: Object, required: true },
  },
}
</script>
