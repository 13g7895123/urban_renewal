import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { a as _export_sfc, m as mergeConfig, d as useUI, t as twMerge, e as appConfig, b as __nuxt_component_1, c as __nuxt_component_0$1 } from "../server.mjs";
import { _ as __nuxt_component_1$1 } from "./Card-DBUe4X9m.js";
import { useId as useId$1, defineComponent, mergeProps, toRef, computed, useSSRContext, ref, withCtx, createVNode, createTextVNode, createBlock, openBlock, Fragment, renderList, toDisplayString } from "vue";
import { twJoin } from "tailwind-merge";
import { u as useFormGroup } from "./useFormGroup-B3564yef.js";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/hookable/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/klona/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/defu/dist/defu.mjs";
import "#internal/nuxt/paths";
import { ssrRenderAttrs, ssrRenderClass, ssrLooseContain, ssrGetDynamicModelProps, ssrRenderAttr, ssrRenderSlot, ssrInterpolate, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
import { _ as __nuxt_component_5 } from "./SelectMenu-CzPdyyER.js";
import "vue-router";
import "ofetch";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/unctx/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/h3/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/radix3/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/ufo/dist/index.mjs";
import "@vueuse/core";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/@unhead/vue/dist/index.mjs";
import "@iconify/vue";
import "ohash/utils";
import "@iconify/utils/lib/css/icon";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/perfect-debounce/dist/index.mjs";
import "@tanstack/vue-virtual";
const useId = useId$1;
const checkbox = {
  wrapper: "relative flex items-start",
  container: "flex items-center h-5",
  base: "h-4 w-4 dark:checked:bg-current dark:checked:border-transparent dark:indeterminate:bg-current dark:indeterminate:border-transparent disabled:opacity-50 disabled:cursor-not-allowed focus:ring-0 focus:ring-transparent focus:ring-offset-transparent",
  form: "form-checkbox",
  rounded: "rounded",
  color: "text-{color}-500 dark:text-{color}-400",
  background: "bg-white dark:bg-gray-900",
  border: "border border-gray-300 dark:border-gray-700",
  ring: "focus-visible:ring-2 focus-visible:ring-{color}-500 dark:focus-visible:ring-{color}-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900",
  inner: "ms-3 flex flex-col",
  label: "text-sm font-medium text-gray-700 dark:text-gray-200",
  required: "text-sm text-red-500 dark:text-red-400",
  help: "text-sm text-gray-500 dark:text-gray-400",
  default: {
    color: "primary"
  }
};
const config = mergeConfig(appConfig.ui.strategy, appConfig.ui.checkbox, checkbox);
const _sfc_main$1 = defineComponent({
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default: () => null
    },
    value: {
      type: [String, Number, Boolean, Object],
      default: null
    },
    modelValue: {
      type: [Boolean, Array],
      default: null
    },
    name: {
      type: String,
      default: null
    },
    disabled: {
      type: Boolean,
      default: false
    },
    indeterminate: {
      type: Boolean,
      default: void 0
    },
    help: {
      type: String,
      default: null
    },
    label: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
    color: {
      type: String,
      default: () => config.default.color,
      validator(value) {
        return appConfig.ui.colors.includes(value);
      }
    },
    inputClass: {
      type: String,
      default: ""
    },
    class: {
      type: [String, Object, Array],
      default: () => ""
    },
    ui: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ["update:modelValue", "change"],
  setup(props, { emit }) {
    const { ui, attrs } = useUI("checkbox", toRef(props, "ui"), config, toRef(props, "class"));
    const { emitFormChange, color, name, inputId: _inputId } = useFormGroup(props);
    const inputId = _inputId.value ?? useId();
    const toggle = computed({
      get() {
        return props.modelValue;
      },
      set(value) {
        emit("update:modelValue", value);
      }
    });
    const onChange = (event) => {
      emit("change", event.target.checked);
      emitFormChange();
    };
    const inputClass = computed(() => {
      return twMerge(twJoin(
        ui.value.base,
        ui.value.form,
        ui.value.rounded,
        ui.value.background,
        ui.value.border,
        color.value && ui.value.ring.replaceAll("{color}", color.value),
        color.value && ui.value.color.replaceAll("{color}", color.value)
      ), props.inputClass);
    });
    return {
      // eslint-disable-next-line vue/no-dupe-keys
      ui,
      attrs,
      toggle,
      inputId,
      // eslint-disable-next-line vue/no-dupe-keys
      name,
      // eslint-disable-next-line vue/no-dupe-keys
      inputClass,
      onChange
    };
  }
});
function _sfc_ssrRender(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  let _temp0;
  _push(`<div${ssrRenderAttrs(mergeProps({
    class: _ctx.ui.wrapper,
    "data-n-ids": _ctx.attrs["data-n-ids"]
  }, _attrs))}><div class="${ssrRenderClass(_ctx.ui.container)}"><input${ssrRenderAttrs((_temp0 = mergeProps({
    id: _ctx.inputId,
    checked: Array.isArray(_ctx.toggle) ? ssrLooseContain(_ctx.toggle, _ctx.value) : _ctx.toggle,
    name: _ctx.name,
    required: _ctx.required,
    value: _ctx.value,
    disabled: _ctx.disabled,
    indeterminate: _ctx.indeterminate,
    type: "checkbox",
    class: _ctx.inputClass
  }, _ctx.attrs), mergeProps(_temp0, ssrGetDynamicModelProps(_temp0, _ctx.toggle))))}></div>`);
  if (_ctx.label || _ctx.$slots.label) {
    _push(`<div class="${ssrRenderClass(_ctx.ui.inner)}"><label${ssrRenderAttr("for", _ctx.inputId)} class="${ssrRenderClass(_ctx.ui.label)}">`);
    ssrRenderSlot(_ctx.$slots, "label", { label: _ctx.label }, () => {
      _push(`${ssrInterpolate(_ctx.label)}`);
    }, _push, _parent);
    if (_ctx.required) {
      _push(`<span class="${ssrRenderClass(_ctx.ui.required)}">*</span>`);
    } else {
      _push(`<!---->`);
    }
    _push(`</label>`);
    if (_ctx.help || _ctx.$slots.help) {
      _push(`<p class="${ssrRenderClass(_ctx.ui.help)}">`);
      ssrRenderSlot(_ctx.$slots, "help", { help: _ctx.help }, () => {
        _push(`${ssrInterpolate(_ctx.help)}`);
      }, _push, _parent);
      _push(`</p>`);
    } else {
      _push(`<!---->`);
    }
    _push(`</div>`);
  } else {
    _push(`<!---->`);
  }
  _push(`</div>`);
}
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/forms/Checkbox.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const __nuxt_component_4 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["ssrRender", _sfc_ssrRender]]);
const _sfc_main = {
  __name: "meeting",
  __ssrInlineRender: true,
  setup(__props) {
    const selectAll = ref(false);
    const selectedMeetings = ref([]);
    const pageSize = ref(10);
    const meetings = ref([
      {
        id: 1,
        name: "114年度第一屆第1次會員大會",
        renewalGroup: "臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會",
        date: "2025年3月15日下午2:00:00",
        attendees: 73,
        absent: 72,
        observers: 0,
        recordStatus: 3
      },
      {
        id: 2,
        name: "114年度第一屆第2次會員大會",
        renewalGroup: "臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會",
        date: "2025年8月9日下午2:00:00",
        attendees: 74,
        absent: 74,
        observers: 0,
        recordStatus: 3
      }
    ]);
    const toggleSelectAll = () => {
      if (selectAll.value) {
        selectedMeetings.value = meetings.value.map((m) => m.id);
      } else {
        selectedMeetings.value = [];
      }
    };
    const addMeeting = () => {
      console.log("Adding new meeting");
    };
    const deleteMeetings = () => {
      console.log("Deleting meetings:", selectedMeetings.value);
    };
    const viewRecord = (meeting) => {
      console.log("Viewing record for:", meeting);
    };
    const viewMeeting = (meeting) => {
      console.log("Viewing meeting for:", meeting);
    };
    const viewResults = (meeting) => {
      console.log("Viewing results for:", meeting);
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_Icon = __nuxt_component_1;
      const _component_UButton = __nuxt_component_0$1;
      const _component_UCard = __nuxt_component_1$1;
      const _component_UCheckbox = __nuxt_component_4;
      const _component_USelectMenu = __nuxt_component_5;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({ name: "main" }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`會議管理`);
          } else {
            return [
              createTextVNode("會議管理")
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
            _push2(`</div><h2 class="text-2xl font-semibold"${_scopeId}>會議</h2></div></div><div class="flex justify-end gap-4 mb-6"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "red",
              onClick: deleteMeetings
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_Icon, {
                    name: "heroicons:trash",
                    class: "w-5 h-5 mr-2"
                  }, null, _parent3, _scopeId2));
                  _push3(` 刪除 `);
                } else {
                  return [
                    createVNode(_component_Icon, {
                      name: "heroicons:trash",
                      class: "w-5 h-5 mr-2"
                    }),
                    createTextVNode(" 刪除 ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "green",
              onClick: addMeeting
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_Icon, {
                    name: "heroicons:plus",
                    class: "w-5 h-5 mr-2"
                  }, null, _parent3, _scopeId2));
                  _push3(` 新增會議 `);
                } else {
                  return [
                    createVNode(_component_Icon, {
                      name: "heroicons:plus",
                      class: "w-5 h-5 mr-2"
                    }),
                    createTextVNode(" 新增會議 ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            _push2(ssrRenderComponent(_component_UCard, null, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="overflow-x-auto"${_scopeId2}><table class="w-full"${_scopeId2}><thead${_scopeId2}><tr class="border-b"${_scopeId2}><th class="p-3 text-left"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UCheckbox, {
                    modelValue: selectAll.value,
                    "onUpdate:modelValue": ($event) => selectAll.value = $event,
                    onChange: toggleSelectAll
                  }, null, _parent3, _scopeId2));
                  _push3(`</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>會議名稱</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>所屬更新會</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>會議日期時間</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>出席人數</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>缺入對象人數</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>列席人數</th><th class="p-3 text-left text-sm font-medium text-gray-700"${_scopeId2}>記錄狀態</th><th class="p-3 text-center text-sm font-medium text-gray-700"${_scopeId2}>操作</th></tr></thead><tbody${_scopeId2}><!--[-->`);
                  ssrRenderList(meetings.value, (meeting, index) => {
                    _push3(`<tr class="border-b hover:bg-gray-50"${_scopeId2}><td class="p-3"${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_UCheckbox, {
                      modelValue: selectedMeetings.value,
                      "onUpdate:modelValue": ($event) => selectedMeetings.value = $event,
                      value: meeting.id
                    }, null, _parent3, _scopeId2));
                    _push3(`</td><td class="p-3 text-sm"${_scopeId2}>${ssrInterpolate(meeting.name)}</td><td class="p-3 text-sm"${_scopeId2}>${ssrInterpolate(meeting.renewalGroup)}</td><td class="p-3 text-sm"${_scopeId2}>${ssrInterpolate(meeting.date)}</td><td class="p-3 text-sm text-center"${_scopeId2}>${ssrInterpolate(meeting.attendees)}</td><td class="p-3 text-sm text-center"${_scopeId2}>${ssrInterpolate(meeting.absent)}</td><td class="p-3 text-sm text-center"${_scopeId2}>${ssrInterpolate(meeting.observers)}</td><td class="p-3 text-sm text-center"${_scopeId2}>${ssrInterpolate(meeting.recordStatus)}</td><td class="p-3 text-center space-x-2"${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_UButton, {
                      color: "green",
                      size: "xs",
                      onClick: ($event) => viewRecord(meeting)
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` 基本資料 `);
                        } else {
                          return [
                            createTextVNode(" 基本資料 ")
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                    _push3(ssrRenderComponent(_component_UButton, {
                      color: "blue",
                      size: "xs",
                      onClick: ($event) => viewMeeting(meeting)
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` 投票會議 `);
                        } else {
                          return [
                            createTextVNode(" 投票會議 ")
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                    _push3(ssrRenderComponent(_component_UButton, {
                      color: "purple",
                      size: "xs",
                      onClick: ($event) => viewResults(meeting)
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` 會議結果 `);
                        } else {
                          return [
                            createTextVNode(" 會議結果 ")
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                    _push3(`</td></tr>`);
                  });
                  _push3(`<!--]--></tbody></table></div><div class="flex justify-between items-center mt-4 pt-4 border-t"${_scopeId2}><div class="text-sm text-gray-500"${_scopeId2}> 每頁顯示： `);
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: pageSize.value,
                    "onUpdate:modelValue": ($event) => pageSize.value = $event,
                    options: [10, 20, 50],
                    size: "sm",
                    class: "inline-block w-20 ml-2"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div class="text-sm text-gray-500"${_scopeId2}> 1-2 共 2 </div><div class="flex gap-2"${_scopeId2}>`);
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
                            createVNode("th", { class: "p-3 text-left" }, [
                              createVNode(_component_UCheckbox, {
                                modelValue: selectAll.value,
                                "onUpdate:modelValue": ($event) => selectAll.value = $event,
                                onChange: toggleSelectAll
                              }, null, 8, ["modelValue", "onUpdate:modelValue"])
                            ]),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "會議名稱"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "所屬更新會"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "會議日期時間"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "出席人數"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "缺入對象人數"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "列席人數"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "記錄狀態"),
                            createVNode("th", { class: "p-3 text-center text-sm font-medium text-gray-700" }, "操作")
                          ])
                        ]),
                        createVNode("tbody", null, [
                          (openBlock(true), createBlock(Fragment, null, renderList(meetings.value, (meeting, index) => {
                            return openBlock(), createBlock("tr", {
                              key: index,
                              class: "border-b hover:bg-gray-50"
                            }, [
                              createVNode("td", { class: "p-3" }, [
                                createVNode(_component_UCheckbox, {
                                  modelValue: selectedMeetings.value,
                                  "onUpdate:modelValue": ($event) => selectedMeetings.value = $event,
                                  value: meeting.id
                                }, null, 8, ["modelValue", "onUpdate:modelValue", "value"])
                              ]),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(meeting.name), 1),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(meeting.renewalGroup), 1),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(meeting.date), 1),
                              createVNode("td", { class: "p-3 text-sm text-center" }, toDisplayString(meeting.attendees), 1),
                              createVNode("td", { class: "p-3 text-sm text-center" }, toDisplayString(meeting.absent), 1),
                              createVNode("td", { class: "p-3 text-sm text-center" }, toDisplayString(meeting.observers), 1),
                              createVNode("td", { class: "p-3 text-sm text-center" }, toDisplayString(meeting.recordStatus), 1),
                              createVNode("td", { class: "p-3 text-center space-x-2" }, [
                                createVNode(_component_UButton, {
                                  color: "green",
                                  size: "xs",
                                  onClick: ($event) => viewRecord(meeting)
                                }, {
                                  default: withCtx(() => [
                                    createTextVNode(" 基本資料 ")
                                  ]),
                                  _: 2
                                }, 1032, ["onClick"]),
                                createVNode(_component_UButton, {
                                  color: "blue",
                                  size: "xs",
                                  onClick: ($event) => viewMeeting(meeting)
                                }, {
                                  default: withCtx(() => [
                                    createTextVNode(" 投票會議 ")
                                  ]),
                                  _: 2
                                }, 1032, ["onClick"]),
                                createVNode(_component_UButton, {
                                  color: "purple",
                                  size: "xs",
                                  onClick: ($event) => viewResults(meeting)
                                }, {
                                  default: withCtx(() => [
                                    createTextVNode(" 會議結果 ")
                                  ]),
                                  _: 2
                                }, 1032, ["onClick"])
                              ])
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
                      createVNode("div", { class: "text-sm text-gray-500" }, " 1-2 共 2 "),
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
                    createVNode("h2", { class: "text-2xl font-semibold" }, "會議")
                  ])
                ]),
                createVNode("div", { class: "flex justify-end gap-4 mb-6" }, [
                  createVNode(_component_UButton, {
                    color: "red",
                    onClick: deleteMeetings
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_Icon, {
                        name: "heroicons:trash",
                        class: "w-5 h-5 mr-2"
                      }),
                      createTextVNode(" 刪除 ")
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UButton, {
                    color: "green",
                    onClick: addMeeting
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_Icon, {
                        name: "heroicons:plus",
                        class: "w-5 h-5 mr-2"
                      }),
                      createTextVNode(" 新增會議 ")
                    ]),
                    _: 1
                  })
                ]),
                createVNode(_component_UCard, null, {
                  default: withCtx(() => [
                    createVNode("div", { class: "overflow-x-auto" }, [
                      createVNode("table", { class: "w-full" }, [
                        createVNode("thead", null, [
                          createVNode("tr", { class: "border-b" }, [
                            createVNode("th", { class: "p-3 text-left" }, [
                              createVNode(_component_UCheckbox, {
                                modelValue: selectAll.value,
                                "onUpdate:modelValue": ($event) => selectAll.value = $event,
                                onChange: toggleSelectAll
                              }, null, 8, ["modelValue", "onUpdate:modelValue"])
                            ]),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "會議名稱"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "所屬更新會"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "會議日期時間"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "出席人數"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "缺入對象人數"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "列席人數"),
                            createVNode("th", { class: "p-3 text-left text-sm font-medium text-gray-700" }, "記錄狀態"),
                            createVNode("th", { class: "p-3 text-center text-sm font-medium text-gray-700" }, "操作")
                          ])
                        ]),
                        createVNode("tbody", null, [
                          (openBlock(true), createBlock(Fragment, null, renderList(meetings.value, (meeting, index) => {
                            return openBlock(), createBlock("tr", {
                              key: index,
                              class: "border-b hover:bg-gray-50"
                            }, [
                              createVNode("td", { class: "p-3" }, [
                                createVNode(_component_UCheckbox, {
                                  modelValue: selectedMeetings.value,
                                  "onUpdate:modelValue": ($event) => selectedMeetings.value = $event,
                                  value: meeting.id
                                }, null, 8, ["modelValue", "onUpdate:modelValue", "value"])
                              ]),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(meeting.name), 1),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(meeting.renewalGroup), 1),
                              createVNode("td", { class: "p-3 text-sm" }, toDisplayString(meeting.date), 1),
                              createVNode("td", { class: "p-3 text-sm text-center" }, toDisplayString(meeting.attendees), 1),
                              createVNode("td", { class: "p-3 text-sm text-center" }, toDisplayString(meeting.absent), 1),
                              createVNode("td", { class: "p-3 text-sm text-center" }, toDisplayString(meeting.observers), 1),
                              createVNode("td", { class: "p-3 text-sm text-center" }, toDisplayString(meeting.recordStatus), 1),
                              createVNode("td", { class: "p-3 text-center space-x-2" }, [
                                createVNode(_component_UButton, {
                                  color: "green",
                                  size: "xs",
                                  onClick: ($event) => viewRecord(meeting)
                                }, {
                                  default: withCtx(() => [
                                    createTextVNode(" 基本資料 ")
                                  ]),
                                  _: 2
                                }, 1032, ["onClick"]),
                                createVNode(_component_UButton, {
                                  color: "blue",
                                  size: "xs",
                                  onClick: ($event) => viewMeeting(meeting)
                                }, {
                                  default: withCtx(() => [
                                    createTextVNode(" 投票會議 ")
                                  ]),
                                  _: 2
                                }, 1032, ["onClick"]),
                                createVNode(_component_UButton, {
                                  color: "purple",
                                  size: "xs",
                                  onClick: ($event) => viewResults(meeting)
                                }, {
                                  default: withCtx(() => [
                                    createTextVNode(" 會議結果 ")
                                  ]),
                                  _: 2
                                }, 1032, ["onClick"])
                              ])
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
                      createVNode("div", { class: "text-sm text-gray-500" }, " 1-2 共 2 "),
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/tables/meeting.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=meeting-C8zoVV24.js.map
