<template>
  <div
    class="rounded-lg border border-neutral-100 bg-white shadow dark:border-neutral-700 dark:bg-neutral-900"
  >
    <div class="px-4 py-5 sm:px-6">
      <h2
        class="flex items-center justify-between font-medium text-neutral-800 dark:text-white"
      >
        <span>
          {{ cardTitle }}
          <span
            v-show="total > 0"
            class="text-sm font-normal text-neutral-400"
            v-text="'(' + total + ')'"
          />
        </span>

        <a
          href="#"
          class="link shrink-0 text-sm font-normal"
          v-if="total > 3 && !showAll"
          @click.prevent="showAll = true"
          v-t="'app.show_all'"
        />
        <a
          href="#"
          class="link shrink-0 text-sm font-normal"
          v-if="total > 3 && showAll"
          @click.prevent="showAll = false"
          v-t="'app.show_less'"
        />
      </h2>
      <ul
        class="divide-y divide-neutral-200 dark:divide-neutral-700"
        :class="{ '-mb-4': total > 0 }"
      >
        <li
          v-for="(deal, index) in iterable"
          :key="deal.id"
          class="group flex items-center space-x-3 py-4"
          v-show="index <= 2 || showAll"
        >
          <div class="shrink-0">
            <i-badge
              :variant="
                deal.status === 'won'
                  ? 'success'
                  : deal.status === 'lost'
                  ? 'danger'
                  : 'neutral'
              "
              v-t="'deal.status.' + deal.status"
            ></i-badge>
          </div>

          <div class="min-w-0 flex-1 truncate">
            <a
              :href="deal.path"
              class="text-sm font-medium text-neutral-800 hover:text-neutral-500 dark:text-white dark:hover:text-neutral-300"
              @click.prevent="preview(deal.id)"
              >{{ deal.display_name }}</a
            >
            <p class="text-sm text-neutral-500 dark:text-neutral-300">
              {{ deal.stage.name }}
            </p>
          </div>
          <div class="block shrink-0 md:hidden md:group-hover:block">
            <div class="flex items-center space-x-1">
              <i-button
                size="sm"
                variant="white"
                v-show="$gate.allows('view', deal)"
                :to="deal.path"
              >
                {{ $t('app.view_record') }}
              </i-button>
              <slot name="actions" :deal="deal"></slot>
            </div>
          </div>
        </li>
      </ul>
      <p
        v-show="!hasDeals"
        class="text-sm text-neutral-500 dark:text-neutral-300"
        v-text="emptyText"
      />
      <slot name="tail"></slot>
    </div>
  </div>
</template>
<script setup>
import castArray from 'lodash/castArray'
import orderBy from 'lodash/orderBy'
import { useStore } from 'vuex'
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const store = useStore()

const props = defineProps({
  deals: { type: [Object, Array], required: true },
  emptyText: String,
  title: String,
})

const showAll = ref(false)
const cardTitle = computed(() => props.title || t('deal.deals'))

const localDeals = computed(() => castArray(props.deals))
const wonSorted = computed(() =>
  orderBy(
    localDeals.value.filter(deal => deal.status === 'won'),
    deal => new Date(deal.won_date),
    'desc'
  )
)
const lostSorted = computed(() =>
  orderBy(
    localDeals.value.filter(deal => deal.status === 'lost'),
    deal => new Date(deal.lost_date),
    'desc'
  )
)
const openSorted = computed(() =>
  orderBy(
    localDeals.value.filter(deal => deal.status === 'open'),
    deal => new Date(deal.created_at)
  )
)

const iterable = computed(() => [
  ...openSorted.value,
  ...lostSorted.value,
  ...wonSorted.value,
])

const total = computed(() => localDeals.value.length)
const hasDeals = computed(() => total.value > 0)

const preview = id => store.dispatch('deals/preview', id)
</script>
