<template>
  <i-card :header="$t('settings.security.security')" no-body>
    <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
      <li class="px-4 py-4 sm:px-6">
        <div
          class="space-y-3 space-x-0 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
        >
          <div>
            <h5
              class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
              v-t="'settings.security.disable_password_forgot'"
            />
            <p
              class="break-words text-sm text-neutral-600 dark:text-neutral-300"
              v-t="'settings.security.disable_password_forgot_info'"
            />
          </div>
          <div>
            <i-form-toggle
              :value="true"
              :unchecked-value="false"
              @change="submit"
              v-model="form.disable_password_forgot"
            />
          </div>
        </div>
      </li>
      <li class="px-4 py-4 sm:px-6">
        <div
          class="space-y-3 space-x-0 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
        >
          <div>
            <h5
              class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
              v-t="'settings.security.block_bad_visitors'"
            />
            <p
              class="break-words text-sm text-neutral-600 dark:text-neutral-300"
              v-t="'settings.security.block_bad_visitors_info'"
            />
          </div>
          <div>
            <i-form-toggle
              :value="true"
              :unchecked-value="false"
              @change="submit"
              v-model="form.block_bad_visitors"
            />
          </div>
        </div>
      </li>
    </ul>
  </i-card>
</template>
<script>
import HandleSettingsForm from '@/views/Settings/HandleSettingsForm'
export default {
  mixins: [HandleSettingsForm],
}
</script>
