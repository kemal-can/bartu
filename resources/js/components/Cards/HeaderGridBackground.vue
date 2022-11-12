<template>
  <div class="absolute flex flex-col items-center overflow-hidden">
    <div
      class="absolute inset-0 grid"
      style="
        grid-template-rows: repeat(auto-fill, 31px);
        grid-template-columns: repeat(16, 1fr);
      "
    >
      <div
        class="w-36 bg-primary-200 dark:bg-primary-500"
        style="grid-area: 4 / 15 / auto / span 2"
      ></div>
      <div
        style="
          box-shadow: rgb(227, 232, 238) 0px 0px 0px 2px inset;
          grid-area: 5 / 14 / auto / span 1;
        "
        class="bg-neutral-100 dark:bg-neutral-300"
      ></div>
      <div
        style="grid-area:6 / 16 / auto / span 1;/* border-top-left-radius:50px;"
        class="bg-primary-200 dark:bg-primary-500"
      ></div>
      <div
        style="grid-area: 3 / 15 / auto / span 1"
        class="bg-primary-200 dark:bg-primary-400"
      ></div>
      <div
        class="w-36 bg-primary-300 dark:bg-primary-400"
        style="grid-area: 3 / 14 / auto / span 2"
      ></div>
    </div>
  </div>
</template>
<script>
export default {}
</script>
