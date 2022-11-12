<template>
  <i-layout full>
    <div class="mt-20 flex flex-col flex-wrap content-center">
      <div class="flex flex-col items-center">
        <icon icon="ShieldExclamation" class="h-20 w-20 text-primary-300" />
        <h1
          class="mt-6 mb-2 text-3xl font-bold text-neutral-900 dark:text-white"
        >
          403!
        </h1>
        <h2 class="text-2xl text-neutral-700 dark:text-neutral-100">
          Hang on...
        </h2>
      </div>

      <div class="flex flex-col justify-center">
        <p class="mt-5 text-center text-neutral-700 dark:text-neutral-200">
          {{
            errorMessage ||
            'You are not authorized to perform this action or view this page.'
          }}
        </p>
        <div class="mt-5 text-center">
          <i-button
            to="/dashboard"
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
  props: {
    errorMessage: String,
  },
  created() {
    this.setPageTitle('403')
  },
}
</script>
