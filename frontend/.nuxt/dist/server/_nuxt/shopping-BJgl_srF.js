import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { _ as __nuxt_component_1 } from "./Card-DBUe4X9m.js";
import { b as __nuxt_component_1$1, c as __nuxt_component_0$1 } from "../server.mjs";
import { mergeProps, withCtx, createVNode, createTextVNode, useSSRContext } from "vue";
import { ssrRenderComponent } from "vue/server-renderer";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/hookable/dist/index.mjs";
import "vue-router";
import "tailwind-merge";
import "ofetch";
import "#internal/nuxt/paths";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/unctx/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/h3/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/radix3/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/defu/dist/defu.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/ufo/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/klona/dist/index.mjs";
import "@vueuse/core";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/@unhead/vue/dist/index.mjs";
import "@iconify/vue";
import "ohash/utils";
import "@iconify/utils/lib/css/icon";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/perfect-debounce/dist/index.mjs";
const _sfc_main = {
  __name: "shopping",
  __ssrInlineRender: true,
  setup(__props) {
    const addToCart = (type) => {
      console.log(`Adding ${type} to cart`);
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_UCard = __nuxt_component_1;
      const _component_Icon = __nuxt_component_1$1;
      const _component_UButton = __nuxt_component_0$1;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({ name: "main" }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`議題管理_會議選擇`);
          } else {
            return [
              createTextVNode("議題管理_會議選擇")
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="p-8"${_scopeId}><div class="flex justify-center items-center min-h-[60vh]"${_scopeId}><div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl w-full"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UCard, { class: "bg-white shadow-lg" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col items-center p-6"${_scopeId2}><h3 class="text-xl font-semibold text-gray-800 mb-4"${_scopeId2}>增開更新會</h3><div class="w-24 h-24 mb-4 flex items-center justify-center bg-gray-100 rounded-full"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_Icon, {
                    name: "heroicons:user-group",
                    class: "w-12 h-12 text-gray-600"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div class="text-3xl font-bold text-gray-800 mb-2"${_scopeId2}>$3000</div><p class="text-gray-600 text-center mb-6"${_scopeId2}>增開更新會管理數量</p>`);
                  _push3(ssrRenderComponent(_component_UButton, {
                    color: "green",
                    size: "lg",
                    class: "w-full",
                    onClick: ($event) => addToCart("renewal")
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(ssrRenderComponent(_component_Icon, {
                          name: "heroicons:shopping-cart",
                          class: "w-5 h-5 mr-2"
                        }, null, _parent4, _scopeId3));
                        _push4(` 加入購物車 `);
                      } else {
                        return [
                          createVNode(_component_Icon, {
                            name: "heroicons:shopping-cart",
                            class: "w-5 h-5 mr-2"
                          }),
                          createTextVNode(" 加入購物車 ")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col items-center p-6" }, [
                      createVNode("h3", { class: "text-xl font-semibold text-gray-800 mb-4" }, "增開更新會"),
                      createVNode("div", { class: "w-24 h-24 mb-4 flex items-center justify-center bg-gray-100 rounded-full" }, [
                        createVNode(_component_Icon, {
                          name: "heroicons:user-group",
                          class: "w-12 h-12 text-gray-600"
                        })
                      ]),
                      createVNode("div", { class: "text-3xl font-bold text-gray-800 mb-2" }, "$3000"),
                      createVNode("p", { class: "text-gray-600 text-center mb-6" }, "增開更新會管理數量"),
                      createVNode(_component_UButton, {
                        color: "green",
                        size: "lg",
                        class: "w-full",
                        onClick: ($event) => addToCart("renewal")
                      }, {
                        default: withCtx(() => [
                          createVNode(_component_Icon, {
                            name: "heroicons:shopping-cart",
                            class: "w-5 h-5 mr-2"
                          }),
                          createTextVNode(" 加入購物車 ")
                        ]),
                        _: 1
                      }, 8, ["onClick"])
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UCard, { class: "bg-white shadow-lg" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col items-center p-6"${_scopeId2}><h3 class="text-xl font-semibold text-gray-800 mb-4"${_scopeId2}>增加議題</h3><div class="w-24 h-24 mb-4 flex items-center justify-center bg-gray-100 rounded-full"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_Icon, {
                    name: "heroicons:document-text",
                    class: "w-12 h-12 text-gray-600"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div class="text-3xl font-bold text-gray-800 mb-2"${_scopeId2}>$1000</div><p class="text-gray-600 text-center mb-6"${_scopeId2}>增加議題使用數量</p>`);
                  _push3(ssrRenderComponent(_component_UButton, {
                    color: "green",
                    size: "lg",
                    class: "w-full",
                    onClick: ($event) => addToCart("issue")
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(ssrRenderComponent(_component_Icon, {
                          name: "heroicons:shopping-cart",
                          class: "w-5 h-5 mr-2"
                        }, null, _parent4, _scopeId3));
                        _push4(` 加入購物車 `);
                      } else {
                        return [
                          createVNode(_component_Icon, {
                            name: "heroicons:shopping-cart",
                            class: "w-5 h-5 mr-2"
                          }),
                          createTextVNode(" 加入購物車 ")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col items-center p-6" }, [
                      createVNode("h3", { class: "text-xl font-semibold text-gray-800 mb-4" }, "增加議題"),
                      createVNode("div", { class: "w-24 h-24 mb-4 flex items-center justify-center bg-gray-100 rounded-full" }, [
                        createVNode(_component_Icon, {
                          name: "heroicons:document-text",
                          class: "w-12 h-12 text-gray-600"
                        })
                      ]),
                      createVNode("div", { class: "text-3xl font-bold text-gray-800 mb-2" }, "$1000"),
                      createVNode("p", { class: "text-gray-600 text-center mb-6" }, "增加議題使用數量"),
                      createVNode(_component_UButton, {
                        color: "green",
                        size: "lg",
                        class: "w-full",
                        onClick: ($event) => addToCart("issue")
                      }, {
                        default: withCtx(() => [
                          createVNode(_component_Icon, {
                            name: "heroicons:shopping-cart",
                            class: "w-5 h-5 mr-2"
                          }),
                          createTextVNode(" 加入購物車 ")
                        ]),
                        _: 1
                      }, 8, ["onClick"])
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "p-8" }, [
                createVNode("div", { class: "flex justify-center items-center min-h-[60vh]" }, [
                  createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl w-full" }, [
                    createVNode(_component_UCard, { class: "bg-white shadow-lg" }, {
                      default: withCtx(() => [
                        createVNode("div", { class: "flex flex-col items-center p-6" }, [
                          createVNode("h3", { class: "text-xl font-semibold text-gray-800 mb-4" }, "增開更新會"),
                          createVNode("div", { class: "w-24 h-24 mb-4 flex items-center justify-center bg-gray-100 rounded-full" }, [
                            createVNode(_component_Icon, {
                              name: "heroicons:user-group",
                              class: "w-12 h-12 text-gray-600"
                            })
                          ]),
                          createVNode("div", { class: "text-3xl font-bold text-gray-800 mb-2" }, "$3000"),
                          createVNode("p", { class: "text-gray-600 text-center mb-6" }, "增開更新會管理數量"),
                          createVNode(_component_UButton, {
                            color: "green",
                            size: "lg",
                            class: "w-full",
                            onClick: ($event) => addToCart("renewal")
                          }, {
                            default: withCtx(() => [
                              createVNode(_component_Icon, {
                                name: "heroicons:shopping-cart",
                                class: "w-5 h-5 mr-2"
                              }),
                              createTextVNode(" 加入購物車 ")
                            ]),
                            _: 1
                          }, 8, ["onClick"])
                        ])
                      ]),
                      _: 1
                    }),
                    createVNode(_component_UCard, { class: "bg-white shadow-lg" }, {
                      default: withCtx(() => [
                        createVNode("div", { class: "flex flex-col items-center p-6" }, [
                          createVNode("h3", { class: "text-xl font-semibold text-gray-800 mb-4" }, "增加議題"),
                          createVNode("div", { class: "w-24 h-24 mb-4 flex items-center justify-center bg-gray-100 rounded-full" }, [
                            createVNode(_component_Icon, {
                              name: "heroicons:document-text",
                              class: "w-12 h-12 text-gray-600"
                            })
                          ]),
                          createVNode("div", { class: "text-3xl font-bold text-gray-800 mb-2" }, "$1000"),
                          createVNode("p", { class: "text-gray-600 text-center mb-6" }, "增加議題使用數量"),
                          createVNode(_component_UButton, {
                            color: "green",
                            size: "lg",
                            class: "w-full",
                            onClick: ($event) => addToCart("issue")
                          }, {
                            default: withCtx(() => [
                              createVNode(_component_Icon, {
                                name: "heroicons:shopping-cart",
                                class: "w-5 h-5 mr-2"
                              }),
                              createTextVNode(" 加入購物車 ")
                            ]),
                            _: 1
                          }, 8, ["onClick"])
                        ])
                      ]),
                      _: 1
                    })
                  ])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/pages/shopping.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=shopping-BJgl_srF.js.map
