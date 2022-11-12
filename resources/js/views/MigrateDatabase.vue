<template>
  <div class="pt-8">
    <div class="mx-auto max-w-3xl">
      <div class="bg-white shadow dark:bg-neutral-900 sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="sm:flex sm:items-start sm:justify-between">
            <div>
              <h3
                class="text-lg font-medium leading-6 text-neutral-900 dark:text-neutral-100"
              >
                Database update required
              </h3>
              <div
                class="mt-2 max-w-xl text-sm text-neutral-500 dark:text-neutral-300"
              >
                <p>
                  The application detected that database update is required,
                  click the Update button on the right to perform the update.
                </p>
              </div>
            </div>
            <div
              class="mt-5 sm:mt-1 sm:ml-6 sm:flex sm:shrink-0 sm:items-center"
            >
              <form @submit.prevent="migrate">
                <i-button
                  variant="primary"
                  type="submit"
                  :loading="migrating"
                  :disabled="migrating"
                  >Update</i-button
                >
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
const migrating = ref(false)

function migrate() {
  migrating.value = true
  Innoclapps.request()
    .get('/tools/migrate')
    .then(() => {
      window.location.href = Innoclapps.config.url
    })
    .finally(() => (migrating.value = false))
}
</script>
