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
          v-for="(company, index) in localCompanies"
          :key="company.id"
          class="group flex items-center space-x-3 py-4"
          v-show="index <= 2 || showAll"
        >
          <div class="shrink-0">
            <company-image></company-image>
          </div>

          <div class="min-w-0 flex-1 truncate">
            <a
              :href="company.path"
              class="text-sm font-medium text-neutral-800 hover:text-neutral-500 dark:text-white dark:hover:text-neutral-300"
              @click.prevent="preview(company.id)"
              >{{ company.display_name }}</a
            >
            <p>
              <a
                :href="'http://' + company.domain"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center text-sm text-neutral-500 hover:text-neutral-400 dark:text-neutral-400 dark:hover:text-neutral-500"
                v-show="company.domain"
                >{{ company.domain }}
                <icon icon="ExternalLink" class="ml-1 h-4 w-4"
              /></a>
            </p>
          </div>
          <div class="block shrink-0 md:hidden md:group-hover:block">
            <div class="flex items-center space-x-1">
              <i-button
                size="sm"
                variant="white"
                v-show="$gate.allows('view', company)"
                :to="company.path"
              >
                {{ $t('app.view_record') }}
              </i-button>
              <slot name="actions" :company="company"></slot>
            </div>
          </div>
        </li>
      </ul>
      <p
        v-show="!hasCompanies"
        class="text-sm text-neutral-500 dark:text-neutral-300"
        v-text="emptyText"
      />
      <slot name="tail"></slot>
    </div>
  </div>
</template>
<script setup>
import CompanyImage from './CompanyImage'
import castArray from 'lodash/castArray'
import orderBy from 'lodash/orderBy'
import { useStore } from 'vuex'
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const store = useStore()

const props = defineProps({
  companies: { type: [Object, Array], required: true },
  emptyText: String,
  title: String,
})

const showAll = ref(false)
const localCompanies = computed(() =>
  orderBy(castArray(props.companies), company => new Date(company.created_at), [
    'asc',
  ])
)
const total = computed(() => localCompanies.value.length)
const hasCompanies = computed(() => total.value > 0)
const cardTitle = computed(() => props.title || t('company.companies'))

const preview = id => store.dispatch('companies/preview', id)
</script>
