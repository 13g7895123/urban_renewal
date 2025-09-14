import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { _ as __nuxt_component_2 } from "../server.mjs";
import { _ as __nuxt_component_1 } from "./Card-DBUe4X9m.js";
import { mergeProps, withCtx, createVNode, createTextVNode, useSSRContext } from "vue";
import { ssrRenderComponent } from "vue/server-renderer";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/hookable/dist/index.mjs";
import "vue-router";
import "ofetch";
import "#internal/nuxt/paths";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/unctx/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/h3/dist/index.mjs";
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
  __name: "index",
  __ssrInlineRender: true,
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_NuxtLink = __nuxt_component_2;
      const _component_UCard = __nuxt_component_1;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({ name: "main" }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`都更計票系統首頁`);
          } else {
            return [
              createTextVNode("都更計票系統首頁")
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="p-8"${_scopeId}><div class="relative"${_scopeId}><div class="absolute top-0 right-20 w-24 h-24 bg-yellow-300 rounded-full"${_scopeId}></div><div class="absolute top-10 left-32 w-32 h-20 bg-white rounded-full opacity-80"${_scopeId}></div><div class="absolute top-20 right-40 w-40 h-24 bg-white rounded-full opacity-60"${_scopeId}></div></div><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-16"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_NuxtLink, {
              to: "/tables/urban-renewal",
              class: "transform hover:scale-105 transition-transform"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(`<div class="w-32 h-32 mb-4 flex items-center justify-center"${_scopeId3}><img src="https://via.placeholder.com/128x128/4A90E2/FFFFFF?text=更新會" alt="更新會管理" class="w-full h-full object-contain"${_scopeId3}></div><h3 class="text-lg font-semibold text-gray-700"${_scopeId3}>更新會管理</h3>`);
                      } else {
                        return [
                          createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                            createVNode("img", {
                              src: "https://via.placeholder.com/128x128/4A90E2/FFFFFF?text=更新會",
                              alt: "更新會管理",
                              class: "w-full h-full object-contain"
                            })
                          ]),
                          createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "更新會管理")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                      default: withCtx(() => [
                        createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                          createVNode("img", {
                            src: "https://via.placeholder.com/128x128/4A90E2/FFFFFF?text=更新會",
                            alt: "更新會管理",
                            class: "w-full h-full object-contain"
                          })
                        ]),
                        createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "更新會管理")
                      ]),
                      _: 1
                    })
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_NuxtLink, {
              to: "/tables/meeting",
              class: "transform hover:scale-105 transition-transform"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(`<div class="w-32 h-32 mb-4 flex items-center justify-center"${_scopeId3}><img src="https://via.placeholder.com/128x128/50B3A2/FFFFFF?text=會議" alt="會議管理" class="w-full h-full object-contain"${_scopeId3}></div><h3 class="text-lg font-semibold text-gray-700"${_scopeId3}>會議管理</h3>`);
                      } else {
                        return [
                          createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                            createVNode("img", {
                              src: "https://via.placeholder.com/128x128/50B3A2/FFFFFF?text=會議",
                              alt: "會議管理",
                              class: "w-full h-full object-contain"
                            })
                          ]),
                          createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "會議管理")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                      default: withCtx(() => [
                        createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                          createVNode("img", {
                            src: "https://via.placeholder.com/128x128/50B3A2/FFFFFF?text=會議",
                            alt: "會議管理",
                            class: "w-full h-full object-contain"
                          })
                        ]),
                        createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "會議管理")
                      ]),
                      _: 1
                    })
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_NuxtLink, {
              to: "/tables/issue",
              class: "transform hover:scale-105 transition-transform"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(`<div class="w-32 h-32 mb-4 flex items-center justify-center"${_scopeId3}><img src="https://via.placeholder.com/128x128/9B59B6/FFFFFF?text=投票" alt="投票管理" class="w-full h-full object-contain"${_scopeId3}></div><h3 class="text-lg font-semibold text-gray-700"${_scopeId3}>投票管理</h3>`);
                      } else {
                        return [
                          createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                            createVNode("img", {
                              src: "https://via.placeholder.com/128x128/9B59B6/FFFFFF?text=投票",
                              alt: "投票管理",
                              class: "w-full h-full object-contain"
                            })
                          ]),
                          createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "投票管理")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                      default: withCtx(() => [
                        createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                          createVNode("img", {
                            src: "https://via.placeholder.com/128x128/9B59B6/FFFFFF?text=投票",
                            alt: "投票管理",
                            class: "w-full h-full object-contain"
                          })
                        ]),
                        createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "投票管理")
                      ]),
                      _: 1
                    })
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_NuxtLink, {
              to: "/pages/user",
              class: "transform hover:scale-105 transition-transform"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(`<div class="w-32 h-32 mb-4 flex items-center justify-center"${_scopeId3}><img src="https://via.placeholder.com/128x128/E67E22/FFFFFF?text=使用者" alt="使用者基本資料變更" class="w-full h-full object-contain"${_scopeId3}></div><h3 class="text-lg font-semibold text-gray-700"${_scopeId3}>使用者基本資料變更</h3>`);
                      } else {
                        return [
                          createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                            createVNode("img", {
                              src: "https://via.placeholder.com/128x128/E67E22/FFFFFF?text=使用者",
                              alt: "使用者基本資料變更",
                              class: "w-full h-full object-contain"
                            })
                          ]),
                          createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "使用者基本資料變更")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                      default: withCtx(() => [
                        createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                          createVNode("img", {
                            src: "https://via.placeholder.com/128x128/E67E22/FFFFFF?text=使用者",
                            alt: "使用者基本資料變更",
                            class: "w-full h-full object-contain"
                          })
                        ]),
                        createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "使用者基本資料變更")
                      ]),
                      _: 1
                    })
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="mt-16 relative h-64"${_scopeId}><div class="absolute bottom-0 left-0 w-full"${_scopeId}><div class="flex justify-between items-end"${_scopeId}><div class="w-20 h-32 bg-green-400 rounded-t-full"${_scopeId}></div><div class="w-24 h-40 bg-yellow-500"${_scopeId}></div><div class="w-16 h-28 bg-orange-400"${_scopeId}></div><div class="w-20 h-36 bg-blue-400"${_scopeId}></div><div class="w-24 h-32 bg-green-400 rounded-t-full"${_scopeId}></div><div class="w-20 h-44 bg-orange-500"${_scopeId}></div></div></div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "p-8" }, [
                createVNode("div", { class: "relative" }, [
                  createVNode("div", { class: "absolute top-0 right-20 w-24 h-24 bg-yellow-300 rounded-full" }),
                  createVNode("div", { class: "absolute top-10 left-32 w-32 h-20 bg-white rounded-full opacity-80" }),
                  createVNode("div", { class: "absolute top-20 right-40 w-40 h-24 bg-white rounded-full opacity-60" })
                ]),
                createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-16" }, [
                  createVNode(_component_NuxtLink, {
                    to: "/tables/urban-renewal",
                    class: "transform hover:scale-105 transition-transform"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                        default: withCtx(() => [
                          createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                            createVNode("img", {
                              src: "https://via.placeholder.com/128x128/4A90E2/FFFFFF?text=更新會",
                              alt: "更新會管理",
                              class: "w-full h-full object-contain"
                            })
                          ]),
                          createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "更新會管理")
                        ]),
                        _: 1
                      })
                    ]),
                    _: 1
                  }),
                  createVNode(_component_NuxtLink, {
                    to: "/tables/meeting",
                    class: "transform hover:scale-105 transition-transform"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                        default: withCtx(() => [
                          createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                            createVNode("img", {
                              src: "https://via.placeholder.com/128x128/50B3A2/FFFFFF?text=會議",
                              alt: "會議管理",
                              class: "w-full h-full object-contain"
                            })
                          ]),
                          createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "會議管理")
                        ]),
                        _: 1
                      })
                    ]),
                    _: 1
                  }),
                  createVNode(_component_NuxtLink, {
                    to: "/tables/issue",
                    class: "transform hover:scale-105 transition-transform"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                        default: withCtx(() => [
                          createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                            createVNode("img", {
                              src: "https://via.placeholder.com/128x128/9B59B6/FFFFFF?text=投票",
                              alt: "投票管理",
                              class: "w-full h-full object-contain"
                            })
                          ]),
                          createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "投票管理")
                        ]),
                        _: 1
                      })
                    ]),
                    _: 1
                  }),
                  createVNode(_component_NuxtLink, {
                    to: "/pages/user",
                    class: "transform hover:scale-105 transition-transform"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UCard, { class: "h-64 flex flex-col items-center justify-center bg-white shadow-lg hover:shadow-xl" }, {
                        default: withCtx(() => [
                          createVNode("div", { class: "w-32 h-32 mb-4 flex items-center justify-center" }, [
                            createVNode("img", {
                              src: "https://via.placeholder.com/128x128/E67E22/FFFFFF?text=使用者",
                              alt: "使用者基本資料變更",
                              class: "w-full h-full object-contain"
                            })
                          ]),
                          createVNode("h3", { class: "text-lg font-semibold text-gray-700" }, "使用者基本資料變更")
                        ]),
                        _: 1
                      })
                    ]),
                    _: 1
                  })
                ]),
                createVNode("div", { class: "mt-16 relative h-64" }, [
                  createVNode("div", { class: "absolute bottom-0 left-0 w-full" }, [
                    createVNode("div", { class: "flex justify-between items-end" }, [
                      createVNode("div", { class: "w-20 h-32 bg-green-400 rounded-t-full" }),
                      createVNode("div", { class: "w-24 h-40 bg-yellow-500" }),
                      createVNode("div", { class: "w-16 h-28 bg-orange-400" }),
                      createVNode("div", { class: "w-20 h-36 bg-blue-400" }),
                      createVNode("div", { class: "w-24 h-32 bg-green-400 rounded-t-full" }),
                      createVNode("div", { class: "w-20 h-44 bg-orange-500" })
                    ])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=index-CRg2OwfG.js.map
