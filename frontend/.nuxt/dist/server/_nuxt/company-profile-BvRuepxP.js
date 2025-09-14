import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { _ as __nuxt_component_1 } from "./Card-DBUe4X9m.js";
import { _ as __nuxt_component_2 } from "./Input-CLveazjF.js";
import { c as __nuxt_component_0$1, b as __nuxt_component_1$1 } from "../server.mjs";
import { ref, mergeProps, withCtx, createVNode, createTextVNode, withModifiers, createBlock, openBlock, Fragment, renderList, toDisplayString, useSSRContext } from "vue";
import { ssrRenderComponent, ssrRenderList, ssrInterpolate } from "vue/server-renderer";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/hookable/dist/index.mjs";
import "vue-router";
import "tailwind-merge";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/defu/dist/defu.mjs";
import "./useFormGroup-B3564yef.js";
import "@vueuse/core";
import "ofetch";
import "#internal/nuxt/paths";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/unctx/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/h3/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/radix3/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/ufo/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/klona/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/@unhead/vue/dist/index.mjs";
import "@iconify/vue";
import "ohash/utils";
import "@iconify/utils/lib/css/icon";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/perfect-debounce/dist/index.mjs";
const _sfc_main = {
  __name: "company-profile",
  __ssrInlineRender: true,
  setup(__props) {
    const form = ref({
      companyName: "中華開發建築經理股份有限公司",
      taxId: "94070886",
      companyPhone: "02-6604-3889",
      maxRenewalCount: 1,
      maxIssueCount: 8
    });
    const managers = ref([
      {
        id: "AW94070886",
        name: "",
        company: ""
      },
      {
        id: "ted3",
        name: "",
        company: ""
      },
      {
        id: "wenchi886",
        name: "",
        company: ""
      }
    ]);
    const saveCompanyProfile = () => {
      console.log("Saving company profile:", form.value);
    };
    const addNewManager = () => {
      console.log("Adding new manager");
    };
    const viewUser = (manager) => {
      console.log("Viewing user:", manager);
    };
    const deleteManager = (index) => {
      managers.value.splice(index, 1);
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_UCard = __nuxt_component_1;
      const _component_UInput = __nuxt_component_2;
      const _component_UButton = __nuxt_component_0$1;
      const _component_Icon = __nuxt_component_1$1;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({ name: "main" }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`企業基本資料`);
          } else {
            return [
              createTextVNode("企業基本資料")
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="p-8"${_scopeId}><div class="bg-green-500 text-white p-4 rounded-t-lg"${_scopeId}><h2 class="text-xl font-semibold"${_scopeId}>企業基本資料</h2></div>`);
            _push2(ssrRenderComponent(_component_UCard, { class: "rounded-t-none" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<form class="space-y-6"${_scopeId2}><div class="grid grid-cols-1 md:grid-cols-2 gap-6"${_scopeId2}><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>企業名稱</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.companyName,
                    "onUpdate:modelValue": ($event) => form.value.companyName = $event,
                    placeholder: "中華開發建築經理股份有限公司",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>統一編號</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.taxId,
                    "onUpdate:modelValue": ($event) => form.value.taxId = $event,
                    placeholder: "94070886",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div></div><div class="grid grid-cols-1 md:grid-cols-2 gap-6"${_scopeId2}><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>企業電話</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.companyPhone,
                    "onUpdate:modelValue": ($event) => form.value.companyPhone = $event,
                    placeholder: "02-6604-3889",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>最大更新會數量</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.maxRenewalCount,
                    "onUpdate:modelValue": ($event) => form.value.maxRenewalCount = $event,
                    type: "number",
                    placeholder: "1",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div></div><div class="grid grid-cols-1 md:grid-cols-2 gap-6"${_scopeId2}><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>最大議題數量</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.maxIssueCount,
                    "onUpdate:modelValue": ($event) => form.value.maxIssueCount = $event,
                    type: "number",
                    placeholder: "8",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div></div><div class="mt-8 flex items-end gap-4"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UButton, {
                    color: "green",
                    size: "sm",
                    onClick: addNewManager
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(ssrRenderComponent(_component_Icon, {
                          name: "heroicons:plus",
                          class: "w-4 h-4 mr-1"
                        }, null, _parent4, _scopeId3));
                        _push4(` 新增使用者 `);
                      } else {
                        return [
                          createVNode(_component_Icon, {
                            name: "heroicons:plus",
                            class: "w-4 h-4 mr-1"
                          }),
                          createTextVNode(" 新增使用者 ")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</div><div class="mt-6"${_scopeId2}><h3 class="text-lg font-medium text-gray-700 mb-4"${_scopeId2}>企業管理者</h3><div class="border rounded-lg overflow-hidden"${_scopeId2}><table class="w-full"${_scopeId2}><tbody${_scopeId2}><!--[-->`);
                  ssrRenderList(managers.value, (manager, index) => {
                    _push3(`<tr class="border-b"${_scopeId2}><td class="p-4 text-gray-700"${_scopeId2}>${ssrInterpolate(manager.id)}</td><td class="p-4 text-gray-700"${_scopeId2}>${ssrInterpolate(manager.name)}</td><td class="p-4 text-gray-700"${_scopeId2}>${ssrInterpolate(manager.company)}</td><td class="p-4 text-right space-x-2"${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_UButton, {
                      color: "orange",
                      size: "xs",
                      onClick: ($event) => viewUser(manager)
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` 查詢取用者 `);
                        } else {
                          return [
                            createTextVNode(" 查詢取用者 ")
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                    _push3(ssrRenderComponent(_component_UButton, {
                      color: "red",
                      size: "xs",
                      onClick: ($event) => deleteManager(index)
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` 刪除 `);
                        } else {
                          return [
                            createTextVNode(" 刪除 ")
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                    _push3(`</td></tr>`);
                  });
                  _push3(`<!--]--></tbody></table></div></div><div class="mt-6"${_scopeId2}><h3 class="text-lg font-medium text-gray-700 mb-4"${_scopeId2}>企業使用者</h3></div><div class="flex justify-end pt-4"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UButton, {
                    type: "submit",
                    color: "green",
                    size: "lg"
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(` 儲存 `);
                      } else {
                        return [
                          createTextVNode(" 儲存 ")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</div></form>`);
                } else {
                  return [
                    createVNode("form", {
                      onSubmit: withModifiers(saveCompanyProfile, ["prevent"]),
                      class: "space-y-6"
                    }, [
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "企業名稱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.companyName,
                            "onUpdate:modelValue": ($event) => form.value.companyName = $event,
                            placeholder: "中華開發建築經理股份有限公司",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "統一編號"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.taxId,
                            "onUpdate:modelValue": ($event) => form.value.taxId = $event,
                            placeholder: "94070886",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "企業電話"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.companyPhone,
                            "onUpdate:modelValue": ($event) => form.value.companyPhone = $event,
                            placeholder: "02-6604-3889",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "最大更新會數量"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.maxRenewalCount,
                            "onUpdate:modelValue": ($event) => form.value.maxRenewalCount = $event,
                            type: "number",
                            placeholder: "1",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "最大議題數量"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.maxIssueCount,
                            "onUpdate:modelValue": ($event) => form.value.maxIssueCount = $event,
                            type: "number",
                            placeholder: "8",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "mt-8 flex items-end gap-4" }, [
                        createVNode(_component_UButton, {
                          color: "green",
                          size: "sm",
                          onClick: addNewManager
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_Icon, {
                              name: "heroicons:plus",
                              class: "w-4 h-4 mr-1"
                            }),
                            createTextVNode(" 新增使用者 ")
                          ]),
                          _: 1
                        })
                      ]),
                      createVNode("div", { class: "mt-6" }, [
                        createVNode("h3", { class: "text-lg font-medium text-gray-700 mb-4" }, "企業管理者"),
                        createVNode("div", { class: "border rounded-lg overflow-hidden" }, [
                          createVNode("table", { class: "w-full" }, [
                            createVNode("tbody", null, [
                              (openBlock(true), createBlock(Fragment, null, renderList(managers.value, (manager, index) => {
                                return openBlock(), createBlock("tr", {
                                  key: index,
                                  class: "border-b"
                                }, [
                                  createVNode("td", { class: "p-4 text-gray-700" }, toDisplayString(manager.id), 1),
                                  createVNode("td", { class: "p-4 text-gray-700" }, toDisplayString(manager.name), 1),
                                  createVNode("td", { class: "p-4 text-gray-700" }, toDisplayString(manager.company), 1),
                                  createVNode("td", { class: "p-4 text-right space-x-2" }, [
                                    createVNode(_component_UButton, {
                                      color: "orange",
                                      size: "xs",
                                      onClick: ($event) => viewUser(manager)
                                    }, {
                                      default: withCtx(() => [
                                        createTextVNode(" 查詢取用者 ")
                                      ]),
                                      _: 2
                                    }, 1032, ["onClick"]),
                                    createVNode(_component_UButton, {
                                      color: "red",
                                      size: "xs",
                                      onClick: ($event) => deleteManager(index)
                                    }, {
                                      default: withCtx(() => [
                                        createTextVNode(" 刪除 ")
                                      ]),
                                      _: 2
                                    }, 1032, ["onClick"])
                                  ])
                                ]);
                              }), 128))
                            ])
                          ])
                        ])
                      ]),
                      createVNode("div", { class: "mt-6" }, [
                        createVNode("h3", { class: "text-lg font-medium text-gray-700 mb-4" }, "企業使用者")
                      ]),
                      createVNode("div", { class: "flex justify-end pt-4" }, [
                        createVNode(_component_UButton, {
                          type: "submit",
                          color: "green",
                          size: "lg"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" 儲存 ")
                          ]),
                          _: 1
                        })
                      ])
                    ], 32)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "p-8" }, [
                createVNode("div", { class: "bg-green-500 text-white p-4 rounded-t-lg" }, [
                  createVNode("h2", { class: "text-xl font-semibold" }, "企業基本資料")
                ]),
                createVNode(_component_UCard, { class: "rounded-t-none" }, {
                  default: withCtx(() => [
                    createVNode("form", {
                      onSubmit: withModifiers(saveCompanyProfile, ["prevent"]),
                      class: "space-y-6"
                    }, [
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "企業名稱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.companyName,
                            "onUpdate:modelValue": ($event) => form.value.companyName = $event,
                            placeholder: "中華開發建築經理股份有限公司",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "統一編號"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.taxId,
                            "onUpdate:modelValue": ($event) => form.value.taxId = $event,
                            placeholder: "94070886",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "企業電話"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.companyPhone,
                            "onUpdate:modelValue": ($event) => form.value.companyPhone = $event,
                            placeholder: "02-6604-3889",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "最大更新會數量"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.maxRenewalCount,
                            "onUpdate:modelValue": ($event) => form.value.maxRenewalCount = $event,
                            type: "number",
                            placeholder: "1",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "最大議題數量"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.maxIssueCount,
                            "onUpdate:modelValue": ($event) => form.value.maxIssueCount = $event,
                            type: "number",
                            placeholder: "8",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "mt-8 flex items-end gap-4" }, [
                        createVNode(_component_UButton, {
                          color: "green",
                          size: "sm",
                          onClick: addNewManager
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_Icon, {
                              name: "heroicons:plus",
                              class: "w-4 h-4 mr-1"
                            }),
                            createTextVNode(" 新增使用者 ")
                          ]),
                          _: 1
                        })
                      ]),
                      createVNode("div", { class: "mt-6" }, [
                        createVNode("h3", { class: "text-lg font-medium text-gray-700 mb-4" }, "企業管理者"),
                        createVNode("div", { class: "border rounded-lg overflow-hidden" }, [
                          createVNode("table", { class: "w-full" }, [
                            createVNode("tbody", null, [
                              (openBlock(true), createBlock(Fragment, null, renderList(managers.value, (manager, index) => {
                                return openBlock(), createBlock("tr", {
                                  key: index,
                                  class: "border-b"
                                }, [
                                  createVNode("td", { class: "p-4 text-gray-700" }, toDisplayString(manager.id), 1),
                                  createVNode("td", { class: "p-4 text-gray-700" }, toDisplayString(manager.name), 1),
                                  createVNode("td", { class: "p-4 text-gray-700" }, toDisplayString(manager.company), 1),
                                  createVNode("td", { class: "p-4 text-right space-x-2" }, [
                                    createVNode(_component_UButton, {
                                      color: "orange",
                                      size: "xs",
                                      onClick: ($event) => viewUser(manager)
                                    }, {
                                      default: withCtx(() => [
                                        createTextVNode(" 查詢取用者 ")
                                      ]),
                                      _: 2
                                    }, 1032, ["onClick"]),
                                    createVNode(_component_UButton, {
                                      color: "red",
                                      size: "xs",
                                      onClick: ($event) => deleteManager(index)
                                    }, {
                                      default: withCtx(() => [
                                        createTextVNode(" 刪除 ")
                                      ]),
                                      _: 2
                                    }, 1032, ["onClick"])
                                  ])
                                ]);
                              }), 128))
                            ])
                          ])
                        ])
                      ]),
                      createVNode("div", { class: "mt-6" }, [
                        createVNode("h3", { class: "text-lg font-medium text-gray-700 mb-4" }, "企業使用者")
                      ]),
                      createVNode("div", { class: "flex justify-end pt-4" }, [
                        createVNode(_component_UButton, {
                          type: "submit",
                          color: "green",
                          size: "lg"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" 儲存 ")
                          ]),
                          _: 1
                        })
                      ])
                    ], 32)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/tables/company-profile.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=company-profile-BvRuepxP.js.map
