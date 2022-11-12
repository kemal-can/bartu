<template>
  <!-- Sidebar for mobile -->
  <TransitionRoot as="template" :show="sidebarOpen">
    <Dialog
      as="div"
      static
      class="fixed inset-0 z-50 flex md:hidden"
      @close="SET_SIDEBAR_OPEN(false)"
      :open="sidebarOpen"
    >
      <TransitionChild
        as="template"
        enter="transition-opacity ease-linear duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="transition-opacity ease-linear duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <DialogOverlay class="fixed inset-0 bg-neutral-600 bg-opacity-75" />
      </TransitionChild>
      <TransitionChild
        as="template"
        enter="transition ease-in-out duration-300 transform"
        enter-from="-translate-x-full"
        enter-to="translate-x-0"
        leave="transition ease-in-out duration-300 transform"
        leave-from="translate-x-0"
        leave-to="-translate-x-full"
      >
        <div
          class="relative flex w-56 max-w-xs flex-col bg-neutral-800 pt-5 pb-4 dark:bg-neutral-900"
        >
          <TransitionChild
            as="template"
            enter="ease-in-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in-out duration-300"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="absolute top-0 right-0 -mr-12 pt-2">
              <button
                type="button"
                class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                @click="SET_SIDEBAR_OPEN(false)"
              >
                <span class="sr-only">Close sidebar</span>
                <icon icon="X" class="h-6 w-6 text-white" />
              </button>
            </div>
          </TransitionChild>
          <div class="flex shrink-0 items-center px-4">
            <router-link class="whitespace-normal" :to="{ name: 'dashboard' }">
              <span v-if="!logo" class="font-bold text-white">
                {{ companyName }}
              </span>
              <img v-else :src="logo" class="h-10 max-h-14 w-auto" />
            </router-link>
          </div>
          <div class="mt-5 h-0 flex-1 overflow-y-auto">
            <nav class="space-y-1 px-2">
              <router-link
                :to="item.route"
                v-for="item in sidebarNavigation"
                :key="item.id"
                custom
                v-slot="{ href, route, navigate, isActive, isExactActive }"
              >
                <a
                  :href="href"
                  @click="navigate"
                  :class="[
                    isActive
                      ? 'bg-neutral-700 text-white'
                      : 'text-neutral-50 hover:bg-neutral-600',
                    'group relative flex items-center rounded-md px-2 py-2 font-medium',
                  ]"
                  :aria-current="isActive ? 'page' : undefined"
                >
                  <icon
                    v-if="item.icon"
                    :icon="item.icon"
                    class="mr-4 h-6 w-6 shrink-0 text-neutral-300"
                  />
                  {{ item.name }}
                  <i-badge
                    v-if="item.badge"
                    :variant="item.badgeVariant"
                    size="circle"
                    class="absolute left-0 top-0"
                    v-text="item.badge"
                  />
                </a>
              </router-link>
            </nav>
          </div>
          <sidebar-highlights />
        </div>
      </TransitionChild>
      <div class="w-14 shrink-0" aria-hidden="true">
        <!-- Dummy element to force sidebar to shrink to fit close icon -->
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Static sidebar for desktop -->
  <div
    class="hidden bg-neutral-800 dark:bg-neutral-900 md:flex md:shrink-0"
    v-show="['404', '403', 'not-found'].indexOf($route.name) === -1"
  >
    <div class="flex w-56 flex-col">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex grow flex-col overflow-y-auto pt-5 pb-4">
        <div class="flex shrink-0 items-center px-4">
          <router-link class="whitespace-normal" :to="{ name: 'dashboard' }">
            <span v-if="!logo" class="font-bold text-white">
              {{ companyName }}
            </span>
            <img v-else :src="logo" class="h-10 max-h-14 w-auto" />
          </router-link>
        </div>

        <!-- Profile dropdown -->
        <div class="relative mt-4 inline-block px-3 text-left">
          <i-dropdown>
            <template #toggle>
              <button
                type="button"
                class="group mt-3 w-full rounded-md bg-neutral-200 px-3.5 py-2 text-left text-sm font-medium text-neutral-700 hover:bg-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 focus:ring-offset-neutral-100 dark:bg-neutral-700"
              >
                <span class="flex w-full items-center justify-between">
                  <span
                    class="flex min-w-0 items-center justify-between space-x-3"
                  >
                    <i-avatar
                      :src="currentUser.avatar_url"
                      :title="currentUser.name"
                    />
                    <span class="flex min-w-0 flex-1 flex-col">
                      <span
                        class="truncate text-sm font-medium text-neutral-800 dark:text-white"
                        >{{ currentUser.name }}</span
                      >
                      <span
                        class="truncate text-sm text-neutral-600 dark:text-neutral-300"
                        >{{ currentUser.email }}</span
                      >
                    </span>
                  </span>
                  <icon
                    icon="Selector"
                    class="h-5 w-5 shrink-0 text-neutral-500 group-hover:text-neutral-600"
                  />
                </span>
              </button>
            </template>
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
              <div class="py-1">
                <i-dropdown-item
                  :to="{ name: 'profile' }"
                  :text="$t('profile.profile')"
                />

                <i-dropdown-item
                  :to="{ name: 'calendar-sync' }"
                  :text="$t('calendar.calendar_sync')"
                />

                <i-dropdown-item
                  :to="{ name: 'oauth-accounts' }"
                  :text="$t('app.oauth.connected_accounts')"
                />

                <i-dropdown-item
                  :to="{ name: 'personal-access-tokens' }"
                  v-if="$gate.userCan('access-api')"
                  :text="$t('api.personal_access_tokens')"
                />
              </div>
              <div class="py-1">
                <i-dropdown-item
                  href="#"
                  @click="$store.dispatch('logout')"
                  :text="$t('auth.logout')"
                />
              </div>
            </div>
          </i-dropdown>
        </div>

        <!-- Sidebar links -->
        <div class="mt-6 flex h-0 flex-1 flex-col overflow-y-auto">
          <nav class="flex-1 space-y-1 px-2">
            <router-link
              :to="item.route"
              v-for="item in sidebarNavigation"
              :key="item.id"
              custom
              v-slot="{ href, route, navigate, isActive, isExactActive }"
            >
              <a
                :href="href"
                @click="navigate"
                :class="[
                  isActive
                    ? 'bg-neutral-700 text-white'
                    : 'text-neutral-50 hover:bg-neutral-600',
                  'group relative flex items-center rounded-md px-2 py-2 text-sm font-medium',
                ]"
                :aria-current="isActive ? 'page' : undefined"
              >
                <icon
                  v-if="item.icon"
                  :icon="item.icon"
                  class="mr-3 h-6 w-6 shrink-0 text-neutral-300"
                />
                {{ item.name }}
                <i-badge
                  v-if="item.badge"
                  :variant="item.badgeVariant"
                  size="circle"
                  class="absolute left-0 top-0"
                  v-text="item.badge"
                />
              </a>
            </router-link>
          </nav>
        </div>
        <sidebar-highlights />
      </div>
    </div>
  </div>
</template>
<script>
import { mapState, mapMutations } from 'vuex'
import SidebarHighlights from '@/components/SidebarHighlights'

export default {
  components: { SidebarHighlights },
  computed: {
    ...mapState({
      sidebarNavigation: state => state.menu,
      sidebarOpen: state => state.sidebarOpen,
    }),

    /**
     * Get the company name
     *
     * @return {String|null}
     */
    companyName() {
      return this.setting('company_name')
    },

    /**
     * Get the logo URL
     *
     * @return {String|null}
     */
    logo() {
      return Innoclapps.config.options.logo_light
    },
  },
  methods: {
    ...mapMutations(['SET_SIDEBAR_OPEN']),
  },
}
</script>
