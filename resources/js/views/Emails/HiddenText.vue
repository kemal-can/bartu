<template>
  <div v-show="text">
    <i-button
      :size="false"
      variant="white"
      class="mt-4 inline-flex rounded-md py-0 px-1.5"
      icon="DotsHorizontal"
      v-i-tooltip="$t('mail.trimmed_content')"
      v-i-toggle="textId"
    />
    <div class="mt-5 hidden" :id="textId">
      <div v-html="text"></div>
    </div>
  </div>
</template>
<script>
import { randomString } from '@/utils'
export default {
  props: {
    text: {
      required: true,
    },
  },
  data: () => ({
    textId: randomString(10),
  }),
}
</script>
