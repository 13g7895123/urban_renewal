import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { u as useNuxtApp, j as useRouter, b as __nuxt_component_1, k as useRuntimeConfig } from "../server.mjs";
import { ref, reactive, mergeProps, withCtx, createVNode, createBlock, createCommentVNode, createTextVNode, openBlock, withModifiers, withDirectives, vModelText, toDisplayString, Fragment, renderList, vModelSelect, useSSRContext } from "vue";
import { ssrRenderComponent, ssrRenderAttr, ssrInterpolate, ssrIncludeBooleanAttr, ssrRenderList, ssrLooseContain, ssrLooseEqual } from "vue/server-renderer";
import "vue-router";
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
import "tailwind-merge";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/@unhead/vue/dist/index.mjs";
import "@iconify/vue";
import "ohash/utils";
import "@iconify/utils/lib/css/icon";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/perfect-debounce/dist/index.mjs";
const _sfc_main = {
  __name: "urban-renewal",
  __ssrInlineRender: true,
  setup(__props) {
    const { $swal } = useNuxtApp();
    const pageSize = ref(10);
    const showCreateModal = ref(false);
    const loading = ref(false);
    const isSubmitting = ref(false);
    const error = ref("");
    const formData = reactive({
      name: "",
      area: "",
      memberCount: "",
      chairmanName: "",
      chairmanPhone: ""
    });
    const renewals = ref([]);
    const runtimeConfig = useRuntimeConfig();
    const router = useRouter();
    const fetchRenewals = async () => {
      loading.value = true;
      error.value = "";
      try {
        const response = await $fetch("/api/urban-renewals", {
          baseURL: runtimeConfig.public.apiBaseUrl
        });
        if (response.status === "success") {
          renewals.value = response.data || [];
        } else {
          error.value = response.message || "獲取資料失敗";
        }
      } catch (err) {
        console.error("Fetch error:", err);
        error.value = "無法連接到伺服器";
      } finally {
        loading.value = false;
      }
    };
    const createUrbanRenewal = async (data) => {
      var _a;
      try {
        const response = await $fetch("/api/urban-renewals", {
          method: "POST",
          baseURL: runtimeConfig.public.apiBaseUrl,
          headers: {
            "Content-Type": "application/json"
          },
          body: {
            name: data.name,
            area: parseFloat(data.area),
            memberCount: parseInt(data.memberCount),
            chairmanName: data.chairmanName,
            chairmanPhone: data.chairmanPhone
          }
        });
        return response;
      } catch (err) {
        console.error("Create error:", err);
        throw new Error(((_a = err.data) == null ? void 0 : _a.message) || "新增失敗");
      }
    };
    const deleteUrbanRenewal = async (id) => {
      var _a;
      try {
        const response = await $fetch(`/api/urban-renewals/${id}`, {
          method: "DELETE",
          baseURL: runtimeConfig.public.apiBaseUrl
        });
        return response;
      } catch (err) {
        console.error("Delete error:", err);
        throw new Error(((_a = err.data) == null ? void 0 : _a.message) || "刪除失敗");
      }
    };
    const allocateRenewal = () => {
      console.log("Allocating renewal meeting");
    };
    const createRenewal = () => {
      showCreateModal.value = true;
    };
    const closeModal = () => {
      showCreateModal.value = false;
      resetForm();
      error.value = "";
    };
    const resetForm = () => {
      formData.name = "";
      formData.area = "";
      formData.memberCount = "";
      formData.chairmanName = "";
      formData.chairmanPhone = "";
    };
    const fillRandomTestData = () => {
      const urbanRenewalNames = [
        "大安區忠孝更新會",
        "信義區松仁更新會",
        "中山區民權更新會",
        "萬華區西門更新會",
        "士林區天母更新會",
        "內湖區科技更新會",
        "南港區經貿更新會",
        "文山區木柵更新會",
        "北投區石牌更新會",
        "松山區民生更新會",
        "中正區博愛更新會",
        "大同區迪化更新會"
      ];
      const chairmanNames = [
        "陳志明",
        "林美玲",
        "王建國",
        "張淑芬",
        "李國華",
        "劉玉婷",
        "吳明德",
        "黃秀英",
        "鄭文昌",
        "謝雅雯",
        "周志偉",
        "徐淑惠",
        "蔡政宏",
        "許雅琴",
        "楊明峰",
        "游淑華",
        "賴志強",
        "沈美珠",
        "潘文傑",
        "蘇雅雲"
      ];
      const randomName = urbanRenewalNames[Math.floor(Math.random() * urbanRenewalNames.length)];
      const randomArea = Math.floor(Math.random() * 4500) + 500;
      const randomMemberCount = Math.floor(Math.random() * 135) + 15;
      const randomChairmanName = chairmanNames[Math.floor(Math.random() * chairmanNames.length)];
      const generateTaiwanPhone = () => {
        const prefixes = ["09"];
        const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
        const suffix = Math.floor(Math.random() * 1e8).toString().padStart(8, "0");
        return prefix + suffix;
      };
      formData.name = randomName;
      formData.area = randomArea.toString();
      formData.memberCount = randomMemberCount.toString();
      formData.chairmanName = randomChairmanName;
      formData.chairmanPhone = generateTaiwanPhone();
    };
    const onSubmit = async () => {
      if (!formData.name || !formData.area || !formData.memberCount || !formData.chairmanName || !formData.chairmanPhone) {
        error.value = "請填寫所有必填項目";
        return;
      }
      isSubmitting.value = true;
      error.value = "";
      try {
        const response = await createUrbanRenewal(formData);
        if (response.status === "success") {
          await fetchRenewals();
          closeModal();
          $swal.fire({
            title: "新增成功！",
            text: "更新會已成功建立",
            icon: "success",
            confirmButtonText: "確定",
            confirmButtonColor: "#10b981"
          });
        } else {
          error.value = response.message || "新增失敗";
        }
      } catch (err) {
        error.value = err.message || "新增失敗，請稍後再試";
      } finally {
        isSubmitting.value = false;
      }
    };
    const viewBasicInfo = (renewal) => {
      router.push(`/tables/urban-renewal/${renewal.id}/basic-info`);
    };
    const viewMembers = (renewal) => {
      console.log("Viewing members for:", renewal);
    };
    const viewJointInfo = (renewal) => {
      console.log("Viewing joint info for:", renewal);
    };
    const deleteRenewal = async (renewal) => {
      const result = await $swal.fire({
        title: "確認刪除",
        text: `確定要刪除「${renewal.name}」嗎？`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "確定刪除",
        cancelButtonText: "取消"
      });
      if (!result.isConfirmed) {
        return;
      }
      try {
        const response = await deleteUrbanRenewal(renewal.id);
        if (response.status === "success") {
          await fetchRenewals();
          $swal.fire({
            title: "刪除成功！",
            text: "更新會已成功刪除",
            icon: "success",
            confirmButtonText: "確定",
            confirmButtonColor: "#10b981"
          });
        } else {
          $swal.fire({
            title: "刪除失敗",
            text: response.message || "刪除失敗",
            icon: "error",
            confirmButtonText: "確定",
            confirmButtonColor: "#ef4444"
          });
        }
      } catch (err) {
        $swal.fire({
          title: "刪除失敗",
          text: err.message || "刪除失敗，請稍後再試",
          icon: "error",
          confirmButtonText: "確定",
          confirmButtonColor: "#ef4444"
        });
      }
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      const _component_Icon = __nuxt_component_1;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({ name: "main" }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`更新會管理`);
          } else {
            return [
              createTextVNode("更新會管理")
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
            _push2(`</div><h2 class="text-2xl font-semibold"${_scopeId}>更新會</h2></div></div><div class="flex justify-end gap-4 mb-6"${_scopeId}><button class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:users",
              class: "w-5 h-5 mr-2"
            }, null, _parent2, _scopeId));
            _push2(` 分配更新會 </button><button class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:plus",
              class: "w-5 h-5 mr-2"
            }, null, _parent2, _scopeId));
            _push2(` 新建更新會 </button></div>`);
            if (showCreateModal.value) {
              _push2(`<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"${_scopeId}><div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"${_scopeId}><div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"${_scopeId}></div><div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"${_scopeId}><div class="border-b border-gray-200 pb-4 mb-6"${_scopeId}><div class="flex justify-between items-center"${_scopeId}><h3 class="text-lg font-semibold text-gray-900"${_scopeId}>新建更新會</h3><button type="button" class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_Icon, {
                name: "heroicons:beaker",
                class: "w-4 h-4 mr-1 inline"
              }, null, _parent2, _scopeId));
              _push2(` 填入測試資料 </button></div></div><form${_scopeId}><div class="space-y-6"${_scopeId}><div${_scopeId}><label for="name" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>更新會名稱 <span class="text-red-500"${_scopeId}>*</span></label><input id="name"${ssrRenderAttr("value", formData.name)} type="text" placeholder="請輸入更新會名稱" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required${_scopeId}></div><div${_scopeId}><label for="area" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>土地面積(平方公尺) <span class="text-red-500"${_scopeId}>*</span></label><input id="area"${ssrRenderAttr("value", formData.area)} type="number" placeholder="請輸入土地面積" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required${_scopeId}></div><div${_scopeId}><label for="memberCount" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>所有權人數 <span class="text-red-500"${_scopeId}>*</span></label><input id="memberCount"${ssrRenderAttr("value", formData.memberCount)} type="number" placeholder="請輸入所有權人數" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required${_scopeId}></div><div${_scopeId}><label for="chairmanName" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>理事長姓名 <span class="text-red-500"${_scopeId}>*</span></label><input id="chairmanName"${ssrRenderAttr("value", formData.chairmanName)} type="text" placeholder="請輸入理事長姓名" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required${_scopeId}></div><div${_scopeId}><label for="chairmanPhone" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>理事長電話 <span class="text-red-500"${_scopeId}>*</span></label><input id="chairmanPhone"${ssrRenderAttr("value", formData.chairmanPhone)} type="tel" placeholder="請輸入理事長電話" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required${_scopeId}></div></div>`);
              if (error.value) {
                _push2(`<div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"${_scopeId}>${ssrInterpolate(error.value)}</div>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200"${_scopeId}><button type="button"${ssrIncludeBooleanAttr(isSubmitting.value) ? " disabled" : ""} class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"${_scopeId}> 取消 </button><button type="submit"${ssrIncludeBooleanAttr(isSubmitting.value) ? " disabled" : ""} class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"${_scopeId}>`);
              if (isSubmitting.value) {
                _push2(`<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"${_scopeId}><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"${_scopeId}></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"${_scopeId}></path></svg>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(` ${ssrInterpolate(isSubmitting.value ? "新增中..." : "確認新建")}</button></div></form></div></div></div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="bg-white rounded-lg shadow-sm border border-gray-200"${_scopeId}><div class="overflow-x-auto"${_scopeId}><table class="w-full"${_scopeId}><thead${_scopeId}><tr class="border-b border-gray-200"${_scopeId}><th class="p-4 text-left text-sm font-medium text-gray-700"${_scopeId}>更新會名稱</th><th class="p-4 text-left text-sm font-medium text-gray-700"${_scopeId}>土地面積 (平方公尺)</th><th class="p-4 text-left text-sm font-medium text-gray-700"${_scopeId}>所有權人數</th><th class="p-4 text-left text-sm font-medium text-gray-700"${_scopeId}>理事長姓名</th><th class="p-4 text-left text-sm font-medium text-gray-700"${_scopeId}>理事長電話</th><th class="p-4 text-center text-sm font-medium text-gray-700"${_scopeId}>操作</th></tr></thead><tbody${_scopeId}>`);
            if (loading.value) {
              _push2(`<tr${_scopeId}><td colspan="6" class="p-8 text-center text-gray-500"${_scopeId}><div class="flex items-center justify-center"${_scopeId}><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"${_scopeId}><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"${_scopeId}></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"${_scopeId}></path></svg> 載入中... </div></td></tr>`);
            } else if (renewals.value.length === 0) {
              _push2(`<tr${_scopeId}><td colspan="6" class="p-8 text-center text-gray-500"${_scopeId}> 暫無資料，請點擊「新建更新會」新增資料 </td></tr>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<!--[-->`);
            ssrRenderList(renewals.value, (renewal, index) => {
              _push2(`<tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150"${_scopeId}><td class="p-4 text-sm text-gray-900"${_scopeId}>${ssrInterpolate(renewal.name)}</td><td class="p-4 text-sm text-gray-900 text-center"${_scopeId}>${ssrInterpolate(renewal.area)}</td><td class="p-4 text-sm text-gray-900 text-center"${_scopeId}>${ssrInterpolate(renewal.member_count)}</td><td class="p-4 text-sm text-gray-900"${_scopeId}>${ssrInterpolate(renewal.chairman_name)}</td><td class="p-4 text-sm text-gray-900"${_scopeId}>${ssrInterpolate(renewal.chairman_phone)}</td><td class="p-4 text-center"${_scopeId}><div class="flex justify-center gap-2 flex-wrap"${_scopeId}><button class="px-2 py-1 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded transition-colors duration-200"${_scopeId}> 基本資料 </button><button class="px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"${_scopeId}> 查詢會員 </button><button class="px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"${_scopeId}> 共舉資訊 </button><button class="px-2 py-1 text-xs font-medium text-white bg-red-500 hover:bg-red-600 rounded transition-colors duration-200"${_scopeId}> 刪除 </button></div></td></tr>`);
            });
            _push2(`<!--]--></tbody></table></div><div class="flex justify-between items-center p-4 border-t border-gray-200"${_scopeId}><div class="text-sm text-gray-500 flex items-center"${_scopeId}> 每頁顯示： <select class="ml-2 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"${_scopeId}><option value="10"${ssrIncludeBooleanAttr(Array.isArray(pageSize.value) ? ssrLooseContain(pageSize.value, "10") : ssrLooseEqual(pageSize.value, "10")) ? " selected" : ""}${_scopeId}>10</option><option value="20"${ssrIncludeBooleanAttr(Array.isArray(pageSize.value) ? ssrLooseContain(pageSize.value, "20") : ssrLooseEqual(pageSize.value, "20")) ? " selected" : ""}${_scopeId}>20</option><option value="50"${ssrIncludeBooleanAttr(Array.isArray(pageSize.value) ? ssrLooseContain(pageSize.value, "50") : ssrLooseEqual(pageSize.value, "50")) ? " selected" : ""}${_scopeId}>50</option></select></div><div class="text-sm text-gray-500"${_scopeId}>${ssrInterpolate(renewals.value.length > 0 ? `1-${renewals.value.length} 共 ${renewals.value.length}` : "0-0 共 0")}</div><div class="flex gap-1"${_scopeId}><button disabled class="p-2 text-gray-400 bg-gray-100 rounded cursor-not-allowed"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:chevron-left",
              class: "w-4 h-4"
            }, null, _parent2, _scopeId));
            _push2(`</button><button class="px-3 py-2 text-sm text-white bg-blue-500 rounded font-medium"${_scopeId}>1</button><button disabled class="p-2 text-gray-400 bg-gray-100 rounded cursor-not-allowed"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_Icon, {
              name: "heroicons:chevron-right",
              class: "w-4 h-4"
            }, null, _parent2, _scopeId));
            _push2(`</button></div></div></div></div>`);
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
                    createVNode("h2", { class: "text-2xl font-semibold" }, "更新會")
                  ])
                ]),
                createVNode("div", { class: "flex justify-end gap-4 mb-6" }, [
                  createVNode("button", {
                    onClick: allocateRenewal,
                    class: "inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
                  }, [
                    createVNode(_component_Icon, {
                      name: "heroicons:users",
                      class: "w-5 h-5 mr-2"
                    }),
                    createTextVNode(" 分配更新會 ")
                  ]),
                  createVNode("button", {
                    onClick: createRenewal,
                    class: "inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
                  }, [
                    createVNode(_component_Icon, {
                      name: "heroicons:plus",
                      class: "w-5 h-5 mr-2"
                    }),
                    createTextVNode(" 新建更新會 ")
                  ])
                ]),
                showCreateModal.value ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "fixed inset-0 z-50 overflow-y-auto",
                  "aria-labelledby": "modal-title",
                  role: "dialog",
                  "aria-modal": "true"
                }, [
                  createVNode("div", { class: "flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" }, [
                    createVNode("div", {
                      class: "fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity",
                      onClick: closeModal
                    }),
                    createVNode("div", { class: "inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" }, [
                      createVNode("div", { class: "border-b border-gray-200 pb-4 mb-6" }, [
                        createVNode("div", { class: "flex justify-between items-center" }, [
                          createVNode("h3", { class: "text-lg font-semibold text-gray-900" }, "新建更新會"),
                          createVNode("button", {
                            type: "button",
                            onClick: fillRandomTestData,
                            class: "px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
                          }, [
                            createVNode(_component_Icon, {
                              name: "heroicons:beaker",
                              class: "w-4 h-4 mr-1 inline"
                            }),
                            createTextVNode(" 填入測試資料 ")
                          ])
                        ])
                      ]),
                      createVNode("form", {
                        onSubmit: withModifiers(onSubmit, ["prevent"])
                      }, [
                        createVNode("div", { class: "space-y-6" }, [
                          createVNode("div", null, [
                            createVNode("label", {
                              for: "name",
                              class: "block text-sm font-medium text-gray-700 mb-2"
                            }, [
                              createTextVNode("更新會名稱 "),
                              createVNode("span", { class: "text-red-500" }, "*")
                            ]),
                            withDirectives(createVNode("input", {
                              id: "name",
                              "onUpdate:modelValue": ($event) => formData.name = $event,
                              type: "text",
                              placeholder: "請輸入更新會名稱",
                              class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                              required: ""
                            }, null, 8, ["onUpdate:modelValue"]), [
                              [vModelText, formData.name]
                            ])
                          ]),
                          createVNode("div", null, [
                            createVNode("label", {
                              for: "area",
                              class: "block text-sm font-medium text-gray-700 mb-2"
                            }, [
                              createTextVNode("土地面積(平方公尺) "),
                              createVNode("span", { class: "text-red-500" }, "*")
                            ]),
                            withDirectives(createVNode("input", {
                              id: "area",
                              "onUpdate:modelValue": ($event) => formData.area = $event,
                              type: "number",
                              placeholder: "請輸入土地面積",
                              class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                              required: ""
                            }, null, 8, ["onUpdate:modelValue"]), [
                              [vModelText, formData.area]
                            ])
                          ]),
                          createVNode("div", null, [
                            createVNode("label", {
                              for: "memberCount",
                              class: "block text-sm font-medium text-gray-700 mb-2"
                            }, [
                              createTextVNode("所有權人數 "),
                              createVNode("span", { class: "text-red-500" }, "*")
                            ]),
                            withDirectives(createVNode("input", {
                              id: "memberCount",
                              "onUpdate:modelValue": ($event) => formData.memberCount = $event,
                              type: "number",
                              placeholder: "請輸入所有權人數",
                              class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                              required: ""
                            }, null, 8, ["onUpdate:modelValue"]), [
                              [vModelText, formData.memberCount]
                            ])
                          ]),
                          createVNode("div", null, [
                            createVNode("label", {
                              for: "chairmanName",
                              class: "block text-sm font-medium text-gray-700 mb-2"
                            }, [
                              createTextVNode("理事長姓名 "),
                              createVNode("span", { class: "text-red-500" }, "*")
                            ]),
                            withDirectives(createVNode("input", {
                              id: "chairmanName",
                              "onUpdate:modelValue": ($event) => formData.chairmanName = $event,
                              type: "text",
                              placeholder: "請輸入理事長姓名",
                              class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                              required: ""
                            }, null, 8, ["onUpdate:modelValue"]), [
                              [vModelText, formData.chairmanName]
                            ])
                          ]),
                          createVNode("div", null, [
                            createVNode("label", {
                              for: "chairmanPhone",
                              class: "block text-sm font-medium text-gray-700 mb-2"
                            }, [
                              createTextVNode("理事長電話 "),
                              createVNode("span", { class: "text-red-500" }, "*")
                            ]),
                            withDirectives(createVNode("input", {
                              id: "chairmanPhone",
                              "onUpdate:modelValue": ($event) => formData.chairmanPhone = $event,
                              type: "tel",
                              placeholder: "請輸入理事長電話",
                              class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                              required: ""
                            }, null, 8, ["onUpdate:modelValue"]), [
                              [vModelText, formData.chairmanPhone]
                            ])
                          ])
                        ]),
                        error.value ? (openBlock(), createBlock("div", {
                          key: 0,
                          class: "mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"
                        }, toDisplayString(error.value), 1)) : createCommentVNode("", true),
                        createVNode("div", { class: "flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200" }, [
                          createVNode("button", {
                            type: "button",
                            onClick: closeModal,
                            disabled: isSubmitting.value,
                            class: "px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                          }, " 取消 ", 8, ["disabled"]),
                          createVNode("button", {
                            type: "submit",
                            disabled: isSubmitting.value,
                            class: "px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                          }, [
                            isSubmitting.value ? (openBlock(), createBlock("svg", {
                              key: 0,
                              class: "animate-spin -ml-1 mr-2 h-4 w-4 text-white",
                              xmlns: "http://www.w3.org/2000/svg",
                              fill: "none",
                              viewBox: "0 0 24 24"
                            }, [
                              createVNode("circle", {
                                class: "opacity-25",
                                cx: "12",
                                cy: "12",
                                r: "10",
                                stroke: "currentColor",
                                "stroke-width": "4"
                              }),
                              createVNode("path", {
                                class: "opacity-75",
                                fill: "currentColor",
                                d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                              })
                            ])) : createCommentVNode("", true),
                            createTextVNode(" " + toDisplayString(isSubmitting.value ? "新增中..." : "確認新建"), 1)
                          ], 8, ["disabled"])
                        ])
                      ], 32)
                    ])
                  ])
                ])) : createCommentVNode("", true),
                createVNode("div", { class: "bg-white rounded-lg shadow-sm border border-gray-200" }, [
                  createVNode("div", { class: "overflow-x-auto" }, [
                    createVNode("table", { class: "w-full" }, [
                      createVNode("thead", null, [
                        createVNode("tr", { class: "border-b border-gray-200" }, [
                          createVNode("th", { class: "p-4 text-left text-sm font-medium text-gray-700" }, "更新會名稱"),
                          createVNode("th", { class: "p-4 text-left text-sm font-medium text-gray-700" }, "土地面積 (平方公尺)"),
                          createVNode("th", { class: "p-4 text-left text-sm font-medium text-gray-700" }, "所有權人數"),
                          createVNode("th", { class: "p-4 text-left text-sm font-medium text-gray-700" }, "理事長姓名"),
                          createVNode("th", { class: "p-4 text-left text-sm font-medium text-gray-700" }, "理事長電話"),
                          createVNode("th", { class: "p-4 text-center text-sm font-medium text-gray-700" }, "操作")
                        ])
                      ]),
                      createVNode("tbody", null, [
                        loading.value ? (openBlock(), createBlock("tr", { key: 0 }, [
                          createVNode("td", {
                            colspan: "6",
                            class: "p-8 text-center text-gray-500"
                          }, [
                            createVNode("div", { class: "flex items-center justify-center" }, [
                              (openBlock(), createBlock("svg", {
                                class: "animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500",
                                xmlns: "http://www.w3.org/2000/svg",
                                fill: "none",
                                viewBox: "0 0 24 24"
                              }, [
                                createVNode("circle", {
                                  class: "opacity-25",
                                  cx: "12",
                                  cy: "12",
                                  r: "10",
                                  stroke: "currentColor",
                                  "stroke-width": "4"
                                }),
                                createVNode("path", {
                                  class: "opacity-75",
                                  fill: "currentColor",
                                  d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                })
                              ])),
                              createTextVNode(" 載入中... ")
                            ])
                          ])
                        ])) : renewals.value.length === 0 ? (openBlock(), createBlock("tr", { key: 1 }, [
                          createVNode("td", {
                            colspan: "6",
                            class: "p-8 text-center text-gray-500"
                          }, " 暫無資料，請點擊「新建更新會」新增資料 ")
                        ])) : createCommentVNode("", true),
                        (openBlock(true), createBlock(Fragment, null, renderList(renewals.value, (renewal, index) => {
                          return openBlock(), createBlock("tr", {
                            key: renewal.id || index,
                            class: "border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150"
                          }, [
                            createVNode("td", { class: "p-4 text-sm text-gray-900" }, toDisplayString(renewal.name), 1),
                            createVNode("td", { class: "p-4 text-sm text-gray-900 text-center" }, toDisplayString(renewal.area), 1),
                            createVNode("td", { class: "p-4 text-sm text-gray-900 text-center" }, toDisplayString(renewal.member_count), 1),
                            createVNode("td", { class: "p-4 text-sm text-gray-900" }, toDisplayString(renewal.chairman_name), 1),
                            createVNode("td", { class: "p-4 text-sm text-gray-900" }, toDisplayString(renewal.chairman_phone), 1),
                            createVNode("td", { class: "p-4 text-center" }, [
                              createVNode("div", { class: "flex justify-center gap-2 flex-wrap" }, [
                                createVNode("button", {
                                  onClick: ($event) => viewBasicInfo(renewal),
                                  class: "px-2 py-1 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded transition-colors duration-200"
                                }, " 基本資料 ", 8, ["onClick"]),
                                createVNode("button", {
                                  onClick: ($event) => viewMembers(renewal),
                                  class: "px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"
                                }, " 查詢會員 ", 8, ["onClick"]),
                                createVNode("button", {
                                  onClick: ($event) => viewJointInfo(renewal),
                                  class: "px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"
                                }, " 共舉資訊 ", 8, ["onClick"]),
                                createVNode("button", {
                                  onClick: ($event) => deleteRenewal(renewal),
                                  class: "px-2 py-1 text-xs font-medium text-white bg-red-500 hover:bg-red-600 rounded transition-colors duration-200"
                                }, " 刪除 ", 8, ["onClick"])
                              ])
                            ])
                          ]);
                        }), 128))
                      ])
                    ])
                  ]),
                  createVNode("div", { class: "flex justify-between items-center p-4 border-t border-gray-200" }, [
                    createVNode("div", { class: "text-sm text-gray-500 flex items-center" }, [
                      createTextVNode(" 每頁顯示： "),
                      withDirectives(createVNode("select", {
                        "onUpdate:modelValue": ($event) => pageSize.value = $event,
                        class: "ml-2 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      }, [
                        createVNode("option", { value: "10" }, "10"),
                        createVNode("option", { value: "20" }, "20"),
                        createVNode("option", { value: "50" }, "50")
                      ], 8, ["onUpdate:modelValue"]), [
                        [vModelSelect, pageSize.value]
                      ])
                    ]),
                    createVNode("div", { class: "text-sm text-gray-500" }, toDisplayString(renewals.value.length > 0 ? `1-${renewals.value.length} 共 ${renewals.value.length}` : "0-0 共 0"), 1),
                    createVNode("div", { class: "flex gap-1" }, [
                      createVNode("button", {
                        disabled: "",
                        class: "p-2 text-gray-400 bg-gray-100 rounded cursor-not-allowed"
                      }, [
                        createVNode(_component_Icon, {
                          name: "heroicons:chevron-left",
                          class: "w-4 h-4"
                        })
                      ]),
                      createVNode("button", { class: "px-3 py-2 text-sm text-white bg-blue-500 rounded font-medium" }, "1"),
                      createVNode("button", {
                        disabled: "",
                        class: "p-2 text-gray-400 bg-gray-100 rounded cursor-not-allowed"
                      }, [
                        createVNode(_component_Icon, {
                          name: "heroicons:chevron-right",
                          class: "w-4 h-4"
                        })
                      ])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/tables/urban-renewal.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=urban-renewal-Ce4txsZ5.js.map
