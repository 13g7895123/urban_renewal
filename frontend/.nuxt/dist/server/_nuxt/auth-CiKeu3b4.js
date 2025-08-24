import { c as __nuxt_component_0 } from "./Button-CUPZ9vdw.js";
import __nuxt_component_1 from "./index-BRDSpBS1.js";
import { mergeProps, withCtx, createVNode, createTextVNode, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderSlot, ssrRenderComponent, ssrRenderClass, ssrRenderAttr, ssrRenderStyle } from "vue/server-renderer";
import { _ as _export_sfc } from "../server.mjs";
import "vue-router";
import "tailwind-merge";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/defu/dist/defu.mjs";
import "@vueuse/core";
import "ohash/utils";
import "@iconify/vue";
import "@iconify/utils/lib/css/icon";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/perfect-debounce/dist/index.mjs";
import "ofetch";
import "#internal/nuxt/paths";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/hookable/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/unctx/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/h3/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/radix3/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/ufo/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/klona/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/@unhead/vue/dist/index.mjs";
import "@heroicons/vue/24/outline";
const _imports_0 = "" + __buildAssetsURL("logo.DlkgSC12.jpg");
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
    _push(`都更計票系統首頁`);
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
        _push2(` 註冊 `);
      } else {
        return [
          createVNode(_component_Icon, {
            name: "heroicons:user-plus",
            class: "w-5 h-5 mr-2"
          }),
          createTextVNode(" 註冊 ")
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
        _push2(` 登入 `);
      } else {
        return [
          createVNode(_component_Icon, {
            name: "heroicons:arrow-right-on-rectangle",
            class: "w-5 h-5 mr-2"
          }),
          createTextVNode(" 登入 ")
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
  _push(` 服務電話: (02) 1234-5678 </div><div class="flex items-center justify-center md:justify-start" data-v-140ce645>`);
  _push(ssrRenderComponent(_component_Icon, {
    name: "heroicons:printer",
    class: "w-4 h-4 mr-2"
  }, null, _parent));
  _push(` 服務傳真: (02) 1234-5679 </div><div class="flex items-center justify-center md:justify-start whitespace-nowrap" data-v-140ce645>`);
  _push(ssrRenderComponent(_component_Icon, {
    name: "heroicons:envelope",
    class: "w-4 h-4 mr-2"
  }, null, _parent));
  _push(` 服務信箱: service@example.com </div></div></div><div class="md:col-span-4 text-center md:text-right" data-v-140ce645><div class="copyright text-xs text-gray-400" data-v-140ce645> © 2024 都更計票系統 </div></div></div></div></footer></div>`);
}
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("layouts/auth.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const auth = /* @__PURE__ */ _export_sfc(_sfc_main, [["ssrRender", _sfc_ssrRender], ["__scopeId", "data-v-140ce645"]]);
export {
  auth as default
};
//# sourceMappingURL=auth-CiKeu3b4.js.map
