<template>
  <i-card>
    <template #header>
      <i-card-heading>
        Zapier
        <i-badge variant="info" class="ml-2">Beta</i-badge>
      </i-card-heading>
    </template>

    <div class="p-3 text-center">
      <img
        src="https://cdn.zapier.com/zapier/images/logos/zapier-logo.png"
        class="mx-auto h-16 w-auto"
      />
      <p class="mt-5 text-sm text-neutral-700 dark:text-white">
        Zapier integration is at <b>"Invite Only"</b> and
        <b>"Testing"</b> stage, we are inviting you to test the integration
        before it's available for everyone.
      </p>
      <p class="mt-1 text-sm text-neutral-700 dark:text-white">
        Before this, <b>we need to verify your purchase key</b> and after that
        we will share the Zapier invite link with you can try it!
      </p>
    </div>
    <div class="m-auto max-w-2xl">
      <div class="mt-5 flex justify-center rounded-md shadow-sm">
        <div class="flex grow items-stretch focus-within:z-10">
          <i-form-input
            :rounded="false"
            id="purchase-key"
            v-model="purchaseKey"
            class="form-input rounded-l-md border-neutral-300"
            placeholder="Enter your purchase key here"
          />
        </div>
        <i-button
          class="relative -ml-px shrink-0 rounded-r-md"
          variant="white"
          :rounded="false"
          @click="getLink"
        >
          Get Integration Link
        </i-button>
      </div>
    </div>
    <div
      class="mt-6 flex items-center justify-center text-neutral-800 dark:text-neutral-300"
      v-if="link"
    >
      <span class="select-all font-medium">{{ link }}</span>
      <copy-button class="ml-3" :text="link" />
    </div>
  </i-card>
</template>
<script>
import axios from 'axios'
export default {
  data: () => ({
    link: null,
    purchaseKey: Innoclapps.config.purchase_key,
  }),
  methods: {
    /**
     * Get the Zapier Link
     *
     * Uses separate axios instance to prevent collision
     * with application error codes alerts and redirects
     *
     * @return {Void}
     */
    getLink() {
      axios
        .get('https://www.bartucrm.com/zapier-link/' + this.purchaseKey, {
          withCredentials: true,
        })
        .then(({ data }) => {
          this.link = data.link
        })
        .catch(error => {
          Innoclapps.error(error.response.data.error)
        })
    },
  },
}
</script>
