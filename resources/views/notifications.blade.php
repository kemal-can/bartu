<notification-group group="app">
    <div class="notifications fixed inset-0 flex items-start justify-end p-6 px-4 py-6 pointer-events-none">
        <div class="w-full max-w-sm">
            <notification v-slot="{ notifications, close }" enter="ease-out duration-300 transition"
                enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enter-to="translate-y-0 opacity-100 sm:translate-x-0" leave="transition ease-in duration-100"
                leave-from="opacity-100" leave-to="opacity-0" move="transition duration-500" move-delay="delay-300">
                <div v-for="(notification, index) in notifications" :key="index"
                    class="
      notification relative max-w-sm w-full bg-white dark:bg-neutral-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden mb-2"
                    :class="{ 'border-2 border-danger-300': notification.type === 'error' }">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <icon icon="CheckCircle" v-if="notification.type === 'success'"
                                    class="h-5 w-5 text-success-400"></icon>
                                <icon icon="InformationCircle" v-if="notification.type === 'info'"
                                    class="h-5 w-5 text-info-400"></icon>
                                <icon icon="XCircle" v-if="notification.type === 'error'"
                                    class="h-5 w-5 text-danger-400"></icon>
                            </div>
                            <div class="w-0 flex-1 flex justify-between ml-3">
                                <p class="w-0 flex-1 text-sm font-medium text-neutral-800 dark:text-white">
                                    @{{ notification.text }}
                                </p>
                                <button type="button" v-if="notification.action" @click="notification.action.onClick"
                                    class="ml-3 shrink-0 bg-white rounded-md text-sm font-medium text-primary-600 hover:text-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    @{{ notification.action.text }}
                                </button>
                            </div>
                            <div class="ml-4 shrink-0 flex">
                                <button type="button" @click="close(notification.id)"
                                    class="bg-white dark:bg-neutral-900 rounded-md inline-flex text-neutral-400 hover:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <icon icon="X" class="h-5 w-5"></icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </notification>
        </div>
    </div>
</notification-group>
