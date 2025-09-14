import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { c as __nuxt_component_0$1, b as __nuxt_component_1 } from "../server.mjs";
import { _ as __nuxt_component_1$1 } from "./Card-DBUe4X9m.js";
import { _ as __nuxt_component_2 } from "./Input-CLveazjF.js";
import { ref, mergeProps, withCtx, createVNode, createTextVNode, withModifiers, useSSRContext } from "vue";
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
import "./useFormGroup-B3564yef.js";
const _sfc_main = {
  __name: "user",
  __ssrInlineRender: true,
  setup(__props) {
    const form = ref({
      accountId: "AW94070886",
      name: "許湘淳",
      nickname: "許湘淳",
      email: "k80686159@yahoo.com.tw",
      phone: "0986820260",
      lineId: "",
      company: "中華開發建築經理股份有限公司",
      jobTitle: ""
    });
    const saveProfile = () => {
      console.log("Saving profile:", form.value);
    };
    const changePassword = () => {
      console.log("Change password clicked");
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_UButton = __nuxt_component_0$1;
      const _component_Icon = __nuxt_component_1;
      const _component_UCard = __nuxt_component_1$1;
      const _component_UInput = __nuxt_component_2;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({ name: "main" }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`使用者基本資料`);
          } else {
            return [
              createTextVNode("使用者基本資料")
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="p-8"${_scopeId}><div class="bg-green-500 text-white p-4 rounded-t-lg flex justify-between items-center"${_scopeId}><h2 class="text-xl font-semibold"${_scopeId}>使用者基本資料</h2>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "white",
              variant: "solid",
              onClick: changePassword
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_Icon, {
                    name: "heroicons:lock-closed",
                    class: "w-5 h-5 mr-2"
                  }, null, _parent3, _scopeId2));
                  _push3(` 變更密碼 `);
                } else {
                  return [
                    createVNode(_component_Icon, {
                      name: "heroicons:lock-closed",
                      class: "w-5 h-5 mr-2"
                    }),
                    createTextVNode(" 變更密碼 ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            _push2(ssrRenderComponent(_component_UCard, { class: "rounded-t-none" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<form class="space-y-6"${_scopeId2}><div class="grid grid-cols-1 md:grid-cols-2 gap-6"${_scopeId2}><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>帳號</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.accountId,
                    "onUpdate:modelValue": ($event) => form.value.accountId = $event,
                    disabled: "",
                    placeholder: "AW94070886",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>姓名</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.name,
                    "onUpdate:modelValue": ($event) => form.value.name = $event,
                    placeholder: "許湘淳",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div></div><div class="grid grid-cols-1 md:grid-cols-2 gap-6"${_scopeId2}><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>暱稱</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.nickname,
                    "onUpdate:modelValue": ($event) => form.value.nickname = $event,
                    placeholder: "許湘淳",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>信箱</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.email,
                    "onUpdate:modelValue": ($event) => form.value.email = $event,
                    type: "email",
                    placeholder: "k80686159@yahoo.com.tw",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div></div><div class="grid grid-cols-1 md:grid-cols-2 gap-6"${_scopeId2}><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>手機號碼</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.phone,
                    "onUpdate:modelValue": ($event) => form.value.phone = $event,
                    placeholder: "0986820260",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>Line帳號</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.lineId,
                    "onUpdate:modelValue": ($event) => form.value.lineId = $event,
                    placeholder: "",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div></div><div class="grid grid-cols-1 md:grid-cols-2 gap-6"${_scopeId2}><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>公司名稱</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.company,
                    "onUpdate:modelValue": ($event) => form.value.company = $event,
                    placeholder: "中華開發建築經理股份有限公司",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>職稱</label>`);
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: form.value.jobTitle,
                    "onUpdate:modelValue": ($event) => form.value.jobTitle = $event,
                    placeholder: "",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div></div><div class="flex justify-end pt-4"${_scopeId2}>`);
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
                      onSubmit: withModifiers(saveProfile, ["prevent"]),
                      class: "space-y-6"
                    }, [
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "帳號"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.accountId,
                            "onUpdate:modelValue": ($event) => form.value.accountId = $event,
                            disabled: "",
                            placeholder: "AW94070886",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "姓名"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.name,
                            "onUpdate:modelValue": ($event) => form.value.name = $event,
                            placeholder: "許湘淳",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "暱稱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.nickname,
                            "onUpdate:modelValue": ($event) => form.value.nickname = $event,
                            placeholder: "許湘淳",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "信箱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.email,
                            "onUpdate:modelValue": ($event) => form.value.email = $event,
                            type: "email",
                            placeholder: "k80686159@yahoo.com.tw",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "手機號碼"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.phone,
                            "onUpdate:modelValue": ($event) => form.value.phone = $event,
                            placeholder: "0986820260",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "Line帳號"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.lineId,
                            "onUpdate:modelValue": ($event) => form.value.lineId = $event,
                            placeholder: "",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "公司名稱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.company,
                            "onUpdate:modelValue": ($event) => form.value.company = $event,
                            placeholder: "中華開發建築經理股份有限公司",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "職稱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.jobTitle,
                            "onUpdate:modelValue": ($event) => form.value.jobTitle = $event,
                            placeholder: "",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
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
                createVNode("div", { class: "bg-green-500 text-white p-4 rounded-t-lg flex justify-between items-center" }, [
                  createVNode("h2", { class: "text-xl font-semibold" }, "使用者基本資料"),
                  createVNode(_component_UButton, {
                    color: "white",
                    variant: "solid",
                    onClick: changePassword
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_Icon, {
                        name: "heroicons:lock-closed",
                        class: "w-5 h-5 mr-2"
                      }),
                      createTextVNode(" 變更密碼 ")
                    ]),
                    _: 1
                  })
                ]),
                createVNode(_component_UCard, { class: "rounded-t-none" }, {
                  default: withCtx(() => [
                    createVNode("form", {
                      onSubmit: withModifiers(saveProfile, ["prevent"]),
                      class: "space-y-6"
                    }, [
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "帳號"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.accountId,
                            "onUpdate:modelValue": ($event) => form.value.accountId = $event,
                            disabled: "",
                            placeholder: "AW94070886",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "姓名"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.name,
                            "onUpdate:modelValue": ($event) => form.value.name = $event,
                            placeholder: "許湘淳",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "暱稱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.nickname,
                            "onUpdate:modelValue": ($event) => form.value.nickname = $event,
                            placeholder: "許湘淳",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "信箱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.email,
                            "onUpdate:modelValue": ($event) => form.value.email = $event,
                            type: "email",
                            placeholder: "k80686159@yahoo.com.tw",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "手機號碼"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.phone,
                            "onUpdate:modelValue": ($event) => form.value.phone = $event,
                            placeholder: "0986820260",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "Line帳號"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.lineId,
                            "onUpdate:modelValue": ($event) => form.value.lineId = $event,
                            placeholder: "",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
                      ]),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "公司名稱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.company,
                            "onUpdate:modelValue": ($event) => form.value.company = $event,
                            placeholder: "中華開發建築經理股份有限公司",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "職稱"),
                          createVNode(_component_UInput, {
                            modelValue: form.value.jobTitle,
                            "onUpdate:modelValue": ($event) => form.value.jobTitle = $event,
                            placeholder: "",
                            class: "w-full"
                          }, null, 8, ["modelValue", "onUpdate:modelValue"])
                        ])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/pages/user.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=user-_weZBBoz.js.map
