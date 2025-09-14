import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { _ as __nuxt_component_1 } from "./Card-DBUe4X9m.js";
import { a as _export_sfc, u as useNuxtApp, b as __nuxt_component_1$1, c as __nuxt_component_0$1 } from "../server.mjs";
import { _ as __nuxt_component_2 } from "./Input-CLveazjF.js";
import { ref, mergeProps, withCtx, unref, createTextVNode, createVNode, createBlock, createCommentVNode, openBlock, useSSRContext } from "vue";
import { ssrRenderComponent, ssrRenderClass, ssrIncludeBooleanAttr } from "vue/server-renderer";
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
  __name: "signup",
  __ssrInlineRender: true,
  setup(__props) {
    const { $swal } = useNuxtApp();
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
      if (!await validateForm()) {
        return;
      }
      loading.value = true;
      try {
        await new Promise((resolve) => setTimeout(resolve, 2e3));
        currentStep.value = 3;
      } catch (error) {
        console.error("Registration error:", error);
        await $swal.fire({
          title: "註冊失敗",
          text: "請稍後再試",
          icon: "error",
          confirmButtonText: "確定",
          confirmButtonColor: "#ef4444"
        });
      } finally {
        loading.value = false;
      }
    };
    const validateForm = async () => {
      const requiredFields = ["account", "nickname", "password", "confirmPassword", "fullName", "email", "phone"];
      for (const field of requiredFields) {
        if (!formData.value[field]) {
          await $swal.fire({
            title: "欄位未填寫完整",
            text: "請填寫所有必填欄位",
            icon: "warning",
            confirmButtonText: "確定",
            confirmButtonColor: "#f59e0b"
          });
          return false;
        }
      }
      if (formData.value.password !== formData.value.confirmPassword) {
        await $swal.fire({
          title: "密碼不一致",
          text: "密碼與確認密碼不符",
          icon: "error",
          confirmButtonText: "確定",
          confirmButtonColor: "#ef4444"
        });
        return false;
      }
      if (selectedAccountType.value === "business") {
        if (!formData.value.businessName || !formData.value.taxId) {
          await $swal.fire({
            title: "企業資料未完整",
            text: "請填寫企業相關資料",
            icon: "warning",
            confirmButtonText: "確定",
            confirmButtonColor: "#f59e0b"
          });
          return false;
        }
      }
      return true;
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_UCard = __nuxt_component_1;
      const _component_Icon = __nuxt_component_1$1;
      const _component_UButton = __nuxt_component_0$1;
      const _component_UInput = __nuxt_component_2;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({
        name: "auth",
        "main-class": `flex items-start justify-center pt-16 pb-8`,
        "logo-style": `width: auto;`
      }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`註冊`);
          } else {
            return [
              createTextVNode("註冊")
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-center pt-16 pb-8 w-full" data-v-bd9e0b1f${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UCard, {
              class: ["signup-card w-full max-w-lg", { "mb-8": unref(selectedAccountType) === "business" && unref(currentStep) === 2 }]
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="progress-container mb-8" data-v-bd9e0b1f${_scopeId2}><div class="flex justify-between items-center" data-v-bd9e0b1f${_scopeId2}><div class="${ssrRenderClass([{ active: unref(currentStep) === 1, completed: unref(currentStep) > 1 }, "step-item"])}" data-v-bd9e0b1f${_scopeId2}><div class="step-circle" data-v-bd9e0b1f${_scopeId2}>`);
                  if (unref(currentStep) > 1) {
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:check",
                      class: "w-5 h-5"
                    }, null, _parent3, _scopeId2));
                  } else {
                    _push3(`<span data-v-bd9e0b1f${_scopeId2}>1</span>`);
                  }
                  _push3(`</div><div class="step-text" data-v-bd9e0b1f${_scopeId2}>選擇帳號類型</div></div><div class="${ssrRenderClass([{ completed: unref(currentStep) > 1 }, "step-line"])}" data-v-bd9e0b1f${_scopeId2}></div><div class="${ssrRenderClass([{ active: unref(currentStep) === 2, completed: unref(currentStep) > 2 }, "step-item"])}" data-v-bd9e0b1f${_scopeId2}><div class="step-circle" data-v-bd9e0b1f${_scopeId2}>`);
                  if (unref(currentStep) > 2) {
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:check",
                      class: "w-5 h-5"
                    }, null, _parent3, _scopeId2));
                  } else {
                    _push3(`<span data-v-bd9e0b1f${_scopeId2}>2</span>`);
                  }
                  _push3(`</div><div class="step-text" data-v-bd9e0b1f${_scopeId2}>填入資料</div></div><div class="${ssrRenderClass([{ completed: unref(currentStep) > 2 }, "step-line"])}" data-v-bd9e0b1f${_scopeId2}></div><div class="${ssrRenderClass([{ active: unref(currentStep) === 3, completed: unref(currentStep) > 3 }, "step-item"])}" data-v-bd9e0b1f${_scopeId2}><div class="step-circle" data-v-bd9e0b1f${_scopeId2}>`);
                  if (unref(currentStep) > 3) {
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:check",
                      class: "w-5 h-5"
                    }, null, _parent3, _scopeId2));
                  } else {
                    _push3(`<span data-v-bd9e0b1f${_scopeId2}>3</span>`);
                  }
                  _push3(`</div><div class="step-text" data-v-bd9e0b1f${_scopeId2}>完成</div></div></div></div>`);
                  if (unref(currentStep) === 1) {
                    _push3(`<div class="account-selection" data-v-bd9e0b1f${_scopeId2}><div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8" data-v-bd9e0b1f${_scopeId2}><div class="${ssrRenderClass([{ "selected": unref(selectedAccountType) === "personal" }, "account-option"])}" data-v-bd9e0b1f${_scopeId2}><button class="${ssrRenderClass([{ "active": unref(selectedAccountType) === "personal" }, "account-btn personal-btn w-full"])}" data-v-bd9e0b1f${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:user",
                      class: "w-8 h-8 mb-2"
                    }, null, _parent3, _scopeId2));
                    _push3(`<div class="text-lg font-semibold" data-v-bd9e0b1f${_scopeId2}>個人帳號</div></button><div class="radio-container" data-v-bd9e0b1f${_scopeId2}><label class="radio-label" data-v-bd9e0b1f${_scopeId2}><input type="radio" name="accountType" value="personal"${ssrIncludeBooleanAttr(unref(selectedAccountType) === "personal") ? " checked" : ""} class="radio-btn" data-v-bd9e0b1f${_scopeId2}></label></div></div><div class="${ssrRenderClass([{ "selected": unref(selectedAccountType) === "business" }, "account-option"])}" data-v-bd9e0b1f${_scopeId2}><button class="${ssrRenderClass([{ "active": unref(selectedAccountType) === "business" }, "account-btn business-btn w-full"])}" data-v-bd9e0b1f${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:building-office",
                      class: "w-8 h-8 mb-2"
                    }, null, _parent3, _scopeId2));
                    _push3(`<div class="text-lg font-semibold" data-v-bd9e0b1f${_scopeId2}>企業帳號</div></button><div class="radio-container" data-v-bd9e0b1f${_scopeId2}><label class="radio-label" data-v-bd9e0b1f${_scopeId2}><input type="radio" name="accountType" value="business"${ssrIncludeBooleanAttr(unref(selectedAccountType) === "business") ? " checked" : ""} class="radio-btn" data-v-bd9e0b1f${_scopeId2}></label></div></div></div>`);
                    _push3(ssrRenderComponent(_component_UButton, {
                      onClick: handleNext,
                      block: "",
                      size: "lg",
                      class: "next-btn",
                      disabled: !unref(selectedAccountType)
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` 下一步 `);
                        } else {
                          return [
                            createTextVNode(" 下一步 ")
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
                    _push3(`<div class="form-section" data-v-bd9e0b1f${_scopeId2}>`);
                    if (unref(selectedAccountType) === "personal") {
                      _push3(`<div class="form-grid" data-v-bd9e0b1f${_scopeId2}><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" data-v-bd9e0b1f${_scopeId2}><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).account,
                        "onUpdate:modelValue": ($event) => unref(formData).account = $event,
                        placeholder: "帳號"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).nickname,
                        "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                        placeholder: "暱稱"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).password,
                        "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                        placeholder: "密碼",
                        type: "password"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).confirmPassword,
                        "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                        placeholder: "確認密碼",
                        type: "password"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).fullName,
                        "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                        placeholder: "姓名"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).email,
                        "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                        placeholder: "信箱",
                        type: "email"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).phone,
                        "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                        placeholder: "手機號碼"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).lineId,
                        "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                        placeholder: "Line帳號"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).companyName,
                        "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                        placeholder: "公司名稱"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).jobTitle,
                        "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                        placeholder: "職稱"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div></div></div>`);
                    } else {
                      _push3(`<!---->`);
                    }
                    if (unref(selectedAccountType) === "business") {
                      _push3(`<div class="form-grid" data-v-bd9e0b1f${_scopeId2}><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" data-v-bd9e0b1f${_scopeId2}><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).account,
                        "onUpdate:modelValue": ($event) => unref(formData).account = $event,
                        placeholder: "帳號"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).nickname,
                        "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                        placeholder: "暱稱"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).password,
                        "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                        placeholder: "密碼",
                        type: "password"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).confirmPassword,
                        "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                        placeholder: "確認密碼",
                        type: "password"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).fullName,
                        "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                        placeholder: "姓名"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).email,
                        "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                        placeholder: "信箱",
                        type: "email"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).phone,
                        "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                        placeholder: "手機號碼"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).lineId,
                        "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                        placeholder: "Line帳號"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).companyName,
                        "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                        placeholder: "公司名稱"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).jobTitle,
                        "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                        placeholder: "職稱"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).businessName,
                        "onUpdate:modelValue": ($event) => unref(formData).businessName = $event,
                        placeholder: "企業名稱"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).taxId,
                        "onUpdate:modelValue": ($event) => unref(formData).taxId = $event,
                        placeholder: "統一編號"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div><div class="form-field" data-v-bd9e0b1f${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UInput, {
                        modelValue: unref(formData).businessPhone,
                        "onUpdate:modelValue": ($event) => unref(formData).businessPhone = $event,
                        placeholder: "企業電話"
                      }, null, _parent3, _scopeId2));
                      _push3(`</div></div></div>`);
                    } else {
                      _push3(`<!---->`);
                    }
                    _push3(`<div class="flex gap-4 mt-4" data-v-bd9e0b1f${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_UButton, {
                      onClick: handleRegister,
                      size: "lg",
                      class: "register-btn flex-1",
                      loading: unref(loading)
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` 註冊 `);
                        } else {
                          return [
                            createTextVNode(" 註冊 ")
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
                          _push4(` 回上一頁 `);
                        } else {
                          return [
                            createTextVNode(" 回上一頁 ")
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
                    _push3(`<div class="completion-section text-center" data-v-bd9e0b1f${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_Icon, {
                      name: "heroicons:check-circle",
                      class: "w-16 h-16 text-green-500 mx-auto mb-4"
                    }, null, _parent3, _scopeId2));
                    _push3(`<h3 class="text-2xl font-bold mb-4 text-gray-800" data-v-bd9e0b1f${_scopeId2}>註冊完成！</h3><p class="text-gray-600 mb-6" data-v-bd9e0b1f${_scopeId2}>您的帳號已成功建立</p>`);
                    _push3(ssrRenderComponent(_component_UButton, {
                      onClick: ($event) => _ctx.$router.push("/login"),
                      size: "lg",
                      class: "login-btn"
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` 前往登入 `);
                        } else {
                          return [
                            createTextVNode(" 前往登入 ")
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
                          createVNode("div", { class: "step-text" }, "選擇帳號類型")
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
                          createVNode("div", { class: "step-text" }, "填入資料")
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
                          createVNode("div", { class: "step-text" }, "完成")
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
                            createVNode("div", { class: "text-lg font-semibold" }, "個人帳號")
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
                            createVNode("div", { class: "text-lg font-semibold" }, "企業帳號")
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
                          createTextVNode(" 下一步 ")
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
                              placeholder: "帳號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).nickname,
                              "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                              placeholder: "暱稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).password,
                              "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                              placeholder: "密碼",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).confirmPassword,
                              "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                              placeholder: "確認密碼",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).fullName,
                              "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                              placeholder: "姓名"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).email,
                              "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                              placeholder: "信箱",
                              type: "email"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).phone,
                              "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                              placeholder: "手機號碼"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).lineId,
                              "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                              placeholder: "Line帳號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).companyName,
                              "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                              placeholder: "公司名稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).jobTitle,
                              "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                              placeholder: "職稱"
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
                              placeholder: "帳號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).nickname,
                              "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                              placeholder: "暱稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).password,
                              "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                              placeholder: "密碼",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).confirmPassword,
                              "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                              placeholder: "確認密碼",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).fullName,
                              "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                              placeholder: "姓名"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).email,
                              "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                              placeholder: "信箱",
                              type: "email"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).phone,
                              "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                              placeholder: "手機號碼"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).lineId,
                              "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                              placeholder: "Line帳號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).companyName,
                              "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                              placeholder: "公司名稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).jobTitle,
                              "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                              placeholder: "職稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).businessName,
                              "onUpdate:modelValue": ($event) => unref(formData).businessName = $event,
                              placeholder: "企業名稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).taxId,
                              "onUpdate:modelValue": ($event) => unref(formData).taxId = $event,
                              placeholder: "統一編號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).businessPhone,
                              "onUpdate:modelValue": ($event) => unref(formData).businessPhone = $event,
                              placeholder: "企業電話"
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
                            createTextVNode(" 註冊 ")
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
                            createTextVNode(" 回上一頁 ")
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
                      createVNode("h3", { class: "text-2xl font-bold mb-4 text-gray-800" }, "註冊完成！"),
                      createVNode("p", { class: "text-gray-600 mb-6" }, "您的帳號已成功建立"),
                      createVNode(_component_UButton, {
                        onClick: ($event) => _ctx.$router.push("/login"),
                        size: "lg",
                        class: "login-btn"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" 前往登入 ")
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
                          createVNode("div", { class: "step-text" }, "選擇帳號類型")
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
                          createVNode("div", { class: "step-text" }, "填入資料")
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
                          createVNode("div", { class: "step-text" }, "完成")
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
                            createVNode("div", { class: "text-lg font-semibold" }, "個人帳號")
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
                            createVNode("div", { class: "text-lg font-semibold" }, "企業帳號")
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
                          createTextVNode(" 下一步 ")
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
                              placeholder: "帳號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).nickname,
                              "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                              placeholder: "暱稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).password,
                              "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                              placeholder: "密碼",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).confirmPassword,
                              "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                              placeholder: "確認密碼",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).fullName,
                              "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                              placeholder: "姓名"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).email,
                              "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                              placeholder: "信箱",
                              type: "email"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).phone,
                              "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                              placeholder: "手機號碼"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).lineId,
                              "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                              placeholder: "Line帳號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).companyName,
                              "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                              placeholder: "公司名稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).jobTitle,
                              "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                              placeholder: "職稱"
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
                              placeholder: "帳號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).nickname,
                              "onUpdate:modelValue": ($event) => unref(formData).nickname = $event,
                              placeholder: "暱稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).password,
                              "onUpdate:modelValue": ($event) => unref(formData).password = $event,
                              placeholder: "密碼",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).confirmPassword,
                              "onUpdate:modelValue": ($event) => unref(formData).confirmPassword = $event,
                              placeholder: "確認密碼",
                              type: "password"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).fullName,
                              "onUpdate:modelValue": ($event) => unref(formData).fullName = $event,
                              placeholder: "姓名"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).email,
                              "onUpdate:modelValue": ($event) => unref(formData).email = $event,
                              placeholder: "信箱",
                              type: "email"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).phone,
                              "onUpdate:modelValue": ($event) => unref(formData).phone = $event,
                              placeholder: "手機號碼"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).lineId,
                              "onUpdate:modelValue": ($event) => unref(formData).lineId = $event,
                              placeholder: "Line帳號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).companyName,
                              "onUpdate:modelValue": ($event) => unref(formData).companyName = $event,
                              placeholder: "公司名稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).jobTitle,
                              "onUpdate:modelValue": ($event) => unref(formData).jobTitle = $event,
                              placeholder: "職稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).businessName,
                              "onUpdate:modelValue": ($event) => unref(formData).businessName = $event,
                              placeholder: "企業名稱"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).taxId,
                              "onUpdate:modelValue": ($event) => unref(formData).taxId = $event,
                              placeholder: "統一編號"
                            }, null, 8, ["modelValue", "onUpdate:modelValue"])
                          ]),
                          createVNode("div", { class: "form-field" }, [
                            createVNode(_component_UInput, {
                              modelValue: unref(formData).businessPhone,
                              "onUpdate:modelValue": ($event) => unref(formData).businessPhone = $event,
                              placeholder: "企業電話"
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
                            createTextVNode(" 註冊 ")
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
                            createTextVNode(" 回上一頁 ")
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
                      createVNode("h3", { class: "text-2xl font-bold mb-4 text-gray-800" }, "註冊完成！"),
                      createVNode("p", { class: "text-gray-600 mb-6" }, "您的帳號已成功建立"),
                      createVNode(_component_UButton, {
                        onClick: ($event) => _ctx.$router.push("/login"),
                        size: "lg",
                        class: "login-btn"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" 前往登入 ")
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
const signup = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-bd9e0b1f"]]);
export {
  signup as default
};
//# sourceMappingURL=signup-B6iJGPcY.js.map
