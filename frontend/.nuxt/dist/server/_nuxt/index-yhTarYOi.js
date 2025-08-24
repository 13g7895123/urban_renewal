import { withAsyncContext, useSSRContext } from "vue";
import { ssrRenderAttrs } from "vue/server-renderer";
import { n as navigateTo } from "../server.mjs";
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
import "@heroicons/vue/24/outline";
const _sfc_main = {
  __name: "index",
  __ssrInlineRender: true,
  async setup(__props) {
    let __temp, __restore;
    [__temp, __restore] = withAsyncContext(() => navigateTo("/login")), await __temp, __restore();
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(_attrs)}></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=index-yhTarYOi.js.map
