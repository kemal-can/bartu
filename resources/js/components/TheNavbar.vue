<template>
  <div
    class="relative flex h-navbar shrink-0 bg-white shadow dark:bg-neutral-700"
  >
    <button
      type="button"
      class="border-r border-neutral-200 px-3 text-neutral-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:border-neutral-600 dark:text-neutral-200 md:hidden"
      @click="SET_SIDEBAR_OPEN(true)"
    >
      <span class="sr-only">Open sidebar</span>
      <icon icon="MenuAlt2" class="h-6 w-6" />
    </button>
    <div class="flex flex-1 justify-between pr-4 sm:pr-6 lg:pr-8">
      <div class="flex flex-1">
        <div class="mx-8 hidden max-w-xs py-5 lg:block" v-if="title">
          <h1
            class="truncate font-semibold uppercase text-neutral-700 dark:text-neutral-100"
            v-text="title"
          />
        </div>
        <span
          v-if="title"
          class="hidden h-navbar border-l border-neutral-200 dark:border-neutral-600 lg:block"
        />

        <div class="flex w-full sm:relative">
          <div class="relative flex w-full">
            <label for="nav-search" class="sr-only">Search</label>
            <div
              class="relative w-full text-neutral-400 focus-within:text-neutral-600 dark:focus-within:text-neutral-200"
            >
              <div
                class="pointer-events-none absolute inset-y-0 left-6 flex items-center"
              >
                <icon
                  icon="SearchSolid"
                  :class="[
                    'h-5 w-5',
                    searchRequestInProgress ? 'animate-pulse' : '',
                  ]"
                />
              </div>
              <input
                ref="search"
                id="nav-search"
                autocomplete="off"
                class="peer block h-full w-full appearance-none border-transparent py-2 pl-14 pr-3 text-neutral-900 placeholder-neutral-500 focus:border-transparent focus:placeholder-neutral-400 focus:outline-none focus:ring-0 dark:bg-neutral-700 dark:text-neutral-200 dark:placeholder-neutral-400 dark:focus:placeholder-neutral-500 sm:text-sm"
                v-model="search"
                @keydown.enter="performSearch(search)"
                @input="performSearch($event.target.value)"
                :placeholder="$t('app.search')"
                type="search"
                name="search"
              />
              <div
                class="absolute left-56 top-[1.1rem] hidden peer-focus:hidden lg:block"
                v-if="shouldUseSearchKeyboardShortcut"
              >
                <kbd
                  class="inline-flex items-center rounded border border-neutral-300 px-2 font-sans text-sm font-bold text-neutral-500 dark:border-neutral-300 dark:text-neutral-300"
                >
                  {{ searchKeyboardShortcutMainKey }}&nbsp;{{
                    searchKeyboardShortcutKey
                  }}
                </kbd>
              </div>
            </div>
          </div>
          <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-1 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-1 opacity-0"
          >
            <div
              v-show="showSearchResults"
              v-click-outside="clearSearch"
              class="absolute inset-0 top-[65px] z-30 w-screen max-w-sm transform px-4 sm:left-8 sm:px-0 lg:max-w-lg"
            >
              <div
                class="overflow-hidden rounded-b-lg shadow-lg ring-1 ring-neutral-600 ring-opacity-5 dark:ring-neutral-700"
              >
                <div class="bg-white dark:bg-neutral-800">
                  <div
                    v-if="hasSearchResults"
                    class="max-h-screen overflow-y-auto py-3 px-5"
                  >
                    <span :key="resource.title" v-for="resource in result">
                      <p
                        class="mt-3 mb-1.5 font-medium text-neutral-900 dark:text-white"
                      >
                        {{ resource.title }}
                      </p>
                      <router-link
                        @click="showSearchResults = false"
                        class="group relative mb-2 block whitespace-normal rounded-lg border border-neutral-100 bg-neutral-50 py-3 pl-5 pr-12 text-sm text-neutral-800 hover:border-primary-700 hover:bg-primary-600 hover:text-white dark:border-neutral-600 dark:bg-neutral-700 dark:text-white dark:hover:border-primary-600 dark:hover:bg-primary-600"
                        v-for="record in resource.data"
                        :key="record.path"
                        :to="record.path"
                      >
                        <span class="block truncate font-medium">
                          {{ record.display_name }}
                        </span>
                        <span
                          class="text-neutral-500 group-hover:text-primary-200 dark:text-neutral-300"
                          v-if="record.created_at"
                          >{{ $t('app.created_at') }}
                          {{ localizedDateTime(record.created_at) }}</span
                        >
                        <icon
                          icon="ChevronRight"
                          class="absolute top-7 right-4 h-4 w-4 text-current"
                        />
                      </router-link>
                    </span>
                  </div>
                  <div
                    v-if="!hasSearchResults"
                    class="p-3 text-center text-sm text-neutral-700 dark:text-neutral-200"
                  >
                    {{ $t('app.no_search_results') }}
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
      <div class="ml-3 flex items-center lg:ml-6">
        <i-button-icon
          id="header__moon"
          @click="toLightMode"
          class="md:block"
          v-i-tooltip.bottom="$t('app.theme.switch_light')"
          icon="Moon"
        />
        <i-button-icon
          @click="toSystemMode"
          v-i-tooltip.bottom="$t('app.theme.switch_system')"
          icon="Sun"
          id="header__sun"
        />
        <i-button-icon
          @click="toDarkMode"
          v-i-tooltip.bottom="$t('app.theme.switch_dark')"
          icon="Sun"
          id="header__indeterminate"
        >
          <svg class="h-5 w-5 text-neutral-400" viewBox="0 0 24 24">
            <path
              fill="currentColor"
              d="M12 2A10 10 0 0 0 2 12A10 10 0 0 0 12 22A10 10 0 0 0 22 12A10 10 0 0 0 12 2M12 4A8 8 0 0 1 20 12A8 8 0 0 1 12 20V4Z"
            ></path>
          </svg>
        </i-button-icon>

        <navbar-separator />

        <!-- Notifications -->
        <div class="mr-1 lg:mr-3"><navbar-notifications /></div>

        <!-- Quick create dropdown -->
        <div class="hidden md:block">
          <navbar-quick-create />
        </div>

        <!-- Teleport target -->
        <div id="navbar-actions" class="hidden items-center lg:flex"></div>

        <!-- Profile dropdown -->
        <div class="ml-1 md:hidden lg:ml-3">
          <i-dropdown placement="bottom-end" :full="false">
            <template #toggle>
              <button
                type="button"
                class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
              >
                <i-avatar
                  :src="currentUser.avatar_url"
                  :title="currentUser.name"
                />
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
      </div>
    </div>
  </div>
</template>
<script>
import { mapMutations } from 'vuex'
import debounce from 'lodash/debounce'
import NavbarNotifications from '@/components/NavbarNotifications'
import NavbarQuickCreate from '@/components/NavbarQuickCreate'
import vClickOutside from 'click-outside-vue3'
export default {
  components: {
    NavbarNotifications,
    NavbarQuickCreate,
  },
  directives: {
    clickOutside: vClickOutside.directive,
  },
  data: () => ({
    search: null,
    result: [],
    showSearchResults: false,
    searchRequestInProgress: false,
  }),
  computed: {
    isMacintosh() {
      return window.navigator.platform.indexOf('Mac') > -1
    },
    searchKeyboardShortcutMainKey() {
      return this.isMacintosh ? '⌘' : 'Ctrl'
    },
    searchKeyboardShortcutKey() {
      return 'K'
    },
    shouldUseSearchKeyboardShortcut() {
      return !(
        /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(
          window.navigator.userAgent
        ) ||
        /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(
          window.navigator.platform
        )
      )
    },
    /**
     *  Navbar title
     *
     * @return {String|null}
     */
    title() {
      return this.$store.state.pageTitle
    },

    /**
     * Check whether there are search results
     *
     * @return {Boolean}
     */
    hasSearchResults() {
      return this.result.length > 0
    },
  },
  methods: {
    ...mapMutations(['SET_SIDEBAR_OPEN']),
    toLightMode() {
      localStorage.theme = 'light'
      window.updateTheme()
    },
    toDarkMode() {
      localStorage.theme = 'dark'
      window.updateTheme()
    },
    toSystemMode() {
      localStorage.theme = 'system'
      window.updateTheme()
    },
    clearSearch() {
      this.search = null
      this.showSearchResults = false
    },
    /**
     * Perform search request
     *
     * @param  {String} value
     *
     * @return {Void}
     */
    performSearch: debounce(function (value) {
      if (!value) {
        this.result = []
        this.showSearchResults = false
        return
      }
      this.searchRequestInProgress = true
      Innoclapps.request()
        .get('/search', { params: { q: value } })
        .then(({ data }) => {
          this.result = data
          this.showSearchResults = true
        })
        .finally(() => (this.searchRequestInProgress = false))
    }, 650),
  },
  mounted() {
    window.addEventListener('keydown', e => {
      if (e.key === 'esc' && this.showSearchResults) {
        this.clearSearch()
      }
    })

    if (this.shouldUseSearchKeyboardShortcut) {
      document.addEventListener('keydown', e => {
        if (
          (e.ctrlKey || e.metaKey) &&
          e.key === this.searchKeyboardShortcutKey.toLowerCase()
        ) {
          e.preventDefault()
          if (document.activeElement === this.$refs.search) {
            this.$refs.search.blur()
            this.clearSearch()
          } else {
            this.$refs.search.focus()
          }
        }
      })
    }
  },
}
</script>
<style>
#header__sun,
#header__moon,
#header__indeterminate {
  display: none;
}

html[color-theme='dark'] #header__moon {
  display: block;
}

html[color-theme='light'] #header__sun {
  display: block;
}

html[color-theme='system'] #header__indeterminate {
  display: block;
}
</style>
