<template>
  <div
    class="flex items-center justify-between"
    v-show="collection.hasPagination"
  >
    <div class="flex flex-1 justify-between sm:hidden">
      <i-button
        variant="white"
        @click="collection.previousPage()"
        :disabled="!collection.hasPreviousPage || loading"
      >
        {{ $t('pagination.previous') }}
      </i-button>

      <i-button
        variant="white"
        @click="collection.nextPage()"
        :disabled="!collection.hasNextPage || loading"
      >
        {{ $t('pagination.next') }}
      </i-button>
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p
          class="text-sm text-neutral-700 dark:text-neutral-200"
          v-t="{
            path: 'table.info',
            args: { from: showingFrom, to: showingTo, total: showingTotal },
          }"
        ></p>
      </div>
      <div>
        <nav
          class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm"
          aria-label="Pagination"
          v-if="collection.hasPagination"
        >
          <template v-if="collection.shouldRenderLinks">
            <a
              href="#"
              @click.prevent="collection.previousPage()"
              :class="{
                'pointer-events-none opacity-60':
                  !collection.hasPreviousPage || loading,
              }"
              class="relative inline-flex items-center rounded-l-md border border-neutral-300 bg-white py-1.5 px-2 text-sm font-medium text-neutral-500 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
            >
              <span class="sr-only">Previous</span>
              <icon icon="ChevronLeft" class="h-5 w-5" />
            </a>
            <!-- Current: "z-10 bg-primary-50 border-primary-500 text-primary-600", Default: "bg-white border-neutral-300 text-neutral-500 hover:bg-neutral-50" -->
            <template
              v-for="(page, index) in collection.pagination"
              :key="index"
            >
              <span
                v-if="page === '...'"
                class="relative inline-flex items-center border border-neutral-300 bg-white px-4 py-1.5 text-sm font-medium text-neutral-700 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300"
              >
                ...
              </span>

              <a
                v-else
                href="#"
                aria-current="page"
                @click.prevent="collection.page(page)"
                :class="[
                  'relative inline-flex items-center border px-4 py-1.5 text-sm font-medium',
                  collection.isCurrentPage(page)
                    ? 'z-10 border-primary-500 bg-primary-50 text-primary-600 dark:border-primary-500 dark:bg-primary-500 dark:text-white'
                    : 'border-neutral-300 bg-white text-neutral-500 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700',
                  {
                    'pointer-events-none opacity-60': loading,
                  },
                ]"
              >
                {{ page }}
              </a>
            </template>
            <a
              href="#"
              @click.prevent="collection.nextPage()"
              :class="[
                'relative inline-flex items-center rounded-r-md border border-neutral-300 bg-white py-1.5 px-2 text-sm font-medium text-neutral-500 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700',
                {
                  'pointer-events-none opacity-60':
                    !collection.hasNextPage || loading,
                },
              ]"
            >
              <span class="sr-only">Next</span>
              <icon icon="ChevronRight" class="h-5 w-5" />
            </a>
          </template>
        </nav>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    collection: {
      type: Object,
      required: true,
    },
    loading: {
      type: Boolean,
      required: false,
    },
  },
  computed: {
    showingFrom() {
      return this.collection.from
    },
    showingTo() {
      return this.collection.to
    },
    showingTotal() {
      return this.collection.total
    },
  },
}
</script>
