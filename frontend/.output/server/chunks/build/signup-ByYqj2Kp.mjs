import { _ as __nuxt_component_0$2, a as __nuxt_component_1$1, c as __nuxt_component_0, b as __nuxt_component_4 } from './Button-CUPZ9vdw.mjs';
import __nuxt_component_1 from './index-BRDSpBS1.mjs';
import { ref, mergeProps, withCtx, unref, createTextVNode, createVNode, createBlock, createCommentVNode, openBlock, useSSRContext } from 'vue';
import { ssrRenderComponent, ssrRenderClass, ssrIncludeBooleanAttr } from 'vue/server-renderer';
import { _ as _export_sfc } from './server.mjs';
import 'vue-router';
import 'tailwind-merge';
import '../_/nitro.mjs';
import 'node:http';
import 'node:https';
import 'node:events';
import 'node:buffer';
import 'node:fs';
import 'node:url';
import '@iconify/utils';
import 'node:crypto';
import 'consola';
import 'node:path';
import '@vueuse/core';
import '@iconify/vue';
import '@iconify/utils/lib/css/icon';
import '@heroicons/vue/24/outline';
import '../routes/renderer.mjs';
import 'vue-bundle-renderer/runtime';
import 'unhead/server';
import 'devalue';
import 'unhead/utils';
import 'unhead/plugins';

const _sfc_main = {
  __name: "signup",
  __ssrInlineRender: true,
  setup(__props) {
    const currentStep = ref(1);
    const selectedAccountType = ref("");
    const loading = ref(false);
    const formData = ref({
      account: "",
      nickname: "",
      password: "",
      confirmPassword: "",
      fullName: "",
      email: "",
      phone: "",
      lineId: "",
      companyName: "",
      jobTitle: "",
      businessName: "",
      taxId: "",
      businessPhone: ""
    });
    const selectAccountType = (type) => {
      selectedAccountType.value = type;
    };
    const handleNext = () => {
      if (!selectedAccountType.value) {
        return;
      }
      currentStep.value = 2;
    };
    const goBack = () => {
      currentStep.value = 1;
    };
    const handleRegister = async () => {
      if (!validateForm()) {
        return;
      }
      loading.value = true;
      try {
        await new Promise((resolve) => setTimeout(resolve, 2e3));
        currentStep.value = 3;
      } catch (error) {
        console.error("Registration error:", error);
        alert("\u8A3B\u518A\u5931\u6557\uFF0C\u8ACB\u7A0D\u5F8C\u518D\u8A66");
      } finally {
        loading.value = false;
      }
    };
    const validateForm = () => {
      const requiredFields = ["account", "nickname", "password", "confirmPassword", "fullName", "email", "phone"];
      for (const field of requiredFields) {
        if (!formData.value[field]) {
          alert("\u8ACB\u586B\u5BEB\u6240\u6709\u5FC5\u586B\u6B04\u4F4D");
          return false;
        }
      }
      if (formData.value.password !== formData.value.confirmPassword) {
        alert("\u5BC6\u78BC\u8207\u78BA\u8A8D\u5BC6\u78BC\u4E0D\u7B26");
        return false;
      }
      if (selectedAccountType.value === "business") {
        if (!formData.value.businessName || !formData.value.taxId) {
          alert("\u8ACB\u586B\u5BEB\u4F01\u696D\u76F8\u95DC\u8CC7\u6599");
          return false;
        }
      }
      return true;
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0$2;
      const _component_UCard = __nuxt_component_1$1;
      const _component_Icon = __nuxt_component_1;
      const _component_UButton = __nuxt_component_0;
      const _component_UInput = __nuxt_component_4;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({
        name: "auth",
        "main-class": `flex items-start justify-center pt-16 pb-8`,
        "logo-style": `width: auto;`
      }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`\u8A3B\u518A`);
          } else {
            return [
              createTextVNode("\u8A3B\u518A")
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-center pt-16 pb-8 w-full" data-v-c59a5275${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UCard, {
              class: ["signup-card w-full max-w-lg", { "mb-8": unref(selectedAccountType) === "business" && unref(currentStep) === 2 }]
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="progress-container mb-8" data-v-c59a5275${_scopeId2}><div class="flex justify-between items-center" data-v-c59a5275${_scopeId2}><div class="${ssrRenderClass([{ active: unref(currentStep) === 1, completed: unref(currentStep) > 1 }, "step-item"])}" data-v-c59a5275${_scopeId2}><div class="step-circle" data-v-c59a5275${_scopeId2}>`);
                  if (unref(currentStep) > 1) {
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:check",
                      class: "w-5 h-5"
                    }, null, _parent3, _scopeId2));
                  } else {
                    _push3(`<span data-v-c59a5275${_scopeId2}>1</span>`);
                  }
                  _push3(`</div><div class="step-text" data-v-c59a5275${_scopeId2}>\u9078\u64C7\u5E33\u865F\u985E\u578B</div></div><div class="${ssrRenderClass([{ completed: unref(currentStep) > 1 }, "step-line"])}" data-v-c59a5275${_scopeId2}></div><div class="${ssrRenderClass([{ active: unref(currentStep) === 2, completed: unref(currentStep) > 2 }, "step-item"])}" data-v-c59a5275${_scopeId2}><div class="step-circle" data-v-c59a5275${_scopeId2}>`);
                  if (unref(currentStep) > 2) {
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:check",
                      class: "w-5 h-5"
                    }, null, _parent3, _scopeId2));
                  } else {
                    _push3(`<span data-v-c59a5275${_scopeId2}>2</span>`);
                  }
                  _push3(`</div><div class="step-text" data-v-c59a5275${_scopeId2}>\u586B\u5165\u8CC7\u6599</div></div><div class="${ssrRenderClass([{ completed: unref(currentStep) > 2 }, "step-line"])}" data-v-c59a5275${_scopeId2}></div><div class="${ssrRenderClass([{ active: unref(currentStep) === 3, completed: unref(currentStep) > 3 }, "step-item"])}" data-v-c59a5275${_scopeId2}><div class="step-circle" data-v-c59a5275${_scopeId2}>`);
                  if (unref(currentStep) > 3) {
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:check",
                      class: "w-5 h-5"
                    }, null, _parent3, _scopeId2));
                  } else {
                    _push3(`<span data-v-c59a5275${_scopeId2}>3</span>`);
                  }
                  _push3(`</div><div class="step-text" data-v-c59a5275${_scopeId2}>\u5B8C\u6210</div></div></div></div>`);
                  if (unref(currentStep) === 1) {
                    _push3(`<div class="account-selection" data-v-c59a5275${_scopeId2}><div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8" data-v-c59a5275${_scopeId2}><div class="${ssrRenderClass([{ "selected": unref(selectedAccountType) === "personal" }, "account-option"])}" data-v-c59a5275${_scopeId2}><button class="${ssrRenderClass([{ "active": unref(selectedAccountType) === "personal" }, "account-btn personal-btn w-full"])}" data-v-c59a5275${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:user",
                      class: "w-8 h-8 mb-2"
                    }, null, _parent3, _scopeId2));
                    _push3(`<div class="text-lg font-semibold" data-v-c59a5275${_scopeId2}>\u500B\u4EBA\u5E33\u865F</div></button><div class="radio-container" data-v-c59a5275${_scopeId2}><label class="radio-label" data-v-c59a5275${_scopeId2}><input type="radio" name="accountType" value="personal"${ssrIncludeBooleanAttr(unref(selectedAccountType) === "personal") ? " checked" : ""} class="radio-btn" data-v-c59a5275${_scopeId2}></label></div></div><div class="${ssrRenderClass([{ "selected": unref(selectedAccountType) === "business" }, "account-option"])}" data-v-c59a5275${_scopeId2}><button class="${ssrRenderClass([{ "active": unref(selectedAccountType) === "business" }, "account-btn business-btn w-full"])}" data-v-c59a5275${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:building-office",
                      class: "w-8 h-8 mb-2"
                    }, null, _parent3, _scopeId2));
                    _push3(`<div class="text-lg font-semibold" data-v-c59a5275${_scopeId2}>\u4F01\u696D\u5E33\u865F</div></button><div class="radio-container" data-v-c59a5275${_scopeId2}><label class="radio-label" data-v-c59a5275${_scopeId2}><input type="radio" name="accountType" value="business"${ssrIncludeBooleanAttr(unref(selectedAccountType) === "business") ? " checked" : ""} class="radio-btn" data-v-c59a5275${_scopeId2}></label></div></div></div>`);
                    _push3(ssrRenderComponent(_component_UButton, {
                      onClick: handleNext,
                      block: "",
                      size: "lg",
                      class: "next-btn",
                      disabled: !unref(selectedAccountType)
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` \u4E0B\u4E00\u6B65 `);
                        } else {
                          return [
                            createTextVNode(" \u4E0B\u4E00\u6B65 ")
                          ];
                        }
                      }),
                      _: 1
                    }, _parent3, _scopeId2));
                    _push3(`</div>`);
                  } else {
                    _push3(`<!---->`);
                  }
                  if (unref(currentStep) === 2) {
                    _push3(`<div class="form-section" data-v-c59a5275${_scopeId2}>`);
                    if (unref(selectedAccountType) === "personal") {
                      _push3(`<div class="form-grid" data-v-c59a5275${_scopeId2}><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" data-v-c59a5275${_scopeId2}><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).account,
                        "onUpdate:modelValue": ($event) => unref(formData).account = $event,
                        placeholder: "\u5E33\u865F"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).nickname,
                        "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                        placeholder: "\u66B1\u7A31"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).password,
                        "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                        placeholder: "\u5BC6\u78BC",
                        type: "password"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).confirmPassword,
                        "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                        placeholder: "\u78BA\u8A8D\u5BC6\u78BC",
                        type: "password"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).fullName,
                        "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                        placeholder: "\u59D3\u540D"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).email,
                        "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                        placeholder: "\u4FE1\u7BB1",
                        type: "email"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).phone,
                        "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                        placeholder: "\u624B\u6A5F\u865F\u78BC"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).lineId,
                        "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                        placeholder: "Line\u5E33\u865F"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).companyName,
                        "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                        placeholder: "\u516C\u53F8\u540D\u7A31"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).jobTitle,
                        "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                        placeholder: "\u8077\u7A31"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div></div></div>`);
                    } else {
                      _push3(`<!---->`);
                    }
                    if (unref(selectedAccountType) === "business") {
                      _push3(`<div class="form-grid" data-v-c59a5275${_scopeId2}><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" data-v-c59a5275${_scopeId2}><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).account,
                        "onUpdate:modelValue": ($event) => unref(formData).account = $event,
                        placeholder: "\u5E33\u865F"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).nickname,
                        "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                        placeholder: "\u66B1\u7A31"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).password,
                        "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                        placeholder: "\u5BC6\u78BC",
                        type: "password"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).confirmPassword,
                        "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                        placeholder: "\u78BA\u8A8D\u5BC6\u78BC",
                        type: "password"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).fullName,
                        "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                        placeholder: "\u59D3\u540D"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).email,
                        "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                        placeholder: "\u4FE1\u7BB1",
                        type: "email"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).phone,
                        "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                        placeholder: "\u624B\u6A5F\u865F\u78BC"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).lineId,
                        "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                        placeholder: "Line\u5E33\u865F"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).companyName,
                        "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                        placeholder: "\u516C\u53F8\u540D\u7A31"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).jobTitle,
                        "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                        placeholder: "\u8077\u7A31"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).businessName,
                        "onUpdate:modelValue": ($event) => unref(formData).businessName = $event,
                        placeholder: "\u4F01\u696D\u540D\u7A31"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).taxId,
                        "onUpdate:modelValue": ($event) => unref(formData).taxId = $event,
                        placeholder: "\u7D71\u4E00\u7DE8\u865F"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-c59a5275${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).businessPhone,
                        "onUpdate:modelValue": ($event) => unref(formData).businessPhone = $event,
                        placeholder: "\u4F01\u696D\u96FB\u8A71"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div></div></div>`);
                    } else {
                      _push3(`<!---->`);
                    }
                    _push3(`<div class="flex gap-4 mt-4" data-v-c59a5275${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_UButton, {
                      onClick: handleRegister,
                      size: "lg",
                      class: "register-btn flex-1",
                      loading: unref(loading)
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` \u8A3B\u518A `);
                        } else {
                          return [
                            createTextVNode(" \u8A3B\u518A ")
                          ];
                        }
                      }),
                      _: 1
                    }, _parent3, _scopeId2));
                    _push3(ssrRenderComponent(_component_UButton, {
                      onClick: goBack,
                      variant: "outline",
                      size: "lg",
                      class: "back-btn flex-1"
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` \u56DE\u4E0A\u4E00\u9801 `);
                        } else {
                          return [
                            createTextVNode(" \u56DE\u4E0A\u4E00\u9801 ")
                          ];
                        }
                      }),
                      _: 1
                    }, _parent3, _scopeId2));
                    _push3(`</div></div>`);
                  } else {
                    _push3(`<!---->`);
                  }
                  if (unref(currentStep) === 3) {
                    _push3(`<div class="completion-section text-center" data-v-c59a5275${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:check-circle",
                      class: "w-16 h-16 text-green-500 mx-auto mb-4"
                    }, null, _parent3, _scopeId2));
                    _push3(`<h3 class="text-2xl font-bold mb-4 text-gray-800" data-v-c59a5275${_scopeId2}>\u8A3B\u518A\u5B8C\u6210\uFF01</h3><p class="text-gray-600 mb-6" data-v-c59a5275${_scopeId2}>\u60A8\u7684\u5E33\u865F\u5DF2\u6210\u529F\u5EFA\u7ACB</p>`);
                    _push3(ssrRenderComponent(_component_UButton, {
                      onClick: ($event) => _ctx.$router.push("/login"),
                      size: "lg",
                      class: "login-btn"
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` \u524D\u5F80\u767B\u5165 `);
                        } else {
                          return [
                            createTextVNode(" \u524D\u5F80\u767B\u5165 ")
                          ];
                        }
                      }),
                      _: 1
                    }, _parent3, _scopeId2));
                    _push3(`</div>`);
                  } else {
                    _push3(`<!---->`);
                  }
                } else {
                  return [
                    createVNode("div", { class: "progress-container mb-8" }, [
                      createVNode("div", { class: "flex justify-between items-center" }, [
                        createVNode("div", {
                          class: ["step-item", { active: unref(currentStep) === 1, completed: unref(currentStep) > 1 }]
                        }, [
                          createVNode("div", { class: "step-circle" }, [
                            unref(currentStep) > 1 ? (openBlock(), createBlock(_component_Icon, {
                              key: 0,
                              name: "heroicons:check",
                              class: "w-5 h-5"
                            })) : (openBlock(), createBlock("span", { key: 1 }, "1"))
                          ]),
                          createVNode("div", { class: "step-text" }, "\u9078\u64C7\u5E33\u865F\u985E\u578B")
                        ], 2),
                        createVNode("div", {
                          class: ["step-line", { completed: unref(currentStep) > 1 }]
                        }, null, 2),
                        createVNode("div", {
                          class: ["step-item", { active: unref(currentStep) === 2, completed: unref(currentStep) > 2 }]
                        }, [
                          createVNode("div", { class: "step-circle" }, [
                            unref(currentStep) > 2 ? (openBlock(), createBlock(_component_Icon, {
                              key: 0,
                              name: "heroicons:check",
                              class: "w-5 h-5"
                            })) : (openBlock(), createBlock("span", { key: 1 }, "2"))
                          ]),
                          createVNode("div", { class: "step-text" }, "\u586B\u5165\u8CC7\u6599")
                        ], 2),
                        createVNode("div", {
                          class: ["step-line", { completed: unref(currentStep) > 2 }]
                        }, null, 2),
                        createVNode("div", {
                          class: ["step-item", { active: unref(currentStep) === 3, completed: unref(currentStep) > 3 }]
                        }, [
                          createVNode("div", { class: "step-circle" }, [
                            unref(currentStep) > 3 ? (openBlock(), createBlock(_component_Icon, {
                              key: 0,
                              name: "heroicons:check",
                              class: "w-5 h-5"
                            })) : (openBlock(), createBlock("span", { key: 1 }, "3"))
                          ]),
                          createVNode("div", { class: "step-text" }, "\u5B8C\u6210")
                        ], 2)
                      ])
                    ]),
                    unref(currentStep) === 1 ? (openBlock(), createBlock("div", {
                      key: 0,
                      class: "account-selection"
                    }, [
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6 mb-8" }, [
                        createVNode("div", {
                          class: ["account-option", { "selected": unref(selectedAccountType) === "personal" }]
                        }, [
                          createVNode("button", {
                            onClick: ($event) => selectAccountType("personal"),
                            class: ["account-btn personal-btn w-full", { "active": unref(selectedAccountType) === "personal" }]
                          }, [
                            createVNode(_component_Icon, {
                              name: "heroicons:user",
                              class: "w-8 h-8 mb-2"
                            }),
                            createVNode("div", { class: "text-lg font-semibold" }, "\u500B\u4EBA\u5E33\u865F")
                          ], 10, ["onClick"]),
                          createVNode("div", { class: "radio-container" }, [
                            createVNode("label", { class: "radio-label" }, [
                              createVNode("input", {
                                type: "radio",
                                name: "accountType",
                                value: "personal",
                                checked: unref(selectedAccountType) === "personal",
                                onChange: ($event) => selectAccountType("personal"),
                                class: "radio-btn"
                              }, null, 40, ["checked", "onChange"])
                            ])
                          ])
                        ], 2),
                        createVNode("div", {
                          class: ["account-option", { "selected": unref(selectedAccountType) === "business" }]
                        }, [
                          createVNode("button", {
                            onClick: ($event) => selectAccountType("business"),
                            class: ["account-btn business-btn w-full", { "active": unref(selectedAccountType) === "business" }]
                          }, [
                            createVNode(_component_Icon, {
                              name: "heroicons:building-office",
                              class: "w-8 h-8 mb-2"
                            }),
                            createVNode("div", { class: "text-lg font-semibold" }, "\u4F01\u696D\u5E33\u865F")
                          ], 10, ["onClick"]),
                          createVNode("div", { class: "radio-container" }, [
                            createVNode("label", { class: "radio-label" }, [
                              createVNode("input", {
                                type: "radio",
                                name: "accountType",
                                value: "business",
                                checked: unref(selectedAccountType) === "business",
                                onChange: ($event) => selectAccountType("business"),
                                class: "radio-btn"
                              }, null, 40, ["checked", "onChange"])
                            ])
                          ])
                        ], 2)
                      ]),
                      createVNode(_component_UButton, {
                        onClick: handleNext,
                        block: "",
                        size: "lg",
                        class: "next-btn",
                        disabled: !unref(selectedAccountType)
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" \u4E0B\u4E00\u6B65 ")
                        ]),
                        _: 1
                      }, 8, ["disabled"])
                    ])) : createCommentVNode("", true),
                    unref(currentStep) === 2 ? (openBlock(), createBlock("div", {
                      key: 1,
                      class: "form-section"
                    }, [
                      unref(selectedAccountType) === "personal" ? (openBlock(), createBlock("div", {
                        key: 0,
                        class: "form-grid"
                      }, [
                        createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" }, [
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).account,
                              "onUpdate:modelValue": ($event) => unref(formData).account = $event,
                              placeholder: "\u5E33\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).nickname,
                              "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                              placeholder: "\u66B1\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).password,
                              "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                              placeholder: "\u5BC6\u78BC",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).confirmPassword,
                              "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                              placeholder: "\u78BA\u8A8D\u5BC6\u78BC",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).fullName,
                              "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                              placeholder: "\u59D3\u540D"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).email,
                              "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                              placeholder: "\u4FE1\u7BB1",
                              type: "email"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).phone,
                              "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                              placeholder: "\u624B\u6A5F\u865F\u78BC"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).lineId,
                              "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                              placeholder: "Line\u5E33\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).companyName,
                              "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                              placeholder: "\u516C\u53F8\u540D\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).jobTitle,
                              "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                              placeholder: "\u8077\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ])
                        ])
                      ])) : createCommentVNode("", true),
                      unref(selectedAccountType) === "business" ? (openBlock(), createBlock("div", {
                        key: 1,
                        class: "form-grid"
                      }, [
                        createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" }, [
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).account,
                              "onUpdate:modelValue": ($event) => unref(formData).account = $event,
                              placeholder: "\u5E33\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).nickname,
                              "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                              placeholder: "\u66B1\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).password,
                              "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                              placeholder: "\u5BC6\u78BC",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).confirmPassword,
                              "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                              placeholder: "\u78BA\u8A8D\u5BC6\u78BC",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).fullName,
                              "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                              placeholder: "\u59D3\u540D"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).email,
                              "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                              placeholder: "\u4FE1\u7BB1",
                              type: "email"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).phone,
                              "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                              placeholder: "\u624B\u6A5F\u865F\u78BC"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).lineId,
                              "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                              placeholder: "Line\u5E33\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).companyName,
                              "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                              placeholder: "\u516C\u53F8\u540D\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).jobTitle,
                              "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                              placeholder: "\u8077\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).businessName,
                              "onUpdate:modelValue": ($event) => unref(formData).businessName = $event,
                              placeholder: "\u4F01\u696D\u540D\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).taxId,
                              "onUpdate:modelValue": ($event) => unref(formData).taxId = $event,
                              placeholder: "\u7D71\u4E00\u7DE8\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).businessPhone,
                              "onUpdate:modelValue": ($event) => unref(formData).businessPhone = $event,
                              placeholder: "\u4F01\u696D\u96FB\u8A71"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ])
                        ])
                      ])) : createCommentVNode("", true),
                      createVNode("div", { class: "flex gap-4 mt-4" }, [
                        createVNode(_component_UButton, {
                          onClick: handleRegister,
                          size: "lg",
                          class: "register-btn flex-1",
                          loading: unref(loading)
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" \u8A3B\u518A ")
                          ]),
                          _: 1
                        }, 8, ["loading"]),
                        createVNode(_component_UButton, {
                          onClick: goBack,
                          variant: "outline",
                          size: "lg",
                          class: "back-btn flex-1"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" \u56DE\u4E0A\u4E00\u9801 ")
                          ]),
                          _: 1
                        })
                      ])
                    ])) : createCommentVNode("", true),
                    unref(currentStep) === 3 ? (openBlock(), createBlock("div", {
                      key: 2,
                      class: "completion-section text-center"
                    }, [
                      createVNode(_component_Icon, {
                        name: "heroicons:check-circle",
                        class: "w-16 h-16 text-green-500 mx-auto mb-4"
                      }),
                      createVNode("h3", { class: "text-2xl font-bold mb-4 text-gray-800" }, "\u8A3B\u518A\u5B8C\u6210\uFF01"),
                      createVNode("p", { class: "text-gray-600 mb-6" }, "\u60A8\u7684\u5E33\u865F\u5DF2\u6210\u529F\u5EFA\u7ACB"),
                      createVNode(_component_UButton, {
                        onClick: ($event) => _ctx.$router.push("/login"),
                        size: "lg",
                        class: "login-btn"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" \u524D\u5F80\u767B\u5165 ")
                        ]),
                        _: 1
                      }, 8, ["onClick"])
                    ])) : createCommentVNode("", true)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-center pt-16 pb-8 w-full" }, [
                createVNode(_component_UCard, {
                  class: ["signup-card w-full max-w-lg", { "mb-8": unref(selectedAccountType) === "business" && unref(currentStep) === 2 }]
                }, {
                  default: withCtx(() => [
                    createVNode("div", { class: "progress-container mb-8" }, [
                      createVNode("div", { class: "flex justify-between items-center" }, [
                        createVNode("div", {
                          class: ["step-item", { active: unref(currentStep) === 1, completed: unref(currentStep) > 1 }]
                        }, [
                          createVNode("div", { class: "step-circle" }, [
                            unref(currentStep) > 1 ? (openBlock(), createBlock(_component_Icon, {
                              key: 0,
                              name: "heroicons:check",
                              class: "w-5 h-5"
                            })) : (openBlock(), createBlock("span", { key: 1 }, "1"))
                          ]),
                          createVNode("div", { class: "step-text" }, "\u9078\u64C7\u5E33\u865F\u985E\u578B")
                        ], 2),
                        createVNode("div", {
                          class: ["step-line", { completed: unref(currentStep) > 1 }]
                        }, null, 2),
                        createVNode("div", {
                          class: ["step-item", { active: unref(currentStep) === 2, completed: unref(currentStep) > 2 }]
                        }, [
                          createVNode("div", { class: "step-circle" }, [
                            unref(currentStep) > 2 ? (openBlock(), createBlock(_component_Icon, {
                              key: 0,
                              name: "heroicons:check",
                              class: "w-5 h-5"
                            })) : (openBlock(), createBlock("span", { key: 1 }, "2"))
                          ]),
                          createVNode("div", { class: "step-text" }, "\u586B\u5165\u8CC7\u6599")
                        ], 2),
                        createVNode("div", {
                          class: ["step-line", { completed: unref(currentStep) > 2 }]
                        }, null, 2),
                        createVNode("div", {
                          class: ["step-item", { active: unref(currentStep) === 3, completed: unref(currentStep) > 3 }]
                        }, [
                          createVNode("div", { class: "step-circle" }, [
                            unref(currentStep) > 3 ? (openBlock(), createBlock(_component_Icon, {
                              key: 0,
                              name: "heroicons:check",
                              class: "w-5 h-5"
                            })) : (openBlock(), createBlock("span", { key: 1 }, "3"))
                          ]),
                          createVNode("div", { class: "step-text" }, "\u5B8C\u6210")
                        ], 2)
                      ])
                    ]),
                    unref(currentStep) === 1 ? (openBlock(), createBlock("div", {
                      key: 0,
                      class: "account-selection"
                    }, [
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6 mb-8" }, [
                        createVNode("div", {
                          class: ["account-option", { "selected": unref(selectedAccountType) === "personal" }]
                        }, [
                          createVNode("button", {
                            onClick: ($event) => selectAccountType("personal"),
                            class: ["account-btn personal-btn w-full", { "active": unref(selectedAccountType) === "personal" }]
                          }, [
                            createVNode(_component_Icon, {
                              name: "heroicons:user",
                              class: "w-8 h-8 mb-2"
                            }),
                            createVNode("div", { class: "text-lg font-semibold" }, "\u500B\u4EBA\u5E33\u865F")
                          ], 10, ["onClick"]),
                          createVNode("div", { class: "radio-container" }, [
                            createVNode("label", { class: "radio-label" }, [
                              createVNode("input", {
                                type: "radio",
                                name: "accountType",
                                value: "personal",
                                checked: unref(selectedAccountType) === "personal",
                                onChange: ($event) => selectAccountType("personal"),
                                class: "radio-btn"
                              }, null, 40, ["checked", "onChange"])
                            ])
                          ])
                        ], 2),
                        createVNode("div", {
                          class: ["account-option", { "selected": unref(selectedAccountType) === "business" }]
                        }, [
                          createVNode("button", {
                            onClick: ($event) => selectAccountType("business"),
                            class: ["account-btn business-btn w-full", { "active": unref(selectedAccountType) === "business" }]
                          }, [
                            createVNode(_component_Icon, {
                              name: "heroicons:building-office",
                              class: "w-8 h-8 mb-2"
                            }),
                            createVNode("div", { class: "text-lg font-semibold" }, "\u4F01\u696D\u5E33\u865F")
                          ], 10, ["onClick"]),
                          createVNode("div", { class: "radio-container" }, [
                            createVNode("label", { class: "radio-label" }, [
                              createVNode("input", {
                                type: "radio",
                                name: "accountType",
                                value: "business",
                                checked: unref(selectedAccountType) === "business",
                                onChange: ($event) => selectAccountType("business"),
                                class: "radio-btn"
                              }, null, 40, ["checked", "onChange"])
                            ])
                          ])
                        ], 2)
                      ]),
                      createVNode(_component_UButton, {
                        onClick: handleNext,
                        block: "",
                        size: "lg",
                        class: "next-btn",
                        disabled: !unref(selectedAccountType)
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" \u4E0B\u4E00\u6B65 ")
                        ]),
                        _: 1
                      }, 8, ["disabled"])
                    ])) : createCommentVNode("", true),
                    unref(currentStep) === 2 ? (openBlock(), createBlock("div", {
                      key: 1,
                      class: "form-section"
                    }, [
                      unref(selectedAccountType) === "personal" ? (openBlock(), createBlock("div", {
                        key: 0,
                        class: "form-grid"
                      }, [
                        createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" }, [
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).account,
                              "onUpdate:modelValue": ($event) => unref(formData).account = $event,
                              placeholder: "\u5E33\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).nickname,
                              "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                              placeholder: "\u66B1\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).password,
                              "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                              placeholder: "\u5BC6\u78BC",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).confirmPassword,
                              "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                              placeholder: "\u78BA\u8A8D\u5BC6\u78BC",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).fullName,
                              "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                              placeholder: "\u59D3\u540D"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).email,
                              "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                              placeholder: "\u4FE1\u7BB1",
                              type: "email"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).phone,
                              "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                              placeholder: "\u624B\u6A5F\u865F\u78BC"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).lineId,
                              "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                              placeholder: "Line\u5E33\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).companyName,
                              "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                              placeholder: "\u516C\u53F8\u540D\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).jobTitle,
                              "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                              placeholder: "\u8077\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ])
                        ])
                      ])) : createCommentVNode("", true),
                      unref(selectedAccountType) === "business" ? (openBlock(), createBlock("div", {
                        key: 1,
                        class: "form-grid"
                      }, [
                        createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" }, [
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).account,
                              "onUpdate:modelValue": ($event) => unref(formData).account = $event,
                              placeholder: "\u5E33\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).nickname,
                              "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                              placeholder: "\u66B1\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).password,
                              "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                              placeholder: "\u5BC6\u78BC",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).confirmPassword,
                              "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                              placeholder: "\u78BA\u8A8D\u5BC6\u78BC",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).fullName,
                              "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                              placeholder: "\u59D3\u540D"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).email,
                              "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                              placeholder: "\u4FE1\u7BB1",
                              type: "email"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).phone,
                              "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                              placeholder: "\u624B\u6A5F\u865F\u78BC"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).lineId,
                              "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                              placeholder: "Line\u5E33\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).companyName,
                              "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                              placeholder: "\u516C\u53F8\u540D\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).jobTitle,
                              "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                              placeholder: "\u8077\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).businessName,
                              "onUpdate:modelValue": ($event) => unref(formData).businessName = $event,
                              placeholder: "\u4F01\u696D\u540D\u7A31"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).taxId,
                              "onUpdate:modelValue": ($event) => unref(formData).taxId = $event,
                              placeholder: "\u7D71\u4E00\u7DE8\u865F"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).businessPhone,
                              "onUpdate:modelValue": ($event) => unref(formData).businessPhone = $event,
                              placeholder: "\u4F01\u696D\u96FB\u8A71"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ])
                        ])
                      ])) : createCommentVNode("", true),
                      createVNode("div", { class: "flex gap-4 mt-4" }, [
                        createVNode(_component_UButton, {
                          onClick: handleRegister,
                          size: "lg",
                          class: "register-btn flex-1",
                          loading: unref(loading)
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" \u8A3B\u518A ")
                          ]),
                          _: 1
                        }, 8, ["loading"]),
                        createVNode(_component_UButton, {
                          onClick: goBack,
                          variant: "outline",
                          size: "lg",
                          class: "back-btn flex-1"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" \u56DE\u4E0A\u4E00\u9801 ")
                          ]),
                          _: 1
                        })
                      ])
                    ])) : createCommentVNode("", true),
                    unref(currentStep) === 3 ? (openBlock(), createBlock("div", {
                      key: 2,
                      class: "completion-section text-center"
                    }, [
                      createVNode(_component_Icon, {
                        name: "heroicons:check-circle",
                        class: "w-16 h-16 text-green-500 mx-auto mb-4"
                      }),
                      createVNode("h3", { class: "text-2xl font-bold mb-4 text-gray-800" }, "\u8A3B\u518A\u5B8C\u6210\uFF01"),
                      createVNode("p", { class: "text-gray-600 mb-6" }, "\u60A8\u7684\u5E33\u865F\u5DF2\u6210\u529F\u5EFA\u7ACB"),
                      createVNode(_component_UButton, {
                        onClick: ($event) => _ctx.$router.push("/login"),
                        size: "lg",
                        class: "login-btn"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" \u524D\u5F80\u767B\u5165 ")
                        ]),
                        _: 1
                      }, 8, ["onClick"])
                    ])) : createCommentVNode("", true)
                  ]),
                  _: 1
                }, 8, ["class"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/signup.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const signup = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-c59a5275"]]);

export { signup as default };
//# sourceMappingURL=signup-ByYqj2Kp.mjs.map
