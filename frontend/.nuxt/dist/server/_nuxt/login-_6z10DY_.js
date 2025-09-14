import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { _ as __nuxt_component_1 } from "./Card-DBUe4X9m.js";
import { a as _export_sfc, b as __nuxt_component_1$1, c as __nuxt_component_0$1, n as navigateTo } from "../server.mjs";
import { _ as __nuxt_component_2 } from "./Input-CLveazjF.js";
import { ref, mergeProps, withCtx, unref, isRef, createVNode, createTextVNode, withModifiers, useSSRContext } from "vue";
import { ssrRenderComponent } from "vue/server-renderer";
import "vue-router";
import "tailwind-merge";
import "ofetch";
import "#internal/nuxt/paths";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/hookable/dist/index.mjs";
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
import "./useFormGroup-B3564yef.js";
const _sfc_main = {
  __name: "login",
  __ssrInlineRender: true,
  setup(__props) {
    const username = ref("");
    const password = ref("");
    const showPassword = ref(false);
    const loading = ref(false);
    const handleLogin = async () => {
      if (!username.value || !password.value) {
        return;
      }
      loading.value = true;
      try {
        await new Promise((resolve) => setTimeout(resolve, 1e3));
        await navigateTo("/tables/urban-renewal");
      } catch (error) {
        console.error("Login error:", error);
      } finally {
        loading.value = false;
      }
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_UCard = __nuxt_component_1;
      const _component_Icon = __nuxt_component_1$1;
      const _component_UInput = __nuxt_component_2;
      const _component_UButton = __nuxt_component_0$1;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({ name: "auth" }, _attrs), {
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
            _push2(`<div class="flex items-center justify-center py-12" data-v-83f78889${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UCard, { class: "login-card w-full max-w-md" }, {
              header: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="text-center" data-v-83f78889${_scopeId2}><h2 class="text-2xl font-bold text-gray-800" data-v-83f78889${_scopeId2}>登入</h2></div>`);
                } else {
                  return [
                    createVNode("div", { class: "text-center" }, [
                      createVNode("h2", { class: "text-2xl font-bold text-gray-800" }, "登入")
                    ])
                  ];
                }
              }),
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<form class="space-y-6" data-v-83f78889${_scopeId2}><div class="input-group" data-v-83f78889${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_Icon, {
                    name: "heroicons:user",
                    class: "input-icon"
                  }, null, _parent3, _scopeId2));
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(username),
                    "onUpdate:modelValue": ($event) => isRef(username) ? username.value = $event : null,
                    placeholder: "帳號",
                    variant: "none",
                    class: "custom-input",
                    ui: { base: "w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500" }
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div class="input-group" data-v-83f78889${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_Icon, {
                    name: "heroicons:lock-closed",
                    class: "input-icon"
                  }, null, _parent3, _scopeId2));
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(password),
                    "onUpdate:modelValue": ($event) => isRef(password) ? password.value = $event : null,
                    placeholder: "密碼",
                    type: unref(showPassword) ? "text" : "password",
                    variant: "none",
                    class: "custom-input",
                    ui: { base: "w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500" }
                  }, {
                    trailing: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(ssrRenderComponent(_component_UButton, {
                          variant: "ghost",
                          size: "xs",
                          onClick: ($event) => showPassword.value = !unref(showPassword),
                          class: "password-toggle"
                        }, {
                          default: withCtx((_4, _push5, _parent5, _scopeId4) => {
                            if (_push5) {
                              _push5(ssrRenderComponent(_component_Icon, {
                                name: unref(showPassword) ? "heroicons:eye-slash" : "heroicons:eye",
                                class: "w-4 h-4 text-gray-500"
                              }, null, _parent5, _scopeId4));
                            } else {
                              return [
                                createVNode(_component_Icon, {
                                  name: unref(showPassword) ? "heroicons:eye-slash" : "heroicons:eye",
                                  class: "w-4 h-4 text-gray-500"
                                }, null, 8, ["name"])
                              ];
                            }
                          }),
                          _: 1
                        }, _parent4, _scopeId3));
                      } else {
                        return [
                          createVNode(_component_UButton, {
                            variant: "ghost",
                            size: "xs",
                            onClick: ($event) => showPassword.value = !unref(showPassword),
                            class: "password-toggle"
                          }, {
                            default: withCtx(() => [
                              createVNode(_component_Icon, {
                                name: unref(showPassword) ? "heroicons:eye-slash" : "heroicons:eye",
                                class: "w-4 h-4 text-gray-500"
                              }, null, 8, ["name"])
                            ]),
                            _: 1
                          }, 8, ["onClick"])
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</div>`);
                  _push3(ssrRenderComponent(_component_UButton, {
                    type: "submit",
                    block: "",
                    size: "lg",
                    class: "login-btn mt-8",
                    loading: unref(loading)
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(` 登入 `);
                      } else {
                        return [
                          createTextVNode(" 登入 ")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</form>`);
                } else {
                  return [
                    createVNode("form", {
                      onSubmit: withModifiers(handleLogin, ["prevent"]),
                      class: "space-y-6"
                    }, [
                      createVNode("div", { class: "input-group" }, [
                        createVNode(_component_Icon, {
                          name: "heroicons:user",
                          class: "input-icon"
                        }),
                        createVNode(_component_UInput, {
                          modelValue: unref(username),
                          "onUpdate:modelValue": ($event) => isRef(username) ? username.value = $event : null,
                          placeholder: "帳號",
                          variant: "none",
                          class: "custom-input",
                          ui: { base: "w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500" }
                        }, null, 8, ["modelValue", "onUpdate:modelValue"])
                      ]),
                      createVNode("div", { class: "input-group" }, [
                        createVNode(_component_Icon, {
                          name: "heroicons:lock-closed",
                          class: "input-icon"
                        }),
                        createVNode(_component_UInput, {
                          modelValue: unref(password),
                          "onUpdate:modelValue": ($event) => isRef(password) ? password.value = $event : null,
                          placeholder: "密碼",
                          type: unref(showPassword) ? "text" : "password",
                          variant: "none",
                          class: "custom-input",
                          ui: { base: "w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500" }
                        }, {
                          trailing: withCtx(() => [
                            createVNode(_component_UButton, {
                              variant: "ghost",
                              size: "xs",
                              onClick: ($event) => showPassword.value = !unref(showPassword),
                              class: "password-toggle"
                            }, {
                              default: withCtx(() => [
                                createVNode(_component_Icon, {
                                  name: unref(showPassword) ? "heroicons:eye-slash" : "heroicons:eye",
                                  class: "w-4 h-4 text-gray-500"
                                }, null, 8, ["name"])
                              ]),
                              _: 1
                            }, 8, ["onClick"])
                          ]),
                          _: 1
                        }, 8, ["modelValue", "onUpdate:modelValue", "type"])
                      ]),
                      createVNode(_component_UButton, {
                        type: "submit",
                        block: "",
                        size: "lg",
                        class: "login-btn mt-8",
                        loading: unref(loading)
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" 登入 ")
                        ]),
                        _: 1
                      }, 8, ["loading"])
                    ], 32)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-center py-12" }, [
                createVNode(_component_UCard, { class: "login-card w-full max-w-md" }, {
                  header: withCtx(() => [
                    createVNode("div", { class: "text-center" }, [
                      createVNode("h2", { class: "text-2xl font-bold text-gray-800" }, "登入")
                    ])
                  ]),
                  default: withCtx(() => [
                    createVNode("form", {
                      onSubmit: withModifiers(handleLogin, ["prevent"]),
                      class: "space-y-6"
                    }, [
                      createVNode("div", { class: "input-group" }, [
                        createVNode(_component_Icon, {
                          name: "heroicons:user",
                          class: "input-icon"
                        }),
                        createVNode(_component_UInput, {
                          modelValue: unref(username),
                          "onUpdate:modelValue": ($event) => isRef(username) ? username.value = $event : null,
                          placeholder: "帳號",
                          variant: "none",
                          class: "custom-input",
                          ui: { base: "w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500" }
                        }, null, 8, ["modelValue", "onUpdate:modelValue"])
                      ]),
                      createVNode("div", { class: "input-group" }, [
                        createVNode(_component_Icon, {
                          name: "heroicons:lock-closed",
                          class: "input-icon"
                        }),
                        createVNode(_component_UInput, {
                          modelValue: unref(password),
                          "onUpdate:modelValue": ($event) => isRef(password) ? password.value = $event : null,
                          placeholder: "密碼",
                          type: unref(showPassword) ? "text" : "password",
                          variant: "none",
                          class: "custom-input",
                          ui: { base: "w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500" }
                        }, {
                          trailing: withCtx(() => [
                            createVNode(_component_UButton, {
                              variant: "ghost",
                              size: "xs",
                              onClick: ($event) => showPassword.value = !unref(showPassword),
                              class: "password-toggle"
                            }, {
                              default: withCtx(() => [
                                createVNode(_component_Icon, {
                                  name: unref(showPassword) ? "heroicons:eye-slash" : "heroicons:eye",
                                  class: "w-4 h-4 text-gray-500"
                                }, null, 8, ["name"])
                              ]),
                              _: 1
                            }, 8, ["onClick"])
                          ]),
                          _: 1
                        }, 8, ["modelValue", "onUpdate:modelValue", "type"])
                      ]),
                      createVNode(_component_UButton, {
                        type: "submit",
                        block: "",
                        size: "lg",
                        class: "login-btn mt-8",
                        loading: unref(loading)
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" 登入 ")
                        ]),
                        _: 1
                      }, 8, ["loading"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/login.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const login = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-83f78889"]]);
export {
  login as default
};
//# sourceMappingURL=login-_6z10DY_.js.map
