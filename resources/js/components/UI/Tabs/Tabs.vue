<template>
  <div class="sm:hidden" v-if="!responsive">
    <label for="tabs" class="sr-only">Select a tab</label>
    <i-form-select
      class="mb-2 bg-neutral-50"
      :model-value="activeTab"
      @change="selectTab($event)"
    >
      <option
        :value="tab.value.tabId"
        v-for="tab in tabs"
        :key="tab.value.title"
      >
        {{ tab.value.title }}
        {{ tab.value.badge ? ' (' + tab.value.badge + ')' : '' }}
      </option>
    </i-form-select>
  </div>
  <div :class="[navWrapperClass, { hidden: !responsive }, 'sm:block']">
    <div
      :class="[
        border ? 'border-b border-neutral-200 dark:border-neutral-600' : '',
        { 'border-b': border === 'b' },
        { 'border-y': border === 'y' },
        { 'border-t': border === 't' },
      ]"
    >
      <div class="flex items-center" :class="{ 'justify-center': centered }">
        <a
          href="#"
          @click.prevent="scrollLeft"
          :class="{ 'pointer-events-none opacity-50': scrolledToFirstTab }"
          class="block px-1 text-neutral-500 dark:text-neutral-200 sm:hidden"
          ><icon icon="ChevronLeft" class="h-6 w-6"
        /></a>
        <nav
          ref="nav"
          class="overlow-y-hidden -mb-px flex grow snap-x snap-mandatory overflow-x-auto sm:space-x-4 lg:space-x-6"
          :class="[{ 'justify-around': fill, 'sm:grow-0': !fill }, navClass]"
          aria-label="Tabs"
        >
          <a
            v-for="(tab, idx) in tabs"
            :key="tab.value.title"
            :ref="'tab-' + idx"
            @click.prevent="selectTab(tab.value.tabId)"
            href="#"
            :class="[
              tab.value.isActive
                ? 'border-primary-500 text-primary-600 dark:border-primary-400 dark:text-primary-300'
                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-100 dark:hover:border-neutral-500 dark:hover:text-neutral-300',
              'group inline-flex min-w-full shrink-0 snap-start snap-always items-center justify-center whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium sm:min-w-0',
              tab.value.class,
            ]"
            :aria-current="tab.value.isActive ? 'page' : undefined"
          >
            <icon
              v-if="tab.value.icon"
              :icon="tab.value.icon"
              :class="[
                tab.value.isActive
                  ? 'text-primary-500 dark:text-primary-300'
                  : 'text-neutral-400 group-hover:text-neutral-500 dark:text-white dark:group-hover:text-neutral-200',
                '-ml-0.5 mr-2 h-5 w-5',
              ]"
            />

            {{ tab.value.title }}

            <i-badge
              size="circle"
              class="ml-3"
              v-if="tab.value.badge"
              :variant="tab.value.badgeVariant"
            >
              {{ tab.value.badge }}
            </i-badge>
          </a>
        </nav>
        <a
          href="#"
          class="block px-1 text-neutral-500 dark:text-neutral-200 sm:hidden"
          :class="{ 'pointer-events-none opacity-50': scrolledToLastTab }"
          ><icon
            icon="ChevronRight"
            @click.prevent="scrollRight"
            class="h-6 w-6"
        /></a>
      </div>
    </div>
  </div>

  <div class="py-2 sm:py-4">
    <slot></slot>
  </div>
</template>
<script>
import { computed } from 'vue'

export default {
  emits: ['update:modelValue', 'changed'],
  props: {
    modelValue: [String, Number],
    navClass: [String, Object, Array],
    navWrapperClass: [String, Object, Array],
    responsive: { type: Boolean, default: true },
    border: { type: [String, Boolean], default: 'b' },
    fill: Boolean,
    centered: Boolean,
  },
  data: () => ({
    tabs: [],
    activeTab: null,
    scrolledToLastTab: false,
    scrolledToFirstTab: true,
    observer: null,
  }),
  provide() {
    return {
      registerTab: this.registerTab,
      selectTab: this.selectTab,
      activeTab: computed(() => this.activeTab),
    }
  },
  watch: {
    modelValue: function (newVal, oldVal) {
      if (newVal !== this.activeTab) {
        this.selectTab(newVal)
      }
    },
  },
  computed: {
    totalTabs() {
      return this.tabs.length
    },
  },
  methods: {
    firstTabElm() {
      return this.$refs['tab-0'][0]
    },
    lastTabElm() {
      return this.$refs['tab-' + (this.tabs.length - 1)][0]
    },
    scrollLeft() {
      this.$refs.nav.scrollLeft -= this.firstTabElm().offsetWidth
    },
    scrollRight() {
      this.$refs.nav.scrollLeft += this.lastTabElm().offsetWidth
    },
    selectTab(tabId) {
      if (tabId === this.activeTab) {
        return
      }

      this.activeTab = tabId
      this.$emit('update:modelValue', tabId)
      this.$emit('changed', tabId)
    },
    registerTab(tab) {
      this.tabs.push(tab)
    },
    unobserve() {
      this.observer.unobserve(this.firstTabElm())
      this.observer.unobserve(this.lastTabElm())
    },
    observe() {
      this.createObserver()
      this.$nextTick(() => {
        this.observer.observe(this.firstTabElm())
        this.observer.observe(this.lastTabElm())
      })
    },
    createObserver() {
      this.observer = new IntersectionObserver(
        entries => {
          entries.forEach(entry => {
            if (entry.target == this.lastTabElm()) {
              this.scrolledToLastTab = entry.isIntersecting
            } else if (entry.target == this.firstTabElm()) {
              this.scrolledToFirstTab = entry.isIntersecting
              this.scrolledToLastTab = false
            }
          })
        },
        {
          root: this.$refs.nav,
          threshold: 1.0,
        }
      )
    },
  },
  mounted() {
    this.selectTab(this.modelValue || this.tabs[0].value.tabId)
    this.observe()

    this.$watch('totalTabs', function () {
      this.unobserve()
      this.createObserver()
    })
  },
  beforeUnmount() {
    this.unobserve()
    this.observer = null
  },
}
</script>
