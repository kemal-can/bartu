<template>
  <i-layout full>
    <div class="mt-20 flex flex-col flex-wrap content-center">
      <div class="flex flex-col items-center">
        <icon icon="QuestionMarkCircle" class="h-20 w-20 text-primary-300" />
        <h1
          class="mt-6 mb-2 text-3xl font-bold text-neutral-900 dark:text-white"
        >
          Oops!
        </h1>
        <h2 class="text-2xl text-neutral-700 dark:text-neutral-100">
          Page not found
        </h2>
      </div>

      <div class="flex flex-col justify-center">
        <p class="mt-5 text-neutral-700 dark:text-neutral-300">
          The page you are trying to view does not exists or you don't have
          permissions to access it.
        </p>
        <div class="mt-5 text-center">
          <i-button
            :to="{ name: 'dashboard' }"
            size="lg"
            v-text="'Go to dashboard'"
          ></i-button>
        </div>
      </div>
    </div>
  </i-layout>
</template>
<script>
export default {
  created() {
    this.setPageTitle('404')
  },
}
</script>
