import { h as defineNuxtRouteMiddleware, i as useAuthStore } from './server.mjs';
import 'vue';
import '../_/nitro.mjs';
import 'node:http';
import 'node:https';
import 'node:events';
import 'node:buffer';
import 'node:fs';
import 'node:url';
import '@iconify/utils';
import 'node:crypto';
import 'consola';
import 'node:path';
import 'vue-router';
import '@vueuse/core';
import 'tailwind-merge';
import '@iconify/vue';
import 'vue/server-renderer';
import '@heroicons/vue/24/outline';
import '../routes/renderer.mjs';
import 'vue-bundle-renderer/runtime';
import 'unhead/server';
import 'devalue';
import 'unhead/utils';
import 'unhead/plugins';

const guest = defineNuxtRouteMiddleware(async (to) => {
  useAuthStore();
  {
    return;
  }
});

export { guest as default };
//# sourceMappingURL=guest-DCMvgAf0.mjs.map
