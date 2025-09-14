import { _ as __nuxt_component_0 } from "./nuxt-layout-D3Yn692_.js";
import { ref, reactive, computed, watch, mergeProps, withCtx, createVNode, createBlock, createCommentVNode, toDisplayString, openBlock, withDirectives, vModelText, createTextVNode, Fragment, renderList, vModelSelect, useSSRContext } from "vue";
import { ssrRenderComponent, ssrInterpolate, ssrRenderAttr, ssrIncludeBooleanAttr, ssrLooseContain, ssrLooseEqual, ssrRenderList } from "vue/server-renderer";
import { l as useRoute, j as useRouter, u as useNuxtApp, k as useRuntimeConfig } from "../server.mjs";
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
  __name: "basic-info",
  __ssrInlineRender: true,
  setup(__props) {
    const route = useRoute();
    const router = useRouter();
    const runtimeConfig = useRuntimeConfig();
    const { $swal } = useNuxtApp();
    const loading = ref(true);
    const isSaving = ref(false);
    const showEditModal = ref(false);
    const renewalData = reactive({
      id: null,
      name: "",
      area: "",
      member_count: "",
      chairman_name: "",
      chairman_phone: "",
      address: "",
      representative: ""
    });
    const landForm = reactive({
      county: "",
      district: "",
      section: "",
      landNumberMain: "",
      landNumberSub: "",
      landArea: ""
    });
    const landNumberErrors = reactive({
      main: "",
      sub: ""
    });
    const landPlots = ref([]);
    const editingLandPlot = ref({});
    const counties = ref([
      { code: "TPE", name: "臺北市" },
      { code: "KHH", name: "高雄市" },
      { code: "TPH", name: "新北市" },
      { code: "TCH", name: "臺中市" },
      { code: "TNN", name: "臺南市" },
      { code: "TYC", name: "桃園市" },
      { code: "HSC", name: "新竹市" },
      { code: "HSH", name: "新竹縣" },
      { code: "MIA", name: "苗栗縣" },
      { code: "CHA", name: "彰化縣" },
      { code: "NTO", name: "南投縣" },
      { code: "YUN", name: "雲林縣" },
      { code: "CYI", name: "嘉義市" },
      { code: "CYQ", name: "嘉義縣" },
      { code: "PIF", name: "屏東縣" },
      { code: "ILA", name: "宜蘭縣" },
      { code: "HUA", name: "花蓮縣" },
      { code: "TTT", name: "臺東縣" },
      { code: "PEN", name: "澎湖縣" },
      { code: "KIN", name: "金門縣" },
      { code: "LIE", name: "連江縣" }
    ]);
    const districts = ref([]);
    const sections = ref([]);
    const administrativeData = {
      "TPE": [
        { code: "ZS", name: "中山區" },
        { code: "DA", name: "大安區" },
        { code: "XY", name: "信義區" },
        { code: "SS", name: "松山區" },
        { code: "WH", name: "萬華區" },
        { code: "ZZ", name: "中正區" },
        { code: "DT", name: "大同區" },
        { code: "SL", name: "士林區" },
        { code: "BT", name: "北投區" },
        { code: "NH", name: "內湖區" },
        { code: "NG", name: "南港區" },
        { code: "WS", name: "文山區" }
      ],
      "KHH": [
        { code: "XZ", name: "新興區" },
        { code: "QJ", name: "前金區" },
        { code: "LY", name: "苓雅區" },
        { code: "YC", name: "鹽埕區" },
        { code: "GS", name: "鼓山區" },
        { code: "QZ", name: "前鎮區" },
        { code: "XL", name: "小港區" },
        { code: "ZS", name: "左營區" }
      ]
    };
    const sectionData = {
      "ZS": [
        { code: "001", name: "中山段" },
        { code: "002", name: "長安段" },
        { code: "003", name: "民權段" }
      ],
      "DA": [
        { code: "001", name: "大安段" },
        { code: "002", name: "忠孝段" },
        { code: "003", name: "信義段" }
      ]
    };
    const canAddLandPlot = computed(() => {
      return landForm.county && landForm.district && landForm.section && landForm.landNumberMain && !landNumberErrors.main && !landNumberErrors.sub;
    });
    const updatePageTitle = () => {
      (void 0).title = `${renewalData.name} - 更新會基本資料管理`;
    };
    const onCountyChange = () => {
      landForm.district = "";
      landForm.section = "";
      districts.value = administrativeData[landForm.county] || [];
      sections.value = [];
    };
    const onDistrictChange = () => {
      landForm.section = "";
      sections.value = sectionData[landForm.district] || [];
    };
    const validateLandNumber = (type) => {
      const value = type === "main" ? landForm.landNumberMain : landForm.landNumberSub;
      const pattern = /^\d{4}$/;
      if (value && !pattern.test(value)) {
        landNumberErrors[type] = "請輸入4位數字格式（例：0001）";
      } else {
        landNumberErrors[type] = "";
      }
    };
    const addLandPlot = () => {
      var _a, _b, _c;
      if (!canAddLandPlot.value) return;
      const countyName = (_a = counties.value.find((c) => c.code === landForm.county)) == null ? void 0 : _a.name;
      const districtName = (_b = districts.value.find((d) => d.code === landForm.district)) == null ? void 0 : _b.name;
      const sectionName = (_c = sections.value.find((s) => s.code === landForm.section)) == null ? void 0 : _c.name;
      const landNumber = landForm.landNumberSub ? `${landForm.landNumberMain}-${landForm.landNumberSub}` : landForm.landNumberMain;
      const fullLandNumber = `${countyName}${districtName}${sectionName}${landNumber}`;
      const newPlot = {
        id: Date.now(),
        // Temporary ID
        county: landForm.county,
        district: landForm.district,
        section: landForm.section,
        landNumberMain: landForm.landNumberMain,
        landNumberSub: landForm.landNumberSub,
        landNumber,
        fullLandNumber,
        landArea: landForm.landArea,
        isRepresentative: landPlots.value.length === 0
        // First plot is representative by default
      };
      landPlots.value.push(newPlot);
      Object.assign(landForm, {
        county: "",
        district: "",
        section: "",
        landNumberMain: "",
        landNumberSub: "",
        landArea: ""
      });
      districts.value = [];
      sections.value = [];
    };
    const setAsRepresentative = (plot) => {
      landPlots.value.forEach((p) => p.isRepresentative = false);
      plot.isRepresentative = true;
    };
    const editLandPlot = (plot) => {
      editingLandPlot.value = { ...plot };
      showEditModal.value = true;
    };
    const closeEditModal = () => {
      showEditModal.value = false;
      editingLandPlot.value = {};
    };
    const updateLandPlot = () => {
      const index = landPlots.value.findIndex((p) => p.id === editingLandPlot.value.id);
      if (index !== -1) {
        landPlots.value[index] = { ...editingLandPlot.value };
      }
      closeEditModal();
    };
    const deleteLandPlot = async (plot) => {
      const result = await $swal.fire({
        title: "確認刪除",
        text: `確定要刪除地號「${plot.fullLandNumber}」嗎？`,
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
      const index = landPlots.value.findIndex((p) => p.id === plot.id);
      if (index !== -1) {
        const wasRepresentative = plot.isRepresentative;
        landPlots.value.splice(index, 1);
        if (wasRepresentative && landPlots.value.length > 0) {
          landPlots.value[0].isRepresentative = true;
        }
      }
    };
    const saveChanges = async () => {
      isSaving.value = true;
      try {
        const response = await $fetch(`/api/urban-renewals/${route.params.id}`, {
          method: "PUT",
          baseURL: runtimeConfig.public.apiBaseUrl,
          headers: {
            "Content-Type": "application/json"
          },
          body: {
            name: renewalData.name,
            area: parseFloat(renewalData.area),
            member_count: parseInt(renewalData.member_count),
            chairman_name: renewalData.chairman_name,
            chairman_phone: renewalData.chairman_phone,
            address: renewalData.address,
            representative: renewalData.representative
          }
        });
        if (response.status === "success") {
          await $swal.fire({
            title: "儲存成功！",
            text: "資料已成功更新",
            icon: "success",
            confirmButtonText: "確定",
            confirmButtonColor: "#10b981"
          });
        } else {
          throw new Error(response.message || "儲存失敗");
        }
      } catch (err) {
        console.error("Save error:", err);
        await $swal.fire({
          title: "儲存失敗",
          text: err.message || "儲存失敗，請稍後再試",
          icon: "error",
          confirmButtonText: "確定",
          confirmButtonColor: "#ef4444"
        });
      } finally {
        isSaving.value = false;
      }
    };
    const goBack = () => {
      router.push("/tables/urban-renewal");
    };
    watch(() => renewalData.name, updatePageTitle);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLayout = __nuxt_component_0;
      _push(ssrRenderComponent(_component_NuxtLayout, mergeProps({ name: "main" }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`更新會基本資料管理`);
          } else {
            return [
              createTextVNode("更新會基本資料管理")
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="p-8"${_scopeId}><div class="mb-6"${_scopeId}><h1 class="text-2xl font-bold text-gray-900"${_scopeId}>${ssrInterpolate(renewalData.name || "載入中...")}</h1></div>`);
            if (loading.value) {
              _push2(`<div class="flex items-center justify-center py-12"${_scopeId}><div class="flex items-center"${_scopeId}><svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"${_scopeId}><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"${_scopeId}></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"${_scopeId}></path></svg><span class="text-lg text-gray-600"${_scopeId}>載入資料中...</span></div></div>`);
            } else {
              _push2(`<div class="space-y-8"${_scopeId}><div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"${_scopeId}><h2 class="text-lg font-semibold text-gray-900 mb-4"${_scopeId}>基本資訊</h2><div class="grid grid-cols-1 md:grid-cols-2 gap-6"${_scopeId}><div${_scopeId}><label for="name" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>更新會名稱</label><input id="name"${ssrRenderAttr("value", renewalData.name)} type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="請輸入更新會名稱"${_scopeId}></div><div${_scopeId}><label for="area" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>土地面積(平方公尺)</label><input id="area"${ssrRenderAttr("value", renewalData.area)} type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="請輸入土地面積"${_scopeId}></div><div${_scopeId}><label for="memberCount" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>所有權人數</label><input id="memberCount"${ssrRenderAttr("value", renewalData.member_count)} type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="請輸入所有權人數"${_scopeId}></div></div></div><div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"${_scopeId}><h2 class="text-lg font-semibold text-gray-900 mb-4"${_scopeId}>地號管理</h2><div class="bg-gray-50 rounded-lg p-6 shadow-sm mb-6"${_scopeId}><h3 class="text-md font-medium text-gray-900 mb-4"${_scopeId}>新增地號</h3><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"${_scopeId}><div${_scopeId}><label for="county" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>縣市 <span class="text-red-500"${_scopeId}>*</span></label><select id="county" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required${_scopeId}><option value=""${ssrIncludeBooleanAttr(Array.isArray(landForm.county) ? ssrLooseContain(landForm.county, "") : ssrLooseEqual(landForm.county, "")) ? " selected" : ""}${_scopeId}>請選擇縣市</option><!--[-->`);
              ssrRenderList(counties.value, (county) => {
                _push2(`<option${ssrRenderAttr("value", county.code)}${ssrIncludeBooleanAttr(Array.isArray(landForm.county) ? ssrLooseContain(landForm.county, county.code) : ssrLooseEqual(landForm.county, county.code)) ? " selected" : ""}${_scopeId}>${ssrInterpolate(county.name)}</option>`);
              });
              _push2(`<!--]--></select></div><div${_scopeId}><label for="district" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>行政區 <span class="text-red-500"${_scopeId}>*</span></label><select id="district"${ssrIncludeBooleanAttr(!landForm.county) ? " disabled" : ""} class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed" required${_scopeId}><option value=""${ssrIncludeBooleanAttr(Array.isArray(landForm.district) ? ssrLooseContain(landForm.district, "") : ssrLooseEqual(landForm.district, "")) ? " selected" : ""}${_scopeId}>請選擇行政區</option><!--[-->`);
              ssrRenderList(districts.value, (district) => {
                _push2(`<option${ssrRenderAttr("value", district.code)}${ssrIncludeBooleanAttr(Array.isArray(landForm.district) ? ssrLooseContain(landForm.district, district.code) : ssrLooseEqual(landForm.district, district.code)) ? " selected" : ""}${_scopeId}>${ssrInterpolate(district.name)}</option>`);
              });
              _push2(`<!--]--></select></div><div${_scopeId}><label for="section" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>段小段 <span class="text-red-500"${_scopeId}>*</span></label><select id="section"${ssrIncludeBooleanAttr(!landForm.district) ? " disabled" : ""} class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed" required${_scopeId}><option value=""${ssrIncludeBooleanAttr(Array.isArray(landForm.section) ? ssrLooseContain(landForm.section, "") : ssrLooseEqual(landForm.section, "")) ? " selected" : ""}${_scopeId}>請選擇段小段</option><!--[-->`);
              ssrRenderList(sections.value, (section) => {
                _push2(`<option${ssrRenderAttr("value", section.code)}${ssrIncludeBooleanAttr(Array.isArray(landForm.section) ? ssrLooseContain(landForm.section, section.code) : ssrLooseEqual(landForm.section, section.code)) ? " selected" : ""}${_scopeId}>${ssrInterpolate(section.name)}</option>`);
              });
              _push2(`<!--]--></select></div><div${_scopeId}><label for="landNumberMain" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>地號母號 <span class="text-red-500"${_scopeId}>*</span></label><input id="landNumberMain"${ssrRenderAttr("value", landForm.landNumberMain)} type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="例: 0001" maxlength="4" required${_scopeId}>`);
              if (landNumberErrors.main) {
                _push2(`<p class="mt-1 text-sm text-red-600"${_scopeId}>${ssrInterpolate(landNumberErrors.main)}</p>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div${_scopeId}><label for="landNumberSub" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>地號子號</label><input id="landNumberSub"${ssrRenderAttr("value", landForm.landNumberSub)} type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="例: 0000" maxlength="4"${_scopeId}>`);
              if (landNumberErrors.sub) {
                _push2(`<p class="mt-1 text-sm text-red-600"${_scopeId}>${ssrInterpolate(landNumberErrors.sub)}</p>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div${_scopeId}><label for="landArea" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>土地總面積(平方公尺)</label><input id="landArea"${ssrRenderAttr("value", landForm.landArea)} type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="請輸入土地面積"${_scopeId}></div></div><div class="flex justify-end mt-4"${_scopeId}><button${ssrIncludeBooleanAttr(!canAddLandPlot.value) ? " disabled" : ""} class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"${_scopeId}> 新增地號 </button></div></div><div class="bg-white rounded-lg border border-gray-200"${_scopeId}><div class="overflow-x-auto"${_scopeId}><table class="w-full"${_scopeId}><thead${_scopeId}><tr class="border-b border-gray-200 bg-gray-50"${_scopeId}><th class="p-4 text-left text-sm font-medium text-gray-700"${_scopeId}>地號</th><th class="p-4 text-left text-sm font-medium text-gray-700"${_scopeId}>土地總面積(平方公尺)</th><th class="p-4 text-center text-sm font-medium text-gray-700"${_scopeId}>操作</th></tr></thead><tbody${_scopeId}>`);
              if (landPlots.value.length === 0) {
                _push2(`<tr${_scopeId}><td colspan="3" class="p-8 text-center text-gray-500"${_scopeId}> 暫無地號資料，請新增地號 </td></tr>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<!--[-->`);
              ssrRenderList(landPlots.value, (plot, index) => {
                _push2(`<tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150"${_scopeId}><td class="p-4 text-sm text-gray-900"${_scopeId}><div class="flex items-center"${_scopeId}><span${_scopeId}>${ssrInterpolate(plot.fullLandNumber)}</span>`);
                if (plot.isRepresentative) {
                  _push2(`<span class="ml-2 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"${_scopeId}> 代表號 </span>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div></td><td class="p-4 text-sm text-gray-900"${_scopeId}>${ssrInterpolate(plot.landArea || "未設定")}</td><td class="p-4 text-center"${_scopeId}><div class="flex justify-center gap-2 flex-wrap"${_scopeId}>`);
                if (!plot.isRepresentative) {
                  _push2(`<button class="px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"${_scopeId}> 設為代表號 </button>`);
                } else {
                  _push2(`<span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded"${_scopeId}> 代表號 </span>`);
                }
                _push2(`<button class="px-2 py-1 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded transition-colors duration-200"${_scopeId}> 編輯 </button><button class="px-2 py-1 text-xs font-medium text-white bg-red-500 hover:bg-red-600 rounded transition-colors duration-200"${_scopeId}> 刪除 </button></div></td></tr>`);
              });
              _push2(`<!--]--></tbody></table></div></div></div><div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"${_scopeId}><h2 class="text-lg font-semibold text-gray-900 mb-4"${_scopeId}>其他資訊</h2><div class="grid grid-cols-1 md:grid-cols-2 gap-6"${_scopeId}><div${_scopeId}><label for="chairmanName" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>理事長姓名</label><input id="chairmanName"${ssrRenderAttr("value", renewalData.chairman_name)} type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="請輸入理事長姓名"${_scopeId}></div><div${_scopeId}><label for="chairmanPhone" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>理事長電話</label><input id="chairmanPhone"${ssrRenderAttr("value", renewalData.chairman_phone)} type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="請輸入理事長電話"${_scopeId}></div><div${_scopeId}><label for="address" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>設立地址</label><input id="address"${ssrRenderAttr("value", renewalData.address)} type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="請輸入設立地址"${_scopeId}></div><div${_scopeId}><label for="representative" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>負責人</label><input id="representative"${ssrRenderAttr("value", renewalData.representative)} type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="請輸入負責人"${_scopeId}></div></div></div><div class="flex justify-end gap-4"${_scopeId}><button class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-300 border border-transparent rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"${_scopeId}> 回上一頁 </button><button${ssrIncludeBooleanAttr(isSaving.value) ? " disabled" : ""} class="px-6 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"${_scopeId}>`);
              if (isSaving.value) {
                _push2(`<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"${_scopeId}><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"${_scopeId}></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"${_scopeId}></path></svg>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(` ${ssrInterpolate(isSaving.value ? "儲存中..." : "儲存")}</button></div></div>`);
            }
            if (showEditModal.value) {
              _push2(`<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"${_scopeId}><div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"${_scopeId}><div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"${_scopeId}></div><div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"${_scopeId}><div class="border-b border-gray-200 pb-4 mb-6"${_scopeId}><h3 class="text-lg font-semibold text-gray-900"${_scopeId}>編輯地號</h3></div><div class="space-y-4"${_scopeId}><div${_scopeId}><label for="editLandArea" class="block text-sm font-medium text-gray-700 mb-2"${_scopeId}>土地總面積(平方公尺)</label><input id="editLandArea"${ssrRenderAttr("value", editingLandPlot.value.landArea)} type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="請輸入土地面積"${_scopeId}></div></div><div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200"${_scopeId}><button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"${_scopeId}> 取消 </button><button class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"${_scopeId}> 確認更新 </button></div></div></div></div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "p-8" }, [
                createVNode("div", { class: "mb-6" }, [
                  createVNode("h1", { class: "text-2xl font-bold text-gray-900" }, toDisplayString(renewalData.name || "載入中..."), 1)
                ]),
                loading.value ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "flex items-center justify-center py-12"
                }, [
                  createVNode("div", { class: "flex items-center" }, [
                    (openBlock(), createBlock("svg", {
                      class: "animate-spin -ml-1 mr-3 h-8 w-8 text-green-500",
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
                    createVNode("span", { class: "text-lg text-gray-600" }, "載入資料中...")
                  ])
                ])) : (openBlock(), createBlock("div", {
                  key: 1,
                  class: "space-y-8"
                }, [
                  createVNode("div", { class: "bg-white rounded-lg shadow-sm border border-gray-200 p-6" }, [
                    createVNode("h2", { class: "text-lg font-semibold text-gray-900 mb-4" }, "基本資訊"),
                    createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                      createVNode("div", null, [
                        createVNode("label", {
                          for: "name",
                          class: "block text-sm font-medium text-gray-700 mb-2"
                        }, "更新會名稱"),
                        withDirectives(createVNode("input", {
                          id: "name",
                          "onUpdate:modelValue": ($event) => renewalData.name = $event,
                          type: "text",
                          onInput: updatePageTitle,
                          class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                          placeholder: "請輸入更新會名稱"
                        }, null, 40, ["onUpdate:modelValue"]), [
                          [vModelText, renewalData.name]
                        ])
                      ]),
                      createVNode("div", null, [
                        createVNode("label", {
                          for: "area",
                          class: "block text-sm font-medium text-gray-700 mb-2"
                        }, "土地面積(平方公尺)"),
                        withDirectives(createVNode("input", {
                          id: "area",
                          "onUpdate:modelValue": ($event) => renewalData.area = $event,
                          type: "number",
                          class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                          placeholder: "請輸入土地面積"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelText, renewalData.area]
                        ])
                      ]),
                      createVNode("div", null, [
                        createVNode("label", {
                          for: "memberCount",
                          class: "block text-sm font-medium text-gray-700 mb-2"
                        }, "所有權人數"),
                        withDirectives(createVNode("input", {
                          id: "memberCount",
                          "onUpdate:modelValue": ($event) => renewalData.member_count = $event,
                          type: "number",
                          class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                          placeholder: "請輸入所有權人數"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelText, renewalData.member_count]
                        ])
                      ])
                    ])
                  ]),
                  createVNode("div", { class: "bg-white rounded-lg shadow-sm border border-gray-200 p-6" }, [
                    createVNode("h2", { class: "text-lg font-semibold text-gray-900 mb-4" }, "地號管理"),
                    createVNode("div", { class: "bg-gray-50 rounded-lg p-6 shadow-sm mb-6" }, [
                      createVNode("h3", { class: "text-md font-medium text-gray-900 mb-4" }, "新增地號"),
                      createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" }, [
                        createVNode("div", null, [
                          createVNode("label", {
                            for: "county",
                            class: "block text-sm font-medium text-gray-700 mb-2"
                          }, [
                            createTextVNode("縣市 "),
                            createVNode("span", { class: "text-red-500" }, "*")
                          ]),
                          withDirectives(createVNode("select", {
                            id: "county",
                            "onUpdate:modelValue": ($event) => landForm.county = $event,
                            onChange: onCountyChange,
                            class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                            required: ""
                          }, [
                            createVNode("option", { value: "" }, "請選擇縣市"),
                            (openBlock(true), createBlock(Fragment, null, renderList(counties.value, (county) => {
                              return openBlock(), createBlock("option", {
                                key: county.code,
                                value: county.code
                              }, toDisplayString(county.name), 9, ["value"]);
                            }), 128))
                          ], 40, ["onUpdate:modelValue"]), [
                            [vModelSelect, landForm.county]
                          ])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", {
                            for: "district",
                            class: "block text-sm font-medium text-gray-700 mb-2"
                          }, [
                            createTextVNode("行政區 "),
                            createVNode("span", { class: "text-red-500" }, "*")
                          ]),
                          withDirectives(createVNode("select", {
                            id: "district",
                            "onUpdate:modelValue": ($event) => landForm.district = $event,
                            onChange: onDistrictChange,
                            disabled: !landForm.county,
                            class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed",
                            required: ""
                          }, [
                            createVNode("option", { value: "" }, "請選擇行政區"),
                            (openBlock(true), createBlock(Fragment, null, renderList(districts.value, (district) => {
                              return openBlock(), createBlock("option", {
                                key: district.code,
                                value: district.code
                              }, toDisplayString(district.name), 9, ["value"]);
                            }), 128))
                          ], 40, ["onUpdate:modelValue", "disabled"]), [
                            [vModelSelect, landForm.district]
                          ])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", {
                            for: "section",
                            class: "block text-sm font-medium text-gray-700 mb-2"
                          }, [
                            createTextVNode("段小段 "),
                            createVNode("span", { class: "text-red-500" }, "*")
                          ]),
                          withDirectives(createVNode("select", {
                            id: "section",
                            "onUpdate:modelValue": ($event) => landForm.section = $event,
                            disabled: !landForm.district,
                            class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed",
                            required: ""
                          }, [
                            createVNode("option", { value: "" }, "請選擇段小段"),
                            (openBlock(true), createBlock(Fragment, null, renderList(sections.value, (section) => {
                              return openBlock(), createBlock("option", {
                                key: section.code,
                                value: section.code
                              }, toDisplayString(section.name), 9, ["value"]);
                            }), 128))
                          ], 8, ["onUpdate:modelValue", "disabled"]), [
                            [vModelSelect, landForm.section]
                          ])
                        ]),
                        createVNode("div", null, [
                          createVNode("label", {
                            for: "landNumberMain",
                            class: "block text-sm font-medium text-gray-700 mb-2"
                          }, [
                            createTextVNode("地號母號 "),
                            createVNode("span", { class: "text-red-500" }, "*")
                          ]),
                          withDirectives(createVNode("input", {
                            id: "landNumberMain",
                            "onUpdate:modelValue": ($event) => landForm.landNumberMain = $event,
                            type: "text",
                            onInput: ($event) => validateLandNumber("main"),
                            class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                            placeholder: "例: 0001",
                            maxlength: "4",
                            required: ""
                          }, null, 40, ["onUpdate:modelValue", "onInput"]), [
                            [vModelText, landForm.landNumberMain]
                          ]),
                          landNumberErrors.main ? (openBlock(), createBlock("p", {
                            key: 0,
                            class: "mt-1 text-sm text-red-600"
                          }, toDisplayString(landNumberErrors.main), 1)) : createCommentVNode("", true)
                        ]),
                        createVNode("div", null, [
                          createVNode("label", {
                            for: "landNumberSub",
                            class: "block text-sm font-medium text-gray-700 mb-2"
                          }, "地號子號"),
                          withDirectives(createVNode("input", {
                            id: "landNumberSub",
                            "onUpdate:modelValue": ($event) => landForm.landNumberSub = $event,
                            type: "text",
                            onInput: ($event) => validateLandNumber("sub"),
                            class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                            placeholder: "例: 0000",
                            maxlength: "4"
                          }, null, 40, ["onUpdate:modelValue", "onInput"]), [
                            [vModelText, landForm.landNumberSub]
                          ]),
                          landNumberErrors.sub ? (openBlock(), createBlock("p", {
                            key: 0,
                            class: "mt-1 text-sm text-red-600"
                          }, toDisplayString(landNumberErrors.sub), 1)) : createCommentVNode("", true)
                        ]),
                        createVNode("div", null, [
                          createVNode("label", {
                            for: "landArea",
                            class: "block text-sm font-medium text-gray-700 mb-2"
                          }, "土地總面積(平方公尺)"),
                          withDirectives(createVNode("input", {
                            id: "landArea",
                            "onUpdate:modelValue": ($event) => landForm.landArea = $event,
                            type: "number",
                            step: "0.01",
                            class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                            placeholder: "請輸入土地面積"
                          }, null, 8, ["onUpdate:modelValue"]), [
                            [vModelText, landForm.landArea]
                          ])
                        ])
                      ]),
                      createVNode("div", { class: "flex justify-end mt-4" }, [
                        createVNode("button", {
                          onClick: addLandPlot,
                          disabled: !canAddLandPlot.value,
                          class: "px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        }, " 新增地號 ", 8, ["disabled"])
                      ])
                    ]),
                    createVNode("div", { class: "bg-white rounded-lg border border-gray-200" }, [
                      createVNode("div", { class: "overflow-x-auto" }, [
                        createVNode("table", { class: "w-full" }, [
                          createVNode("thead", null, [
                            createVNode("tr", { class: "border-b border-gray-200 bg-gray-50" }, [
                              createVNode("th", { class: "p-4 text-left text-sm font-medium text-gray-700" }, "地號"),
                              createVNode("th", { class: "p-4 text-left text-sm font-medium text-gray-700" }, "土地總面積(平方公尺)"),
                              createVNode("th", { class: "p-4 text-center text-sm font-medium text-gray-700" }, "操作")
                            ])
                          ]),
                          createVNode("tbody", null, [
                            landPlots.value.length === 0 ? (openBlock(), createBlock("tr", { key: 0 }, [
                              createVNode("td", {
                                colspan: "3",
                                class: "p-8 text-center text-gray-500"
                              }, " 暫無地號資料，請新增地號 ")
                            ])) : createCommentVNode("", true),
                            (openBlock(true), createBlock(Fragment, null, renderList(landPlots.value, (plot, index) => {
                              return openBlock(), createBlock("tr", {
                                key: plot.id || index,
                                class: "border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150"
                              }, [
                                createVNode("td", { class: "p-4 text-sm text-gray-900" }, [
                                  createVNode("div", { class: "flex items-center" }, [
                                    createVNode("span", null, toDisplayString(plot.fullLandNumber), 1),
                                    plot.isRepresentative ? (openBlock(), createBlock("span", {
                                      key: 0,
                                      class: "ml-2 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                                    }, " 代表號 ")) : createCommentVNode("", true)
                                  ])
                                ]),
                                createVNode("td", { class: "p-4 text-sm text-gray-900" }, toDisplayString(plot.landArea || "未設定"), 1),
                                createVNode("td", { class: "p-4 text-center" }, [
                                  createVNode("div", { class: "flex justify-center gap-2 flex-wrap" }, [
                                    !plot.isRepresentative ? (openBlock(), createBlock("button", {
                                      key: 0,
                                      onClick: ($event) => setAsRepresentative(plot),
                                      class: "px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"
                                    }, " 設為代表號 ", 8, ["onClick"])) : (openBlock(), createBlock("span", {
                                      key: 1,
                                      class: "px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded"
                                    }, " 代表號 ")),
                                    createVNode("button", {
                                      onClick: ($event) => editLandPlot(plot),
                                      class: "px-2 py-1 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded transition-colors duration-200"
                                    }, " 編輯 ", 8, ["onClick"]),
                                    createVNode("button", {
                                      onClick: ($event) => deleteLandPlot(plot),
                                      class: "px-2 py-1 text-xs font-medium text-white bg-red-500 hover:bg-red-600 rounded transition-colors duration-200"
                                    }, " 刪除 ", 8, ["onClick"])
                                  ])
                                ])
                              ]);
                            }), 128))
                          ])
                        ])
                      ])
                    ])
                  ]),
                  createVNode("div", { class: "bg-white rounded-lg shadow-sm border border-gray-200 p-6" }, [
                    createVNode("h2", { class: "text-lg font-semibold text-gray-900 mb-4" }, "其他資訊"),
                    createVNode("div", { class: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
                      createVNode("div", null, [
                        createVNode("label", {
                          for: "chairmanName",
                          class: "block text-sm font-medium text-gray-700 mb-2"
                        }, "理事長姓名"),
                        withDirectives(createVNode("input", {
                          id: "chairmanName",
                          "onUpdate:modelValue": ($event) => renewalData.chairman_name = $event,
                          type: "text",
                          class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                          placeholder: "請輸入理事長姓名"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelText, renewalData.chairman_name]
                        ])
                      ]),
                      createVNode("div", null, [
                        createVNode("label", {
                          for: "chairmanPhone",
                          class: "block text-sm font-medium text-gray-700 mb-2"
                        }, "理事長電話"),
                        withDirectives(createVNode("input", {
                          id: "chairmanPhone",
                          "onUpdate:modelValue": ($event) => renewalData.chairman_phone = $event,
                          type: "tel",
                          class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                          placeholder: "請輸入理事長電話"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelText, renewalData.chairman_phone]
                        ])
                      ]),
                      createVNode("div", null, [
                        createVNode("label", {
                          for: "address",
                          class: "block text-sm font-medium text-gray-700 mb-2"
                        }, "設立地址"),
                        withDirectives(createVNode("input", {
                          id: "address",
                          "onUpdate:modelValue": ($event) => renewalData.address = $event,
                          type: "text",
                          class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                          placeholder: "請輸入設立地址"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelText, renewalData.address]
                        ])
                      ]),
                      createVNode("div", null, [
                        createVNode("label", {
                          for: "representative",
                          class: "block text-sm font-medium text-gray-700 mb-2"
                        }, "負責人"),
                        withDirectives(createVNode("input", {
                          id: "representative",
                          "onUpdate:modelValue": ($event) => renewalData.representative = $event,
                          type: "text",
                          class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                          placeholder: "請輸入負責人"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelText, renewalData.representative]
                        ])
                      ])
                    ])
                  ]),
                  createVNode("div", { class: "flex justify-end gap-4" }, [
                    createVNode("button", {
                      onClick: goBack,
                      class: "px-6 py-2 text-sm font-medium text-gray-700 bg-gray-300 border border-transparent rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
                    }, " 回上一頁 "),
                    createVNode("button", {
                      onClick: saveChanges,
                      disabled: isSaving.value,
                      class: "px-6 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                    }, [
                      isSaving.value ? (openBlock(), createBlock("svg", {
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
                      createTextVNode(" " + toDisplayString(isSaving.value ? "儲存中..." : "儲存"), 1)
                    ], 8, ["disabled"])
                  ])
                ])),
                showEditModal.value ? (openBlock(), createBlock("div", {
                  key: 2,
                  class: "fixed inset-0 z-50 overflow-y-auto",
                  "aria-labelledby": "modal-title",
                  role: "dialog",
                  "aria-modal": "true"
                }, [
                  createVNode("div", { class: "flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" }, [
                    createVNode("div", {
                      class: "fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity",
                      onClick: closeEditModal
                    }),
                    createVNode("div", { class: "inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6" }, [
                      createVNode("div", { class: "border-b border-gray-200 pb-4 mb-6" }, [
                        createVNode("h3", { class: "text-lg font-semibold text-gray-900" }, "編輯地號")
                      ]),
                      createVNode("div", { class: "space-y-4" }, [
                        createVNode("div", null, [
                          createVNode("label", {
                            for: "editLandArea",
                            class: "block text-sm font-medium text-gray-700 mb-2"
                          }, "土地總面積(平方公尺)"),
                          withDirectives(createVNode("input", {
                            id: "editLandArea",
                            "onUpdate:modelValue": ($event) => editingLandPlot.value.landArea = $event,
                            type: "number",
                            step: "0.01",
                            class: "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500",
                            placeholder: "請輸入土地面積"
                          }, null, 8, ["onUpdate:modelValue"]), [
                            [vModelText, editingLandPlot.value.landArea]
                          ])
                        ])
                      ]),
                      createVNode("div", { class: "flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200" }, [
                        createVNode("button", {
                          type: "button",
                          onClick: closeEditModal,
                          class: "px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
                        }, " 取消 "),
                        createVNode("button", {
                          onClick: updateLandPlot,
                          class: "px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                        }, " 確認更新 ")
                      ])
                    ])
                  ])
                ])) : createCommentVNode("", true)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/tables/urban-renewal/[id]/basic-info.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=basic-info-BgoaMgE8.js.map
