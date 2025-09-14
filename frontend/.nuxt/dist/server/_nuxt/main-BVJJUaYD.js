import { a as _export_sfc, c as __nuxt_component_0, b as __nuxt_component_1, _ as __nuxt_component_2, y as __nuxt_component_3 } from "../server.mjs";
import { mergeProps, withCtx, createVNode, createTextVNode, useSSRContext } from "vue";
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
  __name: "main",
  __ssrInlineRender: true,
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = __nuxt_component_0;
      const _component_Icon = __nuxt_component_1;
      const _component_NuxtLink = __nuxt_component_2;
      const _component_Footer = __nuxt_component_3;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen flex flex-col" }, _attrs))} data-v-3f6d57da><header class="navbar-gradient h-16 flex items-center px-6 shadow-sm" data-v-3f6d57da><div class="flex-1" data-v-3f6d57da></div><h1 class="text-white text-xl font-bold" data-v-3f6d57da>`);
      ssrRenderSlot(_ctx.$slots, "title", {}, () => {
        _push(`更新會管理`);
      }, _push, _parent);
      _push(`</h1><div class="flex-1 flex justify-end items-center space-x-4" data-v-3f6d57da>`);
      _push(ssrRenderComponent(_component_UButton, {
        variant: "ghost",
        color: "white",
        size: "sm",
        class: "text-white hover:bg-green-600"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:shopping-cart",
              class: "w-5 h-5 mr-2"
            }, null, _parent2, _scopeId));
            _push2(` 購物車 `);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:shopping-cart",
                class: "w-5 h-5 mr-2"
              }),
              createTextVNode(" 購物車 ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        variant: "ghost",
        color: "white",
        size: "sm",
        class: "text-white hover:bg-green-600"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:arrow-right-on-rectangle",
              class: "w-5 h-5 mr-2"
            }, null, _parent2, _scopeId));
            _push2(` 登出 `);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:arrow-right-on-rectangle",
                class: "w-5 h-5 mr-2"
              }),
              createTextVNode(" 登出 ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></header><div class="flex flex-1" data-v-3f6d57da><aside class="w-64 bg-gray-800 min-h-full text-white" data-v-3f6d57da><div class="p-4" data-v-3f6d57da><div class="flex items-center space-x-3 mb-8" data-v-3f6d57da><div class="w-16 h-16 bg-gray-600 rounded-full flex items-center justify-center" data-v-3f6d57da>`);
      _push(ssrRenderComponent(_component_Icon, {
        name: "heroicons:user",
        class: "w-8 h-8 text-white"
      }, null, _parent));
      _push(`</div><div data-v-3f6d57da><div class="text-white font-medium" data-v-3f6d57da>許湘淳</div><div class="w-6 h-6 bg-gray-600 rounded-full mt-1" data-v-3f6d57da></div></div></div><nav class="space-y-2" data-v-3f6d57da>`);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/",
        class: ["flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors", { "bg-blue-600": _ctx.$route.path === "/" }]
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:home",
              class: "w-5 h-5"
            }, null, _parent2, _scopeId));
            _push2(`<span data-v-3f6d57da${_scopeId}>首頁</span>`);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:home",
                class: "w-5 h-5"
              }),
              createVNode("span", null, "首頁")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/tables/urban-renewal",
        class: ["flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors", { "bg-blue-600": _ctx.$route.path === "/tables/urban-renewal" }]
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:building-office-2",
              class: "w-5 h-5"
            }, null, _parent2, _scopeId));
            _push2(`<span data-v-3f6d57da${_scopeId}>更新會管理</span>`);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:building-office-2",
                class: "w-5 h-5"
              }),
              createVNode("span", null, "更新會管理")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/tables/meeting",
        class: ["flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors", { "bg-blue-600": _ctx.$route.path === "/tables/meeting" }]
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:document-text",
              class: "w-5 h-5"
            }, null, _parent2, _scopeId));
            _push2(`<span data-v-3f6d57da${_scopeId}>會議管理</span>`);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:document-text",
                class: "w-5 h-5"
              }),
              createVNode("span", null, "會議管理")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/tables/issue",
        class: ["flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors", { "bg-blue-600": _ctx.$route.path === "/tables/issue" }]
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:check-badge",
              class: "w-5 h-5"
            }, null, _parent2, _scopeId));
            _push2(`<span data-v-3f6d57da${_scopeId}>投票管理</span>`);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:check-badge",
                class: "w-5 h-5"
              }),
              createVNode("span", null, "投票管理")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/pages/shopping",
        class: ["flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors", { "bg-blue-600": _ctx.$route.path === "/pages/shopping" }]
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:shopping-bag",
              class: "w-5 h-5"
            }, null, _parent2, _scopeId));
            _push2(`<span data-v-3f6d57da${_scopeId}>商城</span>`);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:shopping-bag",
                class: "w-5 h-5"
              }),
              createVNode("span", null, "商城")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/tables/order",
        class: ["flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors", { "bg-blue-600": _ctx.$route.path === "/tables/order" }]
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:clipboard-document-list",
              class: "w-5 h-5"
            }, null, _parent2, _scopeId));
            _push2(`<span data-v-3f6d57da${_scopeId}>購買紀錄</span>`);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:clipboard-document-list",
                class: "w-5 h-5"
              }),
              createVNode("span", null, "購買紀錄")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/pages/user",
        class: ["flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors", { "bg-blue-600": _ctx.$route.path === "/pages/user" }]
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:user",
              class: "w-5 h-5"
            }, null, _parent2, _scopeId));
            _push2(`<span data-v-3f6d57da${_scopeId}>使用者基本資料變更</span>`);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:user",
                class: "w-5 h-5"
              }),
              createVNode("span", null, "使用者基本資料變更")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/tables/company-profile",
        class: ["flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors", { "bg-blue-600": _ctx.$route.path === "/tables/company-profile" }]
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:building-office",
              class: "w-5 h-5"
            }, null, _parent2, _scopeId));
            _push2(`<span data-v-3f6d57da${_scopeId}>企業管理</span>`);
          } else {
            return [
              createVNode(_component_Icon, {
                name: "heroicons:building-office",
                class: "w-5 h-5"
              }),
              createVNode("span", null, "企業管理")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</nav></div></aside><main class="flex-1 bg-gray-50 flex flex-col" data-v-3f6d57da><div class="flex-1 p-6" data-v-3f6d57da><div class="bg-white rounded-lg shadow-lg h-full p-6" data-v-3f6d57da>`);
      ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
      _push(`</div></div>`);
      _push(ssrRenderComponent(_component_Footer, { "copyright-text": "© 2020, made with ❤️ by Thread Tech" }, null, _parent));
      _push(`</main></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("layouts/main.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const main = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-3f6d57da"]]);
export {
  main as default
};
//# sourceMappingURL=main-BVJJUaYD.js.map
