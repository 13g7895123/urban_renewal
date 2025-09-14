import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { b as __nuxt_component_1, c as __nuxt_component_0$1 } from "../server.mjs";
import { _ as __nuxt_component_1$1 } from "./Card-DBUe4X9m.js";
import { _ as __nuxt_component_5 } from "./SelectMenu-CzPdyyER.js";
import { ref, mergeProps, withCtx, createVNode, createTextVNode, createBlock, createCommentVNode, openBlock, Fragment, renderList, toDisplayString, useSSRContext } from "vue";
import { ssrRenderComponent, ssrRenderList, ssrInterpolate } from "vue/server-renderer";
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
import "@tanstack/vue-virtual";
import "./useFormGroup-B3564yef.js";
const _sfc_main = {
  __name: "order",
  __ssrInlineRender: true,
  setup(__props) {
    const pageSize = ref(10);
    const orders = ref([
      // Example order structure (commented out to show empty state):
      // {
      //   orderNumber: 'ORD001',
      //   content: '增開更新會',
      //   amount: 3000,
      //   orderDate: '2024-01-15 14:30:00'
      // }
    ]);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_Icon = __nuxt_component_1;
      const _component_UCard = __nuxt_component_1$1;
      const _component_USelectMenu = __nuxt_component_5;
      const _component_UButton = __nuxt_component_0$1;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({ name: "main" }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`購買紀錄`);
          } else {
            return [
              createTextVNode("購買紀錄")
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="p-8"${_scopeId}><div class="bg-green-500 text-white p-6 rounded-lg mb-6"${_scopeId}><div class="flex items-center"${_scopeId}><div class="bg-white/20 p-3 rounded-lg mr-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:document-text",
              class: "w-8 h-8 text-white"
            }, null, _parent2, _scopeId));
            _push2(`</div><h2 class="text-2xl font-semibold"${_scopeId}>購買紀錄</h2></div></div>`);
            _push2(ssrRenderComponent(_component_UCard, null, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="overflow-x-auto"${_scopeId2}><table class="w-full"${_scopeId2}><thead${_scopeId2}><tr class="border-b"${_scopeId2}><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>訂單編號</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>訂購內容</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>總金額</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>下單時間</th></tr></thead><tbody${_scopeId2}>`);
                  if (orders.value.length === 0) {
                    _push3(`<tr${_scopeId2}><td colspan="4" class="p-8 text-center text-gray-500"${_scopeId2}> 沒有資料 </td></tr>`);
                  } else {
                    _push3(`<!---->`);
                  }
                  _push3(`<!--[-->`);
                  ssrRenderList(orders.value, (order, index) => {
                    _push3(`<tr class="border-b hover:bg-gray-50"${_scopeId2}><td class="p-3 text-sm"${_scopeId2}>${ssrInterpolate(order.orderNumber)}</td><td class="p-3 text-sm"${_scopeId2}>${ssrInterpolate(order.content)}</td><td class="p-3 text-sm"${_scopeId2}>$${ssrInterpolate(order.amount)}</td><td class="p-3 text-sm"${_scopeId2}>${ssrInterpolate(order.orderDate)}</td></tr>`);
                  });
                  _push3(`<!--]--></tbody></table></div><div class="flex justify-between items-center mt-4 pt-4 border-t"${_scopeId2}><div class="text-sm text-gray-500"${_scopeId2}> 每頁顯示： `);
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: pageSize.value,
                    "onUpdate:modelValue": ($event) => pageSize.value = $event,
                    options: [10, 20, 50],
                    size: "sm",
                    class: "inline-block w-20 ml-2"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div class="text-sm text-gray-500"${_scopeId2}> - - </div><div class="flex gap-2"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UButton, {
                    variant: "ghost",
                    size: "sm",
                    disabled: ""
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(ssrRenderComponent(_component_Icon, {
                          name: "heroicons:chevron-left",
                          class: "w-4 h-4"
                        }, null, _parent4, _scopeId3));
                      } else {
                        return [
                          createVNode(_component_Icon, {
                            name: "heroicons:chevron-left",
                            class: "w-4 h-4"
                          })
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(ssrRenderComponent(_component_UButton, {
                    variant: "ghost",
                    size: "sm",
                    class: "bg-blue-500 text-white"
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(`1`);
                      } else {
                        return [
                          createTextVNode("1")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(ssrRenderComponent(_component_UButton, {
                    variant: "ghost",
                    size: "sm",
                    disabled: ""
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(ssrRenderComponent(_component_Icon, {
                          name: "heroicons:chevron-right",
                          class: "w-4 h-4"
                        }, null, _parent4, _scopeId3));
                      } else {
                        return [
                          createVNode(_component_Icon, {
                            name: "heroicons:chevron-right",
                            class: "w-4 h-4"
                          })
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</div></div>`);
                } else {
                  return [
                    createVNode("div", { class: "overflow-x-auto" }, [
                      createVNode("table", { class: "w-full" }, [
                        createVNode("thead", null, [
                          createVNode("tr", { class: "border-b" }, [
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "訂單編號"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "訂購內容"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "總金額"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "下單時間")
                          ])
                        ]),
                        createVNode("tbody", null, [
                          orders.value.length === 0 ? (openBlock(), createBlock("tr", { key: 0 }, [
                            createVNode("td", {
                              colspan: "4",
                              class: "p-8 text-center text-gray-500"
                            }, " 沒有資料 ")
                          ])) : createCommentVNode("", true),
                          (openBlock(true), createBlock(Fragment, null, renderList(orders.value, (order, index) => {
                            return openBlock(), createBlock("tr", {
                              key: index,
                              class: "border-b hover:bg-gray-50"
                            }, [
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(order.orderNumber), 1),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(order.content), 1),
                              createVNode("td", { class: "p-3 text-sm" }, "$" + toDisplayString(order.amount), 1),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(order.orderDate), 1)
                            ]);
                          }), 128))
                        ])
                      ])
                    ]),
                    createVNode("div", { class: "flex justify-between items-center mt-4 pt-4 border-t" }, [
                      createVNode("div", { class: "text-sm text-gray-500" }, [
                        createTextVNode(" 每頁顯示： "),
                        createVNode(_component_USelectMenu, {
                          modelValue: pageSize.value,
                          "onUpdate:modelValue": ($event) => pageSize.value = $event,
                          options: [10, 20, 50],
                          size: "sm",
                          class: "inline-block w-20 ml-2"
                        }, null, 8, ["modelValue", "onUpdate:modelValue"])
                      ]),
                      createVNode("div", { class: "text-sm text-gray-500" }, " - - "),
                      createVNode("div", { class: "flex gap-2" }, [
                        createVNode(_component_UButton, {
                          variant: "ghost",
                          size: "sm",
                          disabled: ""
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_Icon, {
                              name: "heroicons:chevron-left",
                              class: "w-4 h-4"
                            })
                          ]),
                          _: 1
                        }),
                        createVNode(_component_UButton, {
                          variant: "ghost",
                          size: "sm",
                          class: "bg-blue-500 text-white"
                        }, {
                          default: withCtx(() => [
                            createTextVNode("1")
                          ]),
                          _: 1
                        }),
                        createVNode(_component_UButton, {
                          variant: "ghost",
                          size: "sm",
                          disabled: ""
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_Icon, {
                              name: "heroicons:chevron-right",
                              class: "w-4 h-4"
                            })
                          ]),
                          _: 1
                        })
                      ])
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "p-8" }, [
                createVNode("div", { class: "bg-green-500 text-white p-6 rounded-lg mb-6" }, [
                  createVNode("div", { class: "flex items-center" }, [
                    createVNode("div", { class: "bg-white/20 p-3 rounded-lg mr-4" }, [
                      createVNode(_component_Icon, {
                        name: "heroicons:document-text",
                        class: "w-8 h-8 text-white"
                      })
                    ]),
                    createVNode("h2", { class: "text-2xl font-semibold" }, "購買紀錄")
                  ])
                ]),
                createVNode(_component_UCard, null, {
                  default: withCtx(() => [
                    createVNode("div", { class: "overflow-x-auto" }, [
                      createVNode("table", { class: "w-full" }, [
                        createVNode("thead", null, [
                          createVNode("tr", { class: "border-b" }, [
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "訂單編號"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "訂購內容"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "總金額"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "下單時間")
                          ])
                        ]),
                        createVNode("tbody", null, [
                          orders.value.length === 0 ? (openBlock(), createBlock("tr", { key: 0 }, [
                            createVNode("td", {
                              colspan: "4",
                              class: "p-8 text-center text-gray-500"
                            }, " 沒有資料 ")
                          ])) : createCommentVNode("", true),
                          (openBlock(true), createBlock(Fragment, null, renderList(orders.value, (order, index) => {
                            return openBlock(), createBlock("tr", {
                              key: index,
                              class: "border-b hover:bg-gray-50"
                            }, [
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(order.orderNumber), 1),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(order.content), 1),
                              createVNode("td", { class: "p-3 text-sm" }, "$" + toDisplayString(order.amount), 1),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(order.orderDate), 1)
                            ]);
                          }), 128))
                        ])
                      ])
                    ]),
                    createVNode("div", { class: "flex justify-between items-center mt-4 pt-4 border-t" }, [
                      createVNode("div", { class: "text-sm text-gray-500" }, [
                        createTextVNode(" 每頁顯示： "),
                        createVNode(_component_USelectMenu, {
                          modelValue: pageSize.value,
                          "onUpdate:modelValue": ($event) => pageSize.value = $event,
                          options: [10, 20, 50],
                          size: "sm",
                          class: "inline-block w-20 ml-2"
                        }, null, 8, ["modelValue", "onUpdate:modelValue"])
                      ]),
                      createVNode("div", { class: "text-sm text-gray-500" }, " - - "),
                      createVNode("div", { class: "flex gap-2" }, [
                        createVNode(_component_UButton, {
                          variant: "ghost",
                          size: "sm",
                          disabled: ""
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_Icon, {
                              name: "heroicons:chevron-left",
                              class: "w-4 h-4"
                            })
                          ]),
                          _: 1
                        }),
                        createVNode(_component_UButton, {
                          variant: "ghost",
                          size: "sm",
                          class: "bg-blue-500 text-white"
                        }, {
                          default: withCtx(() => [
                            createTextVNode("1")
                          ]),
                          _: 1
                        }),
                        createVNode(_component_UButton, {
                          variant: "ghost",
                          size: "sm",
                          disabled: ""
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_Icon, {
                              name: "heroicons:chevron-right",
                              class: "w-4 h-4"
                            })
                          ]),
                          _: 1
                        })
                      ])
                    ])
                  ]),
                  _: 1
                })
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/tables/order.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=order-CPYpyE4O.js.map
