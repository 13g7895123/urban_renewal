import { c as buildAssetsURL } from '../_/nitro.mjs';
import { c as __nuxt_component_0 } from './Button-CUPZ9vdw.mjs';
import __nuxt_component_1 from './index-BRDSpBS1.mjs';
import { mergeProps, withCtx, createVNode, createTextVNode, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrRenderSlot, ssrRenderComponent, ssrRenderClass, ssrRenderAttr, ssrRenderStyle } from 'vue/server-renderer';
import { _ as _export_sfc } from './server.mjs';
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
import 'tailwind-merge';
import '@vueuse/core';
import '@iconify/vue';
import '@iconify/utils/lib/css/icon';
import '@heroicons/vue/24/outline';
import '../routes/renderer.mjs';
import 'vue-bundle-renderer/runtime';
import 'unhead/server';
import 'devalue';
import 'unhead/utils';
import 'unhead/plugins';

const _imports_0 = "" + buildAssetsURL("logo.DlkgSC12.jpg");
const _sfc_main = {
  name: "AuthLayout",
  props: {
    mainClass: {
      type: String,
      default: "flex items-center justify-center py-12"
    },
    logoStyle: {
      type: String,
      default: "width: auto;"
    }
  }
};
function _sfc_ssrRender(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  const _component_UButton = __nuxt_component_0;
  const _component_Icon = __nuxt_component_1;
  _push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen flex flex-col" }, _attrs))} data-v-140ce645><header class="navbar-gradient h-20 flex items-center px-6" data-v-140ce645><div class="flex-1" data-v-140ce645></div><h1 class="text-white text-xl font-bold" data-v-140ce645>`);
  ssrRenderSlot(_ctx.$slots, "title", {}, () => {
    _push(`\u90FD\u66F4\u8A08\u7968\u7CFB\u7D71\u9996\u9801`);
  }, _push, _parent);
  _push(`</h1><div class="flex-1 flex justify-end space-x-4" data-v-140ce645>`);
  _push(ssrRenderComponent(_component_UButton, {
    variant: "ghost",
    color: "white",
    size: "lg",
    class: "text-white navbar-btn",
    onClick: ($event) => _ctx.$router.push("/signup")
  }, {
    default: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(ssrRenderComponent(_component_Icon, {
          name: "heroicons:user-plus",
          class: "w-5 h-5 mr-2"
        }, null, _parent2, _scopeId));
        _push2(` \u8A3B\u518A `);
      } else {
        return [
          createVNode(_component_Icon, {
            name: "heroicons:user-plus",
            class: "w-5 h-5 mr-2"
          }),
          createTextVNode(" \u8A3B\u518A ")
        ];
      }
    }),
    _: 1
  }, _parent));
  _push(ssrRenderComponent(_component_UButton, {
    variant: "ghost",
    color: "white",
    size: "lg",
    class: "text-white navbar-btn",
    onClick: ($event) => _ctx.$router.push("/login")
  }, {
    default: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(ssrRenderComponent(_component_Icon, {
          name: "heroicons:arrow-right-on-rectangle",
          class: "w-5 h-5 mr-2"
        }, null, _parent2, _scopeId));
        _push2(` \u767B\u5165 `);
      } else {
        return [
          createVNode(_component_Icon, {
            name: "heroicons:arrow-right-on-rectangle",
            class: "w-5 h-5 mr-2"
          }),
          createTextVNode(" \u767B\u5165 ")
        ];
      }
    }),
    _: 1
  }, _parent));
  _push(`</div></header><main class="${ssrRenderClass([$props.mainClass, "flex-1 login-container"])}" data-v-140ce645>`);
  ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
  _push(`</main><footer class="footer-section bg-gray-50 border-t border-gray-200 py-8" data-v-140ce645><div class="container mx-auto px-6" data-v-140ce645><div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center" data-v-140ce645><div class="md:col-span-4" data-v-140ce645></div><div class="md:col-span-4 flex flex-col md:flex-row items-center justify-center" data-v-140ce645><img${ssrRenderAttr("src", _imports_0)} alt="Logo" class="footer-logo" style="${ssrRenderStyle($props.logoStyle)}" data-v-140ce645><div class="contact-info space-y-2 text-sm text-gray-600 text-center md:text-left" data-v-140ce645><div class="flex items-center justify-center md:justify-start" data-v-140ce645>`);
  _push(ssrRenderComponent(_component_Icon, {
    name: "heroicons:phone",
    class: "w-4 h-4 mr-2"
  }, null, _parent));
  _push(` \u670D\u52D9\u96FB\u8A71: (02) 1234-5678 </div><div class="flex items-center justify-center md:justify-start" data-v-140ce645>`);
  _push(ssrRenderComponent(_component_Icon, {
    name: "heroicons:printer",
    class: "w-4 h-4 mr-2"
  }, null, _parent));
  _push(` \u670D\u52D9\u50B3\u771F: (02) 1234-5679 </div><div class="flex items-center justify-center md:justify-start whitespace-nowrap" data-v-140ce645>`);
  _push(ssrRenderComponent(_component_Icon, {
    name: "heroicons:envelope",
    class: "w-4 h-4 mr-2"
  }, null, _parent));
  _push(` \u670D\u52D9\u4FE1\u7BB1: service@example.com </div></div></div><div class="md:col-span-4 text-center md:text-right" data-v-140ce645><div class="copyright text-xs text-gray-400" data-v-140ce645> \xA9 2024 \u90FD\u66F4\u8A08\u7968\u7CFB\u7D71 </div></div></div></div></footer></div>`);
}
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("layouts/auth.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const auth = /* @__PURE__ */ _export_sfc(_sfc_main, [["ssrRender", _sfc_ssrRender], ["__scopeId", "data-v-140ce645"]]);

export { auth as default };
//# sourceMappingURL=auth-CiKeu3b4.mjs.map
