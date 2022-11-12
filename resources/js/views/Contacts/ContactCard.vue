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
          v-for="(contact, index) in localContacts"
          :key="contact.id"
          class="group flex items-center space-x-3 py-4"
          v-show="index <= 2 || showAll"
        >
          <div class="shrink-0">
            <i-avatar :src="contact.avatar_url"></i-avatar>
          </div>

          <div class="min-w-0 flex-1 truncate">
            <a
              :href="contact.path"
              class="text-sm font-medium text-neutral-800 hover:text-neutral-500 dark:text-white dark:hover:text-neutral-300"
              @click.prevent="preview(contact.id)"
              >{{ contact.display_name }}</a
            >
            <p
              class="text-sm text-neutral-500 dark:text-neutral-300"
              v-text="contact.job_title"
            ></p>
          </div>
          <div class="block shrink-0 md:hidden md:group-hover:block">
            <div class="flex items-center space-x-1">
              <i-button
                size="sm"
                variant="white"
                v-show="$gate.allows('view', contact)"
                :to="contact.path"
              >
                {{ $t('app.view_record') }}
              </i-button>
              <slot name="actions" :contact="contact"></slot>
            </div>
          </div>
        </li>
      </ul>
      <p
        v-show="!hasContacts"
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
  contacts: { type: [Object, Array], required: true },
  emptyText: String,
  title: String,
})

const showAll = ref(false)
const localContacts = computed(() =>
  orderBy(castArray(props.contacts), contact => new Date(contact.created_at), [
    'asc',
  ])
)
const total = computed(() => localContacts.value.length)
const hasContacts = computed(() => total.value > 0)
const cardTitle = computed(() => props.title || t('contact.contacts'))

const preview = id => store.dispatch('contacts/preview', id)
</script>
