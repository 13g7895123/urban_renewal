import { a as _export_sfc, c as __nuxt_component_0, b as __nuxt_component_1, x as __nuxt_component_2, y as __nuxt_component_3 } from "../server.mjs";
import { mergeProps, withCtx, createVNode, createTextVNode, renderSlot, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderSlot, ssrRenderComponent } from "vue/server-renderer";
import "ofetch";
import "#internal/nuxt/paths";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/hookable/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/unctx/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/h3/dist/index.mjs";
import "vue-router";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/radix3/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/defu/dist/defu.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/ufo/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/klona/dist/index.mjs";
import "@vueuse/core";
import "tailwind-merge";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/@unhead/vue/dist/index.mjs";
import "@iconify/vue";
import "ohash/utils";
import "@iconify/utils/lib/css/icon";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/perfect-debounce/dist/index.mjs";
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
  const _component_BackgroundImage = __nuxt_component_2;
  const _component_Footer = __nuxt_component_3;
  _push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen flex flex-col" }, _attrs))} data-v-47c578d1><header class="navbar-gradient h-20 flex items-center px-6" data-v-47c578d1><div class="flex-1" data-v-47c578d1></div><h1 class="text-white text-xl font-bold" data-v-47c578d1>`);
  ssrRenderSlot(_ctx.$slots, "title", {}, () => {
    _push(`都更計票系統首頁`);
  }, _push, _parent);
  _push(`</h1><div class="flex-1 flex justify-end space-x-4" data-v-47c578d1>`);
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
  _push(`</div></header><main class="flex-1" data-v-47c578d1>`);
  _push(ssrRenderComponent(_component_BackgroundImage, { "container-class": $props.mainClass }, {
    default: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        ssrRenderSlot(_ctx.$slots, "default", {}, null, _push2, _parent2, _scopeId);
      } else {
        return [
          renderSlot(_ctx.$slots, "default", {}, void 0, true)
        ];
      }
    }),
    _: 3
  }, _parent));
  _push(`</main>`);
  _push(ssrRenderComponent(_component_Footer, { "logo-style": $props.logoStyle }, null, _parent));
  _push(`</div>`);
}
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("layouts/auth.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const auth = /* @__PURE__ */ _export_sfc(_sfc_main, [["ssrRender", _sfc_ssrRender], ["__scopeId", "data-v-47c578d1"]]);
export {
  auth as default
};
//# sourceMappingURL=auth-COvESg9L.js.map
