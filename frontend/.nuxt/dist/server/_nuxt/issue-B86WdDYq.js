import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { b as __nuxt_component_1, c as __nuxt_component_0$1 } from "../server.mjs";
import { _ as __nuxt_component_5 } from "./SelectMenu-CzPdyyER.js";
import { _ as __nuxt_component_1$1 } from "./Card-DBUe4X9m.js";
import { ref, mergeProps, withCtx, createTextVNode, createVNode, withModifiers, useSSRContext } from "vue";
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
import "@tanstack/vue-virtual";
import "./useFormGroup-B3564yef.js";
const _sfc_main = {
  __name: "issue",
  __ssrInlineRender: true,
  setup(__props) {
    const selectedRenewal = ref(null);
    const selectedMeetingDate = ref(null);
    const renewalOptions = ref([
      { label: "更新會 1", value: "renewal1" },
      { label: "更新會 2", value: "renewal2" },
      { label: "更新會 3", value: "renewal3" }
    ]);
    const meetingDateOptions = ref([
      { label: "2024-01-15", value: "2024-01-15" },
      { label: "2024-02-15", value: "2024-02-15" },
      { label: "2024-03-15", value: "2024-03-15" }
    ]);
    const submitIssue = () => {
      console.log("Submitting issue:", {
        renewal: selectedRenewal.value,
        meetingDate: selectedMeetingDate.value
      });
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_Icon = __nuxt_component_1;
      const _component_USelectMenu = __nuxt_component_5;
      const _component_UCard = __nuxt_component_1$1;
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
            _push2(`<div class="p-8"${_scopeId}><div class="bg-green-500 text-white p-6 rounded-lg mb-6"${_scopeId}><div class="flex items-center"${_scopeId}><div class="bg-white/20 p-3 rounded-lg mr-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:document-text",
              class: "w-8 h-8 text-white"
            }, null, _parent2, _scopeId));
            _push2(`</div><h2 class="text-2xl font-semibold"${_scopeId}>投票管理</h2></div></div><div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"${_scopeId}><div${_scopeId}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>選擇更新會</label>`);
            _push2(ssrRenderComponent(_component_USelectMenu, {
              modelValue: selectedRenewal.value,
              "onUpdate:modelValue": ($event) => selectedRenewal.value = $event,
              options: renewalOptions.value,
              placeholder: "請選擇更新會",
              class: "w-full"
            }, null, _parent2, _scopeId));
            _push2(`</div><div${_scopeId}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>選擇會議日期</label>`);
            _push2(ssrRenderComponent(_component_USelectMenu, {
              modelValue: selectedMeetingDate.value,
              "onUpdate:modelValue": ($event) => selectedMeetingDate.value = $event,
              options: meetingDateOptions.value,
              placeholder: "請選擇會議日期",
              class: "w-full"
            }, null, _parent2, _scopeId));
            _push2(`</div></div>`);
            _push2(ssrRenderComponent(_component_UCard, null, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<form${_scopeId2}><div class="space-y-4"${_scopeId2}><div${_scopeId2}><label class="block text-sm font-medium text-gray-700 mb-2"${_scopeId2}>會議名稱列表</label><div class="border rounded-lg p-6 min-h-[300px] bg-gray-50"${_scopeId2}><p class="text-gray-500 text-center"${_scopeId2}>請選擇更新會及會議日期</p></div></div></div><div class="flex justify-end mt-6"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UButton, {
                    type: "submit",
                    color: "green",
                    size: "lg"
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(` 確認 `);
                      } else {
                        return [
                          createTextVNode(" 確認 ")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</div></form>`);
                } else {
                  return [
                    createVNode("form", {
                      onSubmit: withModifiers(submitIssue, ["prevent"])
                    }, [
                      createVNode("div", { class: "space-y-4" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "會議名稱列表"),
                          createVNode("div", { class: "border rounded-lg p-6 min-h-[300px] bg-gray-50" }, [
                            createVNode("p", { class: "text-gray-500 text-center" }, "請選擇更新會及會議日期")
                          ])
                        ])
                      ]),
                      createVNode("div", { class: "flex justify-end mt-6" }, [
                        createVNode(_component_UButton, {
                          type: "submit",
                          color: "green",
                          size: "lg"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" 確認 ")
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
                createVNode("div", { class: "bg-green-500 text-white p-6 rounded-lg mb-6" }, [
                  createVNode("div", { class: "flex items-center" }, [
                    createVNode("div", { class: "bg-white/20 p-3 rounded-lg mr-4" }, [
                      createVNode(_component_Icon, {
                        name: "heroicons:document-text",
                        class: "w-8 h-8 text-white"
                      })
                    ]),
                    createVNode("h2", { class: "text-2xl font-semibold" }, "投票管理")
                  ])
                ]),
                createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" }, [
                  createVNode("div", null, [
                    createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "選擇更新會"),
                    createVNode(_component_USelectMenu, {
                      modelValue: selectedRenewal.value,
                      "onUpdate:modelValue": ($event) => selectedRenewal.value = $event,
                      options: renewalOptions.value,
                      placeholder: "請選擇更新會",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "options"])
                  ]),
                  createVNode("div", null, [
                    createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "選擇會議日期"),
                    createVNode(_component_USelectMenu, {
                      modelValue: selectedMeetingDate.value,
                      "onUpdate:modelValue": ($event) => selectedMeetingDate.value = $event,
                      options: meetingDateOptions.value,
                      placeholder: "請選擇會議日期",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "options"])
                  ])
                ]),
                createVNode(_component_UCard, null, {
                  default: withCtx(() => [
                    createVNode("form", {
                      onSubmit: withModifiers(submitIssue, ["prevent"])
                    }, [
                      createVNode("div", { class: "space-y-4" }, [
                        createVNode("div", null, [
                          createVNode("label", { class: "block text-sm font-medium text-gray-700 mb-2" }, "會議名稱列表"),
                          createVNode("div", { class: "border rounded-lg p-6 min-h-[300px] bg-gray-50" }, [
                            createVNode("p", { class: "text-gray-500 text-center" }, "請選擇更新會及會議日期")
                          ])
                        ])
                      ]),
                      createVNode("div", { class: "flex justify-end mt-6" }, [
                        createVNode(_component_UButton, {
                          type: "submit",
                          color: "green",
                          size: "lg"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" 確認 ")
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/tables/issue.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=issue-B86WdDYq.js.map
