import { a as _export_sfc, f as __nuxt_component_0, m as mergeConfig, d as useUI, t as twMerge, e as appConfig, g as useInjectButtonGroup, h as get, s as select, i as selectMenu } from "../server.mjs";
import * as e$1 from "vue";
import { defineComponent, mergeProps, createVNode, resolveDynamicComponent, toRef, computed, ref, watch, useSSRContext, onUnmounted, watchEffect, onMounted, cloneVNode, h as h$1, Fragment, inject, provide, nextTick, toRaw, reactive, resolveComponent, withCtx, renderSlot, createBlock, createCommentVNode, openBlock, toDisplayString, renderList, createTextVNode, Transition, useId } from "vue";
import { twJoin } from "tailwind-merge";
import { ssrRenderAttrs, ssrRenderVNode, ssrRenderClass, ssrInterpolate, ssrRenderComponent, ssrRenderSlot, ssrRenderAttr, ssrIncludeBooleanAttr, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
import { useVirtualizer } from "@tanstack/vue-virtual";
import { computedAsync, useDebounceFn } from "@vueuse/core";
import { defu } from "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/defu/dist/defu.mjs";
import { isEqual } from "ohash/utils";
import { u as useFormGroup } from "./useFormGroup-B3564yef.js";
const avatar = {
  wrapper: "relative inline-flex items-center justify-center flex-shrink-0",
  background: "bg-gray-100 dark:bg-gray-800",
  rounded: "rounded-full",
  text: "font-medium leading-none text-gray-900 dark:text-white truncate",
  placeholder: "font-medium leading-none text-gray-500 dark:text-gray-400 truncate",
  size: {
    "3xs": "h-4 w-4 text-[8px]",
    "2xs": "h-5 w-5 text-[10px]",
    "xs": "h-6 w-6 text-xs",
    "sm": "h-8 w-8 text-sm",
    "md": "h-10 w-10 text-base",
    "lg": "h-12 w-12 text-lg",
    "xl": "h-14 w-14 text-xl",
    "2xl": "h-16 w-16 text-2xl",
    "3xl": "h-20 w-20 text-3xl"
  },
  chip: {
    base: "absolute rounded-full ring-1 ring-white dark:ring-gray-900 flex items-center justify-center text-white dark:text-gray-900 font-medium",
    background: "bg-{color}-500 dark:bg-{color}-400",
    position: {
      "top-right": "top-0 right-0",
      "bottom-right": "bottom-0 right-0",
      "top-left": "top-0 left-0",
      "bottom-left": "bottom-0 left-0"
    },
    size: {
      "3xs": "h-[4px] min-w-[4px] text-[4px] p-px",
      "2xs": "h-[5px] min-w-[5px] text-[5px] p-px",
      "xs": "h-1.5 min-w-[0.375rem] text-[6px] p-px",
      "sm": "h-2 min-w-[0.5rem] text-[7px] p-0.5",
      "md": "h-2.5 min-w-[0.625rem] text-[8px] p-0.5",
      "lg": "h-3 min-w-[0.75rem] text-[10px] p-0.5",
      "xl": "h-3.5 min-w-[0.875rem] text-[11px] p-1",
      "2xl": "h-4 min-w-[1rem] text-[12px] p-1",
      "3xl": "h-5 min-w-[1.25rem] text-[14px] p-1"
    }
  },
  icon: {
    base: "text-gray-500 dark:text-gray-400 flex-shrink-0",
    size: {
      "3xs": "h-2 w-2",
      "2xs": "h-2.5 w-2.5",
      "xs": "h-3 w-3",
      "sm": "h-4 w-4",
      "md": "h-5 w-5",
      "lg": "h-6 w-6",
      "xl": "h-7 w-7",
      "2xl": "h-8 w-8",
      "3xl": "h-10 w-10"
    }
  },
  default: {
    size: "sm",
    icon: null,
    chipColor: null,
    chipPosition: "top-right"
  }
};
const config$1 = mergeConfig(appConfig.ui.strategy, appConfig.ui.avatar, avatar);
const _sfc_main$1 = defineComponent({
  components: {
    UIcon: __nuxt_component_0
  },
  inheritAttrs: false,
  props: {
    as: {
      type: [String, Object],
      default: "img"
    },
    src: {
      type: [String, Boolean],
      default: null
    },
    alt: {
      type: String,
      default: null
    },
    text: {
      type: String,
      default: null
    },
    icon: {
      type: String,
      default: () => config$1.default.icon
    },
    size: {
      type: String,
      default: () => config$1.default.size,
      validator(value) {
        return Object.keys(config$1.size).includes(value);
      }
    },
    chipColor: {
      type: String,
      default: () => config$1.default.chipColor,
      validator(value) {
        return ["gray", ...appConfig.ui.colors].includes(value);
      }
    },
    chipPosition: {
      type: String,
      default: () => config$1.default.chipPosition,
      validator(value) {
        return Object.keys(config$1.chip.position).includes(value);
      }
    },
    chipText: {
      type: [String, Number],
      default: null
    },
    imgClass: {
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
  setup(props) {
    const { ui, attrs } = useUI("avatar", toRef(props, "ui"), config$1);
    const url = computed(() => {
      if (typeof props.src === "boolean") {
        return null;
      }
      return props.src;
    });
    const placeholder = computed(() => {
      return (props.alt || "").split(" ").map((word) => word.charAt(0)).join("").substring(0, 2);
    });
    const wrapperClass = computed(() => {
      return twMerge(twJoin(
        ui.value.wrapper,
        (error.value || !url.value) && ui.value.background,
        ui.value.rounded,
        ui.value.size[props.size]
      ), props.class);
    });
    const imgClass = computed(() => {
      return twMerge(twJoin(
        ui.value.rounded,
        ui.value.size[props.size]
      ), props.imgClass);
    });
    const iconClass = computed(() => {
      return twJoin(
        ui.value.icon.base,
        ui.value.icon.size[props.size]
      );
    });
    const chipClass = computed(() => {
      return twJoin(
        ui.value.chip.base,
        ui.value.chip.size[props.size],
        ui.value.chip.position[props.chipPosition],
        ui.value.chip.background.replaceAll("{color}", props.chipColor)
      );
    });
    const error = ref(false);
    watch(() => props.src, () => {
      if (error.value) {
        error.value = false;
      }
    });
    function onError() {
      error.value = true;
    }
    return {
      // eslint-disable-next-line vue/no-dupe-keys
      ui,
      attrs,
      wrapperClass,
      // eslint-disable-next-line vue/no-dupe-keys
      imgClass,
      iconClass,
      chipClass,
      url,
      placeholder,
      error,
      onError
    };
  }
});
function _sfc_ssrRender$1(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  const _component_UIcon = __nuxt_component_0;
  _push(`<span${ssrRenderAttrs(mergeProps({ class: _ctx.wrapperClass }, _attrs))}>`);
  if (_ctx.url && !_ctx.error) {
    ssrRenderVNode(_push, createVNode(resolveDynamicComponent(_ctx.as), mergeProps({
      class: _ctx.imgClass,
      alt: _ctx.alt,
      src: _ctx.url
    }, _ctx.attrs, { onError: _ctx.onError }), null), _parent);
  } else if (_ctx.text) {
    _push(`<span class="${ssrRenderClass(_ctx.ui.text)}">${ssrInterpolate(_ctx.text)}</span>`);
  } else if (_ctx.icon) {
    _push(ssrRenderComponent(_component_UIcon, {
      name: _ctx.icon,
      class: _ctx.iconClass
    }, null, _parent));
  } else if (_ctx.placeholder) {
    _push(`<span class="${ssrRenderClass(_ctx.ui.placeholder)}">${ssrInterpolate(_ctx.placeholder)}</span>`);
  } else {
    _push(`<!---->`);
  }
  if (_ctx.chipColor) {
    _push(`<span class="${ssrRenderClass(_ctx.chipClass)}">${ssrInterpolate(_ctx.chipText)}</span>`);
  } else {
    _push(`<!---->`);
  }
  ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
  _push(`</span>`);
}
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/elements/Avatar.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const __nuxt_component_1 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["ssrRender", _sfc_ssrRender$1]]);
function d$1(u2, e2, r2) {
  let i2 = ref(r2 == null ? void 0 : r2.value), f2 = computed(() => u2.value !== void 0);
  return [computed(() => f2.value ? u2.value : i2.value), function(t2) {
    return f2.value || (i2.value = t2), e2 == null ? void 0 : e2(t2);
  }];
}
function t$4(e2) {
  typeof queueMicrotask == "function" ? queueMicrotask(e2) : Promise.resolve().then(e2).catch((o2) => setTimeout(() => {
    throw o2;
  }));
}
function o$5() {
  let a2 = [], s3 = { addEventListener(e2, t2, r2, i2) {
    return e2.addEventListener(t2, r2, i2), s3.add(() => e2.removeEventListener(t2, r2, i2));
  }, requestAnimationFrame(...e2) {
    let t2 = requestAnimationFrame(...e2);
    s3.add(() => cancelAnimationFrame(t2));
  }, nextFrame(...e2) {
    s3.requestAnimationFrame(() => {
      s3.requestAnimationFrame(...e2);
    });
  }, setTimeout(...e2) {
    let t2 = setTimeout(...e2);
    s3.add(() => clearTimeout(t2));
  }, microTask(...e2) {
    let t2 = { current: true };
    return t$4(() => {
      t2.current && e2[0]();
    }), s3.add(() => {
      t2.current = false;
    });
  }, style(e2, t2, r2) {
    let i2 = e2.style.getPropertyValue(t2);
    return Object.assign(e2.style, { [t2]: r2 }), this.add(() => {
      Object.assign(e2.style, { [t2]: i2 });
    });
  }, group(e2) {
    let t2 = o$5();
    return e2(t2), this.add(() => t2.dispose());
  }, add(e2) {
    return a2.push(e2), () => {
      let t2 = a2.indexOf(e2);
      if (t2 >= 0) for (let r2 of a2.splice(t2, 1)) r2();
    };
  }, dispose() {
    for (let e2 of a2.splice(0)) e2();
  } };
  return s3;
}
function i$6() {
  let o2 = o$5();
  return onUnmounted(() => o2.dispose()), o2;
}
function t$3() {
  let e2 = i$6();
  return (o2) => {
    e2.dispose(), e2.nextFrame(o2);
  };
}
var r$2;
let n$3 = Symbol("headlessui.useid"), o$4 = 0;
const i$5 = (r$2 = e$1.useId) != null ? r$2 : function() {
  return e$1.inject(n$3, () => `${++o$4}`)();
};
function s$2(t2) {
  e$1.provide(n$3, t2);
}
function o$3(e2) {
  var l2;
  if (e2 == null || e2.value == null) return null;
  let n2 = (l2 = e2.value.$el) != null ? l2 : e2.value;
  return n2 instanceof Node ? n2 : null;
}
function u$4(r2, n2, ...a2) {
  if (r2 in n2) {
    let e2 = n2[r2];
    return typeof e2 == "function" ? e2(...a2) : e2;
  }
  let t2 = new Error(`Tried to handle "${r2}" but there is no handler defined. Only defined handlers are: ${Object.keys(n2).map((e2) => `"${e2}"`).join(", ")}.`);
  throw Error.captureStackTrace && Error.captureStackTrace(t2, u$4), t2;
}
var i$4 = Object.defineProperty;
var d = (t2, e2, r2) => e2 in t2 ? i$4(t2, e2, { enumerable: true, configurable: true, writable: true, value: r2 }) : t2[e2] = r2;
var n$2 = (t2, e2, r2) => (d(t2, typeof e2 != "symbol" ? e2 + "" : e2, r2), r2);
let s$1 = class s {
  constructor() {
    n$2(this, "current", this.detect());
    n$2(this, "currentId", 0);
  }
  set(e2) {
    this.current !== e2 && (this.currentId = 0, this.current = e2);
  }
  reset() {
    this.set(this.detect());
  }
  nextId() {
    return ++this.currentId;
  }
  get isServer() {
    return this.current === "server";
  }
  get isClient() {
    return this.current === "client";
  }
  detect() {
    return "server";
  }
};
let c$2 = new s$1();
function i$3(r2) {
  if (c$2.isServer) return null;
  if (r2 instanceof Node) return r2.ownerDocument;
  if (r2 != null && r2.hasOwnProperty("value")) {
    let n2 = o$3(r2);
    if (n2) return n2.ownerDocument;
  }
  return void 0;
}
let c$1 = ["[contentEditable=true]", "[tabindex]", "a[href]", "area[href]", "button:not([disabled])", "iframe", "input:not([disabled])", "select:not([disabled])", "textarea:not([disabled])"].map((e2) => `${e2}:not([tabindex='-1'])`).join(",");
var N$1 = ((n2) => (n2[n2.First = 1] = "First", n2[n2.Previous = 2] = "Previous", n2[n2.Next = 4] = "Next", n2[n2.Last = 8] = "Last", n2[n2.WrapAround = 16] = "WrapAround", n2[n2.NoScroll = 32] = "NoScroll", n2))(N$1 || {}), T$1 = ((o2) => (o2[o2.Error = 0] = "Error", o2[o2.Overflow = 1] = "Overflow", o2[o2.Success = 2] = "Success", o2[o2.Underflow = 3] = "Underflow", o2))(T$1 || {}), F = ((t2) => (t2[t2.Previous = -1] = "Previous", t2[t2.Next = 1] = "Next", t2))(F || {});
var h = ((t2) => (t2[t2.Strict = 0] = "Strict", t2[t2.Loose = 1] = "Loose", t2))(h || {});
function w$2(e2, r2 = 0) {
  var t2;
  return e2 === ((t2 = i$3(e2)) == null ? void 0 : t2.body) ? false : u$4(r2, { [0]() {
    return e2.matches(c$1);
  }, [1]() {
    let l2 = e2;
    for (; l2 !== null; ) {
      if (l2.matches(c$1)) return true;
      l2 = l2.parentElement;
    }
    return false;
  } });
}
var y$1 = ((t2) => (t2[t2.Keyboard = 0] = "Keyboard", t2[t2.Mouse = 1] = "Mouse", t2))(y$1 || {});
function O(e2, r2 = (t2) => t2) {
  return e2.slice().sort((t2, l2) => {
    let o2 = r2(t2), i2 = r2(l2);
    if (o2 === null || i2 === null) return 0;
    let n2 = o2.compareDocumentPosition(i2);
    return n2 & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : n2 & Node.DOCUMENT_POSITION_PRECEDING ? 1 : 0;
  });
}
function t$2() {
  return /iPhone/gi.test((void 0).navigator.platform) || /Mac/gi.test((void 0).navigator.platform) && (void 0).navigator.maxTouchPoints > 0;
}
function i$2() {
  return /Android/gi.test((void 0).navigator.userAgent);
}
function n$1() {
  return t$2() || i$2();
}
function u$3(e2, t2, n2) {
  c$2.isServer || watchEffect((o2) => {
    (void 0).addEventListener(e2, t2, n2), o2(() => (void 0).removeEventListener(e2, t2, n2));
  });
}
function w$1(e2, n2, t2) {
  c$2.isServer || watchEffect((o2) => {
    (void 0).addEventListener(e2, n2, t2), o2(() => (void 0).removeEventListener(e2, n2, t2));
  });
}
function w(f2, m, l2 = computed(() => true)) {
  function a2(e2, r2) {
    if (!l2.value || e2.defaultPrevented) return;
    let t2 = r2(e2);
    if (t2 === null || !t2.getRootNode().contains(t2)) return;
    let c2 = function o2(n2) {
      return typeof n2 == "function" ? o2(n2()) : Array.isArray(n2) || n2 instanceof Set ? n2 : [n2];
    }(f2);
    for (let o2 of c2) {
      if (o2 === null) continue;
      let n2 = o2 instanceof HTMLElement ? o2 : o$3(o2);
      if (n2 != null && n2.contains(t2) || e2.composed && e2.composedPath().includes(n2)) return;
    }
    return !w$2(t2, h.Loose) && t2.tabIndex !== -1 && e2.preventDefault(), m(e2, t2);
  }
  let u2 = ref(null);
  u$3("pointerdown", (e2) => {
    var r2, t2;
    l2.value && (u2.value = ((t2 = (r2 = e2.composedPath) == null ? void 0 : r2.call(e2)) == null ? void 0 : t2[0]) || e2.target);
  }, true), u$3("mousedown", (e2) => {
    var r2, t2;
    l2.value && (u2.value = ((t2 = (r2 = e2.composedPath) == null ? void 0 : r2.call(e2)) == null ? void 0 : t2[0]) || e2.target);
  }, true), u$3("click", (e2) => {
    n$1() || u2.value && (a2(e2, () => u2.value), u2.value = null);
  }, true), u$3("touchend", (e2) => a2(e2, () => e2.target instanceof HTMLElement ? e2.target : null), true), w$1("blur", (e2) => a2(e2, () => (void 0).document.activeElement instanceof HTMLIFrameElement ? (void 0).document.activeElement : null), true);
}
function r$1(t2, e2) {
  if (t2) return t2;
  let n2 = e2 != null ? e2 : "button";
  if (typeof n2 == "string" && n2.toLowerCase() === "button") return "button";
}
function s2(t2, e2) {
  let n2 = ref(r$1(t2.value.type, t2.value.as));
  return onMounted(() => {
    n2.value = r$1(t2.value.type, t2.value.as);
  }), watchEffect(() => {
    var u2;
    n2.value || o$3(e2) && o$3(e2) instanceof HTMLButtonElement && !((u2 = o$3(e2)) != null && u2.hasAttribute("type")) && (n2.value = "button");
  }), n2;
}
function r(e2) {
  return [e2.screenX, e2.screenY];
}
function u$2() {
  let e2 = ref([-1, -1]);
  return { wasMoved(n2) {
    let t2 = r(n2);
    return e2.value[0] === t2[0] && e2.value[1] === t2[1] ? false : (e2.value = t2, true);
  }, update(n2) {
    e2.value = r(n2);
  } };
}
function i$1({ container: e2, accept: t2, walk: d2, enabled: o2 }) {
  watchEffect(() => {
    let r2 = e2.value;
    if (!r2 || o2 !== void 0 && !o2.value) return;
    let l2 = i$3(e2);
    if (!l2) return;
    let c2 = Object.assign((f2) => t2(f2), { acceptNode: t2 }), n2 = l2.createTreeWalker(r2, NodeFilter.SHOW_ELEMENT, c2, false);
    for (; n2.nextNode(); ) d2(n2.currentNode);
  });
}
var N = ((o2) => (o2[o2.None = 0] = "None", o2[o2.RenderStrategy = 1] = "RenderStrategy", o2[o2.Static = 2] = "Static", o2))(N || {}), S = ((e2) => (e2[e2.Unmount = 0] = "Unmount", e2[e2.Hidden = 1] = "Hidden", e2))(S || {});
function A$1({ visible: r2 = true, features: t2 = 0, ourProps: e2, theirProps: o2, ...i2 }) {
  var a2;
  let n2 = j(o2, e2), l2 = Object.assign(i2, { props: n2 });
  if (r2 || t2 & 2 && n2.static) return y(l2);
  if (t2 & 1) {
    let d2 = (a2 = n2.unmount) == null || a2 ? 0 : 1;
    return u$4(d2, { [0]() {
      return null;
    }, [1]() {
      return y({ ...i2, props: { ...n2, hidden: true, style: { display: "none" } } });
    } });
  }
  return y(l2);
}
function y({ props: r2, attrs: t2, slots: e2, slot: o2, name: i2 }) {
  var m, h2;
  let { as: n2, ...l2 } = T(r2, ["unmount", "static"]), a2 = (m = e2.default) == null ? void 0 : m.call(e2, o2), d2 = {};
  if (o2) {
    let u2 = false, c2 = [];
    for (let [p2, f2] of Object.entries(o2)) typeof f2 == "boolean" && (u2 = true), f2 === true && c2.push(p2);
    u2 && (d2["data-headlessui-state"] = c2.join(" "));
  }
  if (n2 === "template") {
    if (a2 = b(a2 != null ? a2 : []), Object.keys(l2).length > 0 || Object.keys(t2).length > 0) {
      let [u2, ...c2] = a2 != null ? a2 : [];
      if (!v(u2) || c2.length > 0) throw new Error(['Passing props on "template"!', "", `The current component <${i2} /> is rendering a "template".`, "However we need to passthrough the following props:", Object.keys(l2).concat(Object.keys(t2)).map((s3) => s3.trim()).filter((s3, g2, R) => R.indexOf(s3) === g2).sort((s3, g2) => s3.localeCompare(g2)).map((s3) => `  - ${s3}`).join(`
`), "", "You can apply a few solutions:", ['Add an `as="..."` prop, to ensure that we render an actual element instead of a "template".', "Render a single element as the child so that we can forward the props onto that element."].map((s3) => `  - ${s3}`).join(`
`)].join(`
`));
      let p2 = j((h2 = u2.props) != null ? h2 : {}, l2, d2), f2 = cloneVNode(u2, p2, true);
      for (let s3 in p2) s3.startsWith("on") && (f2.props || (f2.props = {}), f2.props[s3] = p2[s3]);
      return f2;
    }
    return Array.isArray(a2) && a2.length === 1 ? a2[0] : a2;
  }
  return h$1(n2, Object.assign({}, l2, d2), { default: () => a2 });
}
function b(r2) {
  return r2.flatMap((t2) => t2.type === Fragment ? b(t2.children) : [t2]);
}
function j(...r2) {
  if (r2.length === 0) return {};
  if (r2.length === 1) return r2[0];
  let t2 = {}, e2 = {};
  for (let i2 of r2) for (let n2 in i2) n2.startsWith("on") && typeof i2[n2] == "function" ? (e2[n2] != null || (e2[n2] = []), e2[n2].push(i2[n2])) : t2[n2] = i2[n2];
  if (t2.disabled || t2["aria-disabled"]) return Object.assign(t2, Object.fromEntries(Object.keys(e2).map((i2) => [i2, void 0])));
  for (let i2 in e2) Object.assign(t2, { [i2](n2, ...l2) {
    let a2 = e2[i2];
    for (let d2 of a2) {
      if (n2 instanceof Event && n2.defaultPrevented) return;
      d2(n2, ...l2);
    }
  } });
  return t2;
}
function E(r2) {
  let t2 = Object.assign({}, r2);
  for (let e2 in t2) t2[e2] === void 0 && delete t2[e2];
  return t2;
}
function T(r2, t2 = []) {
  let e2 = Object.assign({}, r2);
  for (let o2 of t2) o2 in e2 && delete e2[o2];
  return e2;
}
function v(r2) {
  return r2 == null ? false : typeof r2.type == "string" || typeof r2.type == "object" || typeof r2.type == "function";
}
var u$1 = ((e2) => (e2[e2.None = 1] = "None", e2[e2.Focusable = 2] = "Focusable", e2[e2.Hidden = 4] = "Hidden", e2))(u$1 || {});
let f$2 = defineComponent({ name: "Hidden", props: { as: { type: [Object, String], default: "div" }, features: { type: Number, default: 1 } }, setup(t2, { slots: n2, attrs: i2 }) {
  return () => {
    var r2;
    let { features: e2, ...d2 } = t2, o2 = { "aria-hidden": (e2 & 2) === 2 ? true : (r2 = d2["aria-hidden"]) != null ? r2 : void 0, hidden: (e2 & 4) === 4 ? true : void 0, style: { position: "fixed", top: 1, left: 1, width: 1, height: 0, padding: 0, margin: -1, overflow: "hidden", clip: "rect(0, 0, 0, 0)", whiteSpace: "nowrap", borderWidth: "0", ...(e2 & 4) === 4 && (e2 & 2) !== 2 && { display: "none" } } };
    return A$1({ ourProps: o2, theirProps: d2, slot: {}, attrs: i2, slots: n2, name: "Hidden" });
  };
} });
let n = Symbol("Context");
var i = ((e2) => (e2[e2.Open = 1] = "Open", e2[e2.Closed = 2] = "Closed", e2[e2.Closing = 4] = "Closing", e2[e2.Opening = 8] = "Opening", e2))(i || {});
function l() {
  return inject(n, null);
}
function t$1(o2) {
  provide(n, o2);
}
var o$2 = ((r2) => (r2.Space = " ", r2.Enter = "Enter", r2.Escape = "Escape", r2.Backspace = "Backspace", r2.Delete = "Delete", r2.ArrowLeft = "ArrowLeft", r2.ArrowUp = "ArrowUp", r2.ArrowRight = "ArrowRight", r2.ArrowDown = "ArrowDown", r2.Home = "Home", r2.End = "End", r2.PageUp = "PageUp", r2.PageDown = "PageDown", r2.Tab = "Tab", r2))(o$2 || {});
var g$1 = ((f2) => (f2[f2.Left = 0] = "Left", f2[f2.Right = 2] = "Right", f2))(g$1 || {});
let t = [];
function u(l2) {
  throw new Error("Unexpected object: " + l2);
}
var c = ((i2) => (i2[i2.First = 0] = "First", i2[i2.Previous = 1] = "Previous", i2[i2.Next = 2] = "Next", i2[i2.Last = 3] = "Last", i2[i2.Specific = 4] = "Specific", i2[i2.Nothing = 5] = "Nothing", i2))(c || {});
function f$1(l2, n2) {
  let t2 = n2.resolveItems();
  if (t2.length <= 0) return null;
  let r2 = n2.resolveActiveIndex(), s3 = r2 != null ? r2 : -1;
  switch (l2.focus) {
    case 0: {
      for (let e2 = 0; e2 < t2.length; ++e2) if (!n2.resolveDisabled(t2[e2], e2, t2)) return e2;
      return r2;
    }
    case 1: {
      s3 === -1 && (s3 = t2.length);
      for (let e2 = s3 - 1; e2 >= 0; --e2) if (!n2.resolveDisabled(t2[e2], e2, t2)) return e2;
      return r2;
    }
    case 2: {
      for (let e2 = s3 + 1; e2 < t2.length; ++e2) if (!n2.resolveDisabled(t2[e2], e2, t2)) return e2;
      return r2;
    }
    case 3: {
      for (let e2 = t2.length - 1; e2 >= 0; --e2) if (!n2.resolveDisabled(t2[e2], e2, t2)) return e2;
      return r2;
    }
    case 4: {
      for (let e2 = 0; e2 < t2.length; ++e2) if (n2.resolveId(t2[e2], e2, t2) === l2.id) return e2;
      return r2;
    }
    case 5:
      return null;
    default:
      u(l2);
  }
}
function e(i2 = {}, s3 = null, t2 = []) {
  for (let [r2, n2] of Object.entries(i2)) o$1(t2, f(s3, r2), n2);
  return t2;
}
function f(i2, s3) {
  return i2 ? i2 + "[" + s3 + "]" : s3;
}
function o$1(i2, s3, t2) {
  if (Array.isArray(t2)) for (let [r2, n2] of t2.entries()) o$1(i2, f(s3, r2.toString()), n2);
  else t2 instanceof Date ? i2.push([s3, t2.toISOString()]) : typeof t2 == "boolean" ? i2.push([s3, t2 ? "1" : "0"]) : typeof t2 == "string" ? i2.push([s3, t2]) : typeof t2 == "number" ? i2.push([s3, `${t2}`]) : t2 == null ? i2.push([s3, ""]) : e(t2, s3, i2);
}
function De(a2, h2) {
  return a2 === h2;
}
var Ee = ((r2) => (r2[r2.Open = 0] = "Open", r2[r2.Closed = 1] = "Closed", r2))(Ee || {}), Ve = ((r2) => (r2[r2.Single = 0] = "Single", r2[r2.Multi = 1] = "Multi", r2))(Ve || {}), ke = ((y2) => (y2[y2.Pointer = 0] = "Pointer", y2[y2.Focus = 1] = "Focus", y2[y2.Other = 2] = "Other", y2))(ke || {});
let ne = Symbol("ComboboxContext");
function K(a2) {
  let h2 = inject(ne, null);
  if (h2 === null) {
    let r2 = new Error(`<${a2} /> is missing a parent <Combobox /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(r2, K), r2;
  }
  return h2;
}
let ie = Symbol("VirtualContext"), Ae$1 = defineComponent({ name: "VirtualProvider", setup(a2, { slots: h2 }) {
  let r2 = K("VirtualProvider"), y2 = computed(() => {
    let c2 = o$3(r2.optionsRef);
    if (!c2) return { start: 0, end: 0 };
    let f2 = (void 0).getComputedStyle(c2);
    return { start: parseFloat(f2.paddingBlockStart || f2.paddingTop), end: parseFloat(f2.paddingBlockEnd || f2.paddingBottom) };
  }), o2 = useVirtualizer(computed(() => ({ scrollPaddingStart: y2.value.start, scrollPaddingEnd: y2.value.end, count: r2.virtual.value.options.length, estimateSize() {
    return 40;
  }, getScrollElement() {
    return o$3(r2.optionsRef);
  }, overscan: 12 }))), u2 = computed(() => {
    var c2;
    return (c2 = r2.virtual.value) == null ? void 0 : c2.options;
  }), e2 = ref(0);
  return watch([u2], () => {
    e2.value += 1;
  }), provide(ie, r2.virtual.value ? o2 : null), () => [h$1("div", { style: { position: "relative", width: "100%", height: `${o2.value.getTotalSize()}px` }, ref: (c2) => {
    if (c2) {
      if (typeof process != "undefined" && process.env.JEST_WORKER_ID !== void 0 || r2.activationTrigger.value === 0) return;
      r2.activeOptionIndex.value !== null && r2.virtual.value.options.length > r2.activeOptionIndex.value && o2.value.scrollToIndex(r2.activeOptionIndex.value);
    }
  } }, o2.value.getVirtualItems().map((c2) => cloneVNode(h2.default({ option: r2.virtual.value.options[c2.index], open: r2.comboboxState.value === 0 })[0], { key: `${e2.value}-${c2.index}`, "data-index": c2.index, "aria-setsize": r2.virtual.value.options.length, "aria-posinset": c2.index + 1, style: { position: "absolute", top: 0, left: 0, transform: `translateY(${c2.start}px)`, overflowAnchor: "none" } })))];
} }), lt = defineComponent({ name: "Combobox", emits: { "update:modelValue": (a2) => true }, props: { as: { type: [Object, String], default: "template" }, disabled: { type: [Boolean], default: false }, by: { type: [String, Function], nullable: true, default: null }, modelValue: { type: [Object, String, Number, Boolean], default: void 0 }, defaultValue: { type: [Object, String, Number, Boolean], default: void 0 }, form: { type: String, optional: true }, name: { type: String, optional: true }, nullable: { type: Boolean, default: false }, multiple: { type: [Boolean], default: false }, immediate: { type: [Boolean], default: false }, virtual: { type: Object, default: null } }, inheritAttrs: false, setup(a2, { slots: h2, attrs: r2, emit: y2 }) {
  let o2 = ref(1), u2 = ref(null), e$12 = ref(null), c$12 = ref(null), f2 = ref(null), S2 = ref({ static: false, hold: false }), v2 = ref([]), d2 = ref(null), D = ref(2), E$1 = ref(false);
  function w$12(t2 = (n2) => n2) {
    let n2 = d2.value !== null ? v2.value[d2.value] : null, s3 = t2(v2.value.slice()), b2 = s3.length > 0 && s3[0].dataRef.order.value !== null ? s3.sort((C, A2) => C.dataRef.order.value - A2.dataRef.order.value) : O(s3, (C) => o$3(C.dataRef.domRef)), O$1 = n2 ? b2.indexOf(n2) : null;
    return O$1 === -1 && (O$1 = null), { options: b2, activeOptionIndex: O$1 };
  }
  let M = computed(() => a2.multiple ? 1 : 0), $2 = computed(() => a2.nullable), [B, p2] = d$1(computed(() => a2.modelValue), (t2) => y2("update:modelValue", t2), computed(() => a2.defaultValue)), R = computed(() => B.value === void 0 ? u$4(M.value, { [1]: [], [0]: void 0 }) : B.value), V = null, i$12 = null;
  function I(t2) {
    return u$4(M.value, { [0]() {
      return p2 == null ? void 0 : p2(t2);
    }, [1]: () => {
      let n2 = toRaw(l2.value.value).slice(), s3 = toRaw(t2), b2 = n2.findIndex((O2) => l2.compare(s3, toRaw(O2)));
      return b2 === -1 ? n2.push(s3) : n2.splice(b2, 1), p2 == null ? void 0 : p2(n2);
    } });
  }
  let T$12 = computed(() => {
  });
  watch([T$12], ([t2], [n2]) => {
    if (l2.virtual.value && t2 && n2 && d2.value !== null) {
      let s3 = t2.indexOf(n2[d2.value]);
      s3 !== -1 ? d2.value = s3 : d2.value = null;
    }
  });
  let l2 = { comboboxState: o2, value: R, mode: M, compare(t2, n2) {
    if (typeof a2.by == "string") {
      let s3 = a2.by;
      return (t2 == null ? void 0 : t2[s3]) === (n2 == null ? void 0 : n2[s3]);
    }
    return a2.by === null ? De(t2, n2) : a2.by(t2, n2);
  }, calculateIndex(t2) {
    return l2.virtual.value ? a2.by === null ? l2.virtual.value.options.indexOf(t2) : l2.virtual.value.options.findIndex((n2) => l2.compare(n2, t2)) : v2.value.findIndex((n2) => l2.compare(n2.dataRef.value, t2));
  }, defaultValue: computed(() => a2.defaultValue), nullable: $2, immediate: computed(() => false), virtual: computed(() => null), inputRef: e$12, labelRef: u2, buttonRef: c$12, optionsRef: f2, disabled: computed(() => a2.disabled), options: v2, change(t2) {
    p2(t2);
  }, activeOptionIndex: computed(() => {
    if (E$1.value && d2.value === null && (l2.virtual.value ? l2.virtual.value.options.length > 0 : v2.value.length > 0)) {
      if (l2.virtual.value) {
        let n2 = l2.virtual.value.options.findIndex((s3) => {
          var b2;
          return !((b2 = l2.virtual.value) != null && b2.disabled(s3));
        });
        if (n2 !== -1) return n2;
      }
      let t2 = v2.value.findIndex((n2) => !n2.dataRef.disabled);
      if (t2 !== -1) return t2;
    }
    return d2.value;
  }), activationTrigger: D, optionsPropsRef: S2, closeCombobox() {
    E$1.value = false, !a2.disabled && o2.value !== 1 && (o2.value = 1, d2.value = null);
  }, openCombobox() {
    if (E$1.value = true, !a2.disabled && o2.value !== 0) {
      if (l2.value.value) {
        let t2 = l2.calculateIndex(l2.value.value);
        t2 !== -1 && (d2.value = t2);
      }
      o2.value = 0;
    }
  }, setActivationTrigger(t2) {
    D.value = t2;
  }, goToOption(t2, n2, s3) {
    E$1.value = false, V !== null && cancelAnimationFrame(V), V = requestAnimationFrame(() => {
      if (a2.disabled || f2.value && !S2.value.static && o2.value === 1) return;
      if (l2.virtual.value) {
        d2.value = t2 === c.Specific ? n2 : f$1({ focus: t2 }, { resolveItems: () => l2.virtual.value.options, resolveActiveIndex: () => {
          var C, A2;
          return (A2 = (C = l2.activeOptionIndex.value) != null ? C : l2.virtual.value.options.findIndex((j2) => {
            var q;
            return !((q = l2.virtual.value) != null && q.disabled(j2));
          })) != null ? A2 : null;
        }, resolveDisabled: (C) => l2.virtual.value.disabled(C), resolveId() {
          throw new Error("Function not implemented.");
        } }), D.value = s3 != null ? s3 : 2;
        return;
      }
      let b2 = w$12();
      if (b2.activeOptionIndex === null) {
        let C = b2.options.findIndex((A2) => !A2.dataRef.disabled);
        C !== -1 && (b2.activeOptionIndex = C);
      }
      let O2 = t2 === c.Specific ? n2 : f$1({ focus: t2 }, { resolveItems: () => b2.options, resolveActiveIndex: () => b2.activeOptionIndex, resolveId: (C) => C.id, resolveDisabled: (C) => C.dataRef.disabled });
      d2.value = O2, D.value = s3 != null ? s3 : 2, v2.value = b2.options;
    });
  }, selectOption(t2) {
    let n2 = v2.value.find((b2) => b2.id === t2);
    if (!n2) return;
    let { dataRef: s3 } = n2;
    I(s3.value);
  }, selectActiveOption() {
    if (l2.activeOptionIndex.value !== null) {
      if (l2.virtual.value) I(l2.virtual.value.options[l2.activeOptionIndex.value]);
      else {
        let { dataRef: t2 } = v2.value[l2.activeOptionIndex.value];
        I(t2.value);
      }
      l2.goToOption(c.Specific, l2.activeOptionIndex.value);
    }
  }, registerOption(t2, n2) {
    let s3 = reactive({ id: t2, dataRef: n2 });
    if (l2.virtual.value) {
      v2.value.push(s3);
      return;
    }
    i$12 && cancelAnimationFrame(i$12);
    let b2 = w$12((O2) => (O2.push(s3), O2));
    d2.value === null && l2.isSelected(n2.value.value) && (b2.activeOptionIndex = b2.options.indexOf(s3)), v2.value = b2.options, d2.value = b2.activeOptionIndex, D.value = 2, b2.options.some((O2) => !o$3(O2.dataRef.domRef)) && (i$12 = requestAnimationFrame(() => {
      let O2 = w$12();
      v2.value = O2.options, d2.value = O2.activeOptionIndex;
    }));
  }, unregisterOption(t2, n2) {
    if (V !== null && cancelAnimationFrame(V), n2 && (E$1.value = true), l2.virtual.value) {
      v2.value = v2.value.filter((b2) => b2.id !== t2);
      return;
    }
    let s3 = w$12((b2) => {
      let O2 = b2.findIndex((C) => C.id === t2);
      return O2 !== -1 && b2.splice(O2, 1), b2;
    });
    v2.value = s3.options, d2.value = s3.activeOptionIndex, D.value = 2;
  }, isSelected(t2) {
    return u$4(M.value, { [0]: () => l2.compare(toRaw(l2.value.value), toRaw(t2)), [1]: () => toRaw(l2.value.value).some((n2) => l2.compare(toRaw(n2), toRaw(t2))) });
  }, isActive(t2) {
    return d2.value === l2.calculateIndex(t2);
  } };
  w([e$12, c$12, f2], () => l2.closeCombobox(), computed(() => o2.value === 0)), provide(ne, l2), t$1(computed(() => u$4(o2.value, { [0]: i.Open, [1]: i.Closed })));
  let g2 = computed(() => {
    var t2;
    return (t2 = o$3(e$12)) == null ? void 0 : t2.closest("form");
  });
  return onMounted(() => {
    watch([g2], () => {
      if (!g2.value || a2.defaultValue === void 0) return;
      function t2() {
        l2.change(a2.defaultValue);
      }
      return g2.value.addEventListener("reset", t2), () => {
        var n2;
        (n2 = g2.value) == null || n2.removeEventListener("reset", t2);
      };
    }, { immediate: true });
  }), () => {
    var C, A2, j2;
    let { name: t2, disabled: n2, form: s3, ...b2 } = a2, O2 = { open: o2.value === 0, disabled: n2, activeIndex: l2.activeOptionIndex.value, activeOption: l2.activeOptionIndex.value === null ? null : l2.virtual.value ? l2.virtual.value.options[(C = l2.activeOptionIndex.value) != null ? C : 0] : (j2 = (A2 = l2.options.value[l2.activeOptionIndex.value]) == null ? void 0 : A2.dataRef.value) != null ? j2 : null, value: R.value };
    return h$1(Fragment, [...t2 != null && R.value != null ? e({ [t2]: R.value }).map(([q, ue]) => h$1(f$2, E({ features: u$1.Hidden, key: q, as: "input", type: "hidden", hidden: true, readOnly: true, form: s3, disabled: n2, name: q, value: ue }))) : [], A$1({ theirProps: { ...r2, ...T(b2, ["by", "defaultValue", "immediate", "modelValue", "multiple", "nullable", "onUpdate:modelValue", "virtual"]) }, ourProps: {}, slot: O2, slots: h2, attrs: r2, name: "Combobox" })]);
  };
} });
defineComponent({ name: "ComboboxLabel", props: { as: { type: [Object, String], default: "label" }, id: { type: String, default: null } }, setup(a2, { attrs: h2, slots: r2 }) {
  var e2;
  let y2 = (e2 = a2.id) != null ? e2 : `headlessui-combobox-label-${i$5()}`, o2 = K("ComboboxLabel");
  function u2() {
    var c2;
    (c2 = o$3(o2.inputRef)) == null || c2.focus({ preventScroll: true });
  }
  return () => {
    let c2 = { open: o2.comboboxState.value === 0, disabled: o2.disabled.value }, { ...f2 } = a2, S2 = { id: y2, ref: o2.labelRef, onClick: u2 };
    return A$1({ ourProps: S2, theirProps: f2, slot: c2, attrs: h2, slots: r2, name: "ComboboxLabel" });
  };
} });
let nt = defineComponent({ name: "ComboboxButton", props: { as: { type: [Object, String], default: "button" }, id: { type: String, default: null } }, setup(a2, { attrs: h2, slots: r2, expose: y2 }) {
  var S2;
  let o2 = (S2 = a2.id) != null ? S2 : `headlessui-combobox-button-${i$5()}`, u2 = K("ComboboxButton");
  y2({ el: u2.buttonRef, $el: u2.buttonRef });
  function e2(v2) {
    u2.disabled.value || (u2.comboboxState.value === 0 ? u2.closeCombobox() : (v2.preventDefault(), u2.openCombobox()), nextTick(() => {
      var d2;
      return (d2 = o$3(u2.inputRef)) == null ? void 0 : d2.focus({ preventScroll: true });
    }));
  }
  function c$12(v2) {
    switch (v2.key) {
      case o$2.ArrowDown:
        v2.preventDefault(), v2.stopPropagation(), u2.comboboxState.value === 1 && u2.openCombobox(), nextTick(() => {
          var d2;
          return (d2 = u2.inputRef.value) == null ? void 0 : d2.focus({ preventScroll: true });
        });
        return;
      case o$2.ArrowUp:
        v2.preventDefault(), v2.stopPropagation(), u2.comboboxState.value === 1 && (u2.openCombobox(), nextTick(() => {
          u2.value.value || u2.goToOption(c.Last);
        })), nextTick(() => {
          var d2;
          return (d2 = u2.inputRef.value) == null ? void 0 : d2.focus({ preventScroll: true });
        });
        return;
      case o$2.Escape:
        if (u2.comboboxState.value !== 0) return;
        v2.preventDefault(), u2.optionsRef.value && !u2.optionsPropsRef.value.static && v2.stopPropagation(), u2.closeCombobox(), nextTick(() => {
          var d2;
          return (d2 = u2.inputRef.value) == null ? void 0 : d2.focus({ preventScroll: true });
        });
        return;
    }
  }
  let f2 = s2(computed(() => ({ as: a2.as, type: h2.type })), u2.buttonRef);
  return () => {
    var E2, w2;
    let v2 = { open: u2.comboboxState.value === 0, disabled: u2.disabled.value, value: u2.value.value }, { ...d2 } = a2, D = { ref: u2.buttonRef, id: o2, type: f2.value, tabindex: "-1", "aria-haspopup": "listbox", "aria-controls": (E2 = o$3(u2.optionsRef)) == null ? void 0 : E2.id, "aria-expanded": u2.comboboxState.value === 0, "aria-labelledby": u2.labelRef.value ? [(w2 = o$3(u2.labelRef)) == null ? void 0 : w2.id, o2].join(" ") : void 0, disabled: u2.disabled.value === true ? true : void 0, onKeydown: c$12, onClick: e2 };
    return A$1({ ourProps: D, theirProps: d2, slot: v2, attrs: h2, slots: r2, name: "ComboboxButton" });
  };
} }), it = defineComponent({ name: "ComboboxInput", props: { as: { type: [Object, String], default: "input" }, static: { type: Boolean, default: false }, unmount: { type: Boolean, default: true }, displayValue: { type: Function }, defaultValue: { type: String, default: void 0 }, id: { type: String, default: null } }, emits: { change: (a2) => true }, setup(a2, { emit: h2, attrs: r2, slots: y2, expose: o2 }) {
  var V;
  let u2 = (V = a2.id) != null ? V : `headlessui-combobox-input-${i$5()}`, e2 = K("ComboboxInput"), c$12 = computed(() => i$3(o$3(e2.inputRef))), f2 = { value: false };
  o2({ el: e2.inputRef, $el: e2.inputRef });
  function S2() {
    e2.change(null);
    let i2 = o$3(e2.optionsRef);
    i2 && (i2.scrollTop = 0), e2.goToOption(c.Nothing);
  }
  let v2 = computed(() => {
    var I;
    let i2 = e2.value.value;
    return o$3(e2.inputRef) ? typeof a2.displayValue != "undefined" && i2 !== void 0 ? (I = a2.displayValue(i2)) != null ? I : "" : typeof i2 == "string" ? i2 : "" : "";
  });
  onMounted(() => {
    watch([v2, e2.comboboxState, c$12], ([i2, I], [T2, l2]) => {
      if (f2.value) return;
      let g2 = o$3(e2.inputRef);
      g2 && ((l2 === 0 && I === 1 || i2 !== T2) && (g2.value = i2), requestAnimationFrame(() => {
        var s3;
        if (f2.value || !g2 || ((s3 = c$12.value) == null ? void 0 : s3.activeElement) !== g2) return;
        let { selectionStart: t2, selectionEnd: n2 } = g2;
        Math.abs((n2 != null ? n2 : 0) - (t2 != null ? t2 : 0)) === 0 && t2 === 0 && g2.setSelectionRange(g2.value.length, g2.value.length);
      }));
    }, { immediate: true }), watch([e2.comboboxState], ([i2], [I]) => {
      if (i2 === 0 && I === 1) {
        if (f2.value) return;
        let T2 = o$3(e2.inputRef);
        if (!T2) return;
        let l2 = T2.value, { selectionStart: g2, selectionEnd: t2, selectionDirection: n2 } = T2;
        T2.value = "", T2.value = l2, n2 !== null ? T2.setSelectionRange(g2, t2, n2) : T2.setSelectionRange(g2, t2);
      }
    });
  });
  let d2 = ref(false);
  function D() {
    d2.value = true;
  }
  function E2() {
    o$5().nextFrame(() => {
      d2.value = false;
    });
  }
  let w2 = t$3();
  function M(i2) {
    switch (f2.value = true, w2(() => {
      f2.value = false;
    }), i2.key) {
      case o$2.Enter:
        if (f2.value = false, e2.comboboxState.value !== 0 || d2.value) return;
        if (i2.preventDefault(), i2.stopPropagation(), e2.activeOptionIndex.value === null) {
          e2.closeCombobox();
          return;
        }
        e2.selectActiveOption(), e2.mode.value === 0 && e2.closeCombobox();
        break;
      case o$2.ArrowDown:
        return f2.value = false, i2.preventDefault(), i2.stopPropagation(), u$4(e2.comboboxState.value, { [0]: () => e2.goToOption(c.Next), [1]: () => e2.openCombobox() });
      case o$2.ArrowUp:
        return f2.value = false, i2.preventDefault(), i2.stopPropagation(), u$4(e2.comboboxState.value, { [0]: () => e2.goToOption(c.Previous), [1]: () => {
          e2.openCombobox(), nextTick(() => {
            e2.value.value || e2.goToOption(c.Last);
          });
        } });
      case o$2.Home:
        if (i2.shiftKey) break;
        return f2.value = false, i2.preventDefault(), i2.stopPropagation(), e2.goToOption(c.First);
      case o$2.PageUp:
        return f2.value = false, i2.preventDefault(), i2.stopPropagation(), e2.goToOption(c.First);
      case o$2.End:
        if (i2.shiftKey) break;
        return f2.value = false, i2.preventDefault(), i2.stopPropagation(), e2.goToOption(c.Last);
      case o$2.PageDown:
        return f2.value = false, i2.preventDefault(), i2.stopPropagation(), e2.goToOption(c.Last);
      case o$2.Escape:
        if (f2.value = false, e2.comboboxState.value !== 0) return;
        i2.preventDefault(), e2.optionsRef.value && !e2.optionsPropsRef.value.static && i2.stopPropagation(), e2.nullable.value && e2.mode.value === 0 && e2.value.value === null && S2(), e2.closeCombobox();
        break;
      case o$2.Tab:
        if (f2.value = false, e2.comboboxState.value !== 0) return;
        e2.mode.value === 0 && e2.activationTrigger.value !== 1 && e2.selectActiveOption(), e2.closeCombobox();
        break;
    }
  }
  function $2(i2) {
    h2("change", i2), e2.nullable.value && e2.mode.value === 0 && i2.target.value === "" && S2(), e2.openCombobox();
  }
  function B(i2) {
    var T2, l2, g2;
    let I = (T2 = i2.relatedTarget) != null ? T2 : t.find((t2) => t2 !== i2.currentTarget);
    if (f2.value = false, !((l2 = o$3(e2.optionsRef)) != null && l2.contains(I)) && !((g2 = o$3(e2.buttonRef)) != null && g2.contains(I)) && e2.comboboxState.value === 0) return i2.preventDefault(), e2.mode.value === 0 && (e2.nullable.value && e2.value.value === null ? S2() : e2.activationTrigger.value !== 1 && e2.selectActiveOption()), e2.closeCombobox();
  }
  function p2(i2) {
    var T2, l2, g2;
    let I = (T2 = i2.relatedTarget) != null ? T2 : t.find((t2) => t2 !== i2.currentTarget);
    (l2 = o$3(e2.buttonRef)) != null && l2.contains(I) || (g2 = o$3(e2.optionsRef)) != null && g2.contains(I) || e2.disabled.value || e2.immediate.value && e2.comboboxState.value !== 0 && (e2.openCombobox(), o$5().nextFrame(() => {
      e2.setActivationTrigger(1);
    }));
  }
  let R = computed(() => {
    var i2, I, T2, l2;
    return (l2 = (T2 = (I = a2.defaultValue) != null ? I : e2.defaultValue.value !== void 0 ? (i2 = a2.displayValue) == null ? void 0 : i2.call(a2, e2.defaultValue.value) : null) != null ? T2 : e2.defaultValue.value) != null ? l2 : "";
  });
  return () => {
    var t2, n2, s3, b2, O2, C, A2;
    let i2 = { open: e2.comboboxState.value === 0 }, { displayValue: I, onChange: T2, ...l2 } = a2, g2 = { "aria-controls": (t2 = e2.optionsRef.value) == null ? void 0 : t2.id, "aria-expanded": e2.comboboxState.value === 0, "aria-activedescendant": e2.activeOptionIndex.value === null ? void 0 : e2.virtual.value ? (n2 = e2.options.value.find((j2) => !e2.virtual.value.disabled(j2.dataRef.value) && e2.compare(j2.dataRef.value, e2.virtual.value.options[e2.activeOptionIndex.value]))) == null ? void 0 : n2.id : (s3 = e2.options.value[e2.activeOptionIndex.value]) == null ? void 0 : s3.id, "aria-labelledby": (C = (b2 = o$3(e2.labelRef)) == null ? void 0 : b2.id) != null ? C : (O2 = o$3(e2.buttonRef)) == null ? void 0 : O2.id, "aria-autocomplete": "list", id: u2, onCompositionstart: D, onCompositionend: E2, onKeydown: M, onInput: $2, onFocus: p2, onBlur: B, role: "combobox", type: (A2 = r2.type) != null ? A2 : "text", tabIndex: 0, ref: e2.inputRef, defaultValue: R.value, disabled: e2.disabled.value === true ? true : void 0 };
    return A$1({ ourProps: g2, theirProps: l2, slot: i2, attrs: r2, slots: y2, features: N.RenderStrategy | N.Static, name: "ComboboxInput" });
  };
} }), ut = defineComponent({ name: "ComboboxOptions", props: { as: { type: [Object, String], default: "ul" }, static: { type: Boolean, default: false }, unmount: { type: Boolean, default: true }, hold: { type: [Boolean], default: false } }, setup(a2, { attrs: h2, slots: r2, expose: y2 }) {
  let o2 = K("ComboboxOptions"), u2 = `headlessui-combobox-options-${i$5()}`;
  y2({ el: o2.optionsRef, $el: o2.optionsRef }), watchEffect(() => {
    o2.optionsPropsRef.value.static = a2.static;
  }), watchEffect(() => {
    o2.optionsPropsRef.value.hold = a2.hold;
  });
  let e2 = l(), c2 = computed(() => e2 !== null ? (e2.value & i.Open) === i.Open : o2.comboboxState.value === 0);
  i$1({ container: computed(() => o$3(o2.optionsRef)), enabled: computed(() => o2.comboboxState.value === 0), accept(S2) {
    return S2.getAttribute("role") === "option" ? NodeFilter.FILTER_REJECT : S2.hasAttribute("role") ? NodeFilter.FILTER_SKIP : NodeFilter.FILTER_ACCEPT;
  }, walk(S2) {
    S2.setAttribute("role", "none");
  } });
  function f2(S2) {
    S2.preventDefault();
  }
  return () => {
    var D, E2, w2;
    let S2 = { open: o2.comboboxState.value === 0 }, v2 = { "aria-labelledby": (w2 = (D = o$3(o2.labelRef)) == null ? void 0 : D.id) != null ? w2 : (E2 = o$3(o2.buttonRef)) == null ? void 0 : E2.id, id: u2, ref: o2.optionsRef, role: "listbox", "aria-multiselectable": o2.mode.value === 1 ? true : void 0, onMousedown: f2 }, d2 = T(a2, ["hold"]);
    return A$1({ ourProps: v2, theirProps: d2, slot: S2, attrs: h2, slots: o2.virtual.value && o2.comboboxState.value === 0 ? { ...r2, default: () => [h$1(Ae$1, {}, r2.default)] } : r2, features: N.RenderStrategy | N.Static, visible: c2.value, name: "ComboboxOptions" });
  };
} }), rt = defineComponent({ name: "ComboboxOption", props: { as: { type: [Object, String], default: "li" }, value: { type: [Object, String, Number, Boolean] }, disabled: { type: Boolean, default: false }, order: { type: [Number], default: null } }, setup(a2, { slots: h2, attrs: r2, expose: y2 }) {
  let o2 = K("ComboboxOption"), u2 = `headlessui-combobox-option-${i$5()}`, e2 = ref(null), c$12 = computed(() => a2.disabled);
  y2({ el: e2, $el: e2 });
  let f2 = computed(() => {
    var p2;
    return o2.virtual.value ? o2.activeOptionIndex.value === o2.calculateIndex(a2.value) : o2.activeOptionIndex.value === null ? false : ((p2 = o2.options.value[o2.activeOptionIndex.value]) == null ? void 0 : p2.id) === u2;
  }), S2 = computed(() => o2.isSelected(a2.value)), v2 = inject(ie, null), d2 = computed(() => ({ disabled: a2.disabled, value: a2.value, domRef: e2, order: computed(() => a2.order) }));
  onMounted(() => o2.registerOption(u2, d2)), onUnmounted(() => o2.unregisterOption(u2, f2.value)), watchEffect(() => {
    let p2 = o$3(e2);
    p2 && (v2 == null || v2.value.measureElement(p2));
  }), watchEffect(() => {
    o2.comboboxState.value === 0 && f2.value && (o2.virtual.value || o2.activationTrigger.value !== 0 && nextTick(() => {
      var p2, R;
      return (R = (p2 = o$3(e2)) == null ? void 0 : p2.scrollIntoView) == null ? void 0 : R.call(p2, { block: "nearest" });
    }));
  });
  function D(p2) {
    p2.preventDefault(), p2.button === g$1.Left && (c$12.value || (o2.selectOption(u2), n$1() || requestAnimationFrame(() => {
      var R;
      return (R = o$3(o2.inputRef)) == null ? void 0 : R.focus({ preventScroll: true });
    }), o2.mode.value === 0 && o2.closeCombobox()));
  }
  function E2() {
    var R;
    if (a2.disabled || (R = o2.virtual.value) != null && R.disabled(a2.value)) return o2.goToOption(c.Nothing);
    let p2 = o2.calculateIndex(a2.value);
    o2.goToOption(c.Specific, p2);
  }
  let w2 = u$2();
  function M(p2) {
    w2.update(p2);
  }
  function $2(p2) {
    var V;
    if (!w2.wasMoved(p2) || a2.disabled || (V = o2.virtual.value) != null && V.disabled(a2.value) || f2.value) return;
    let R = o2.calculateIndex(a2.value);
    o2.goToOption(c.Specific, R, 0);
  }
  function B(p2) {
    var R;
    w2.wasMoved(p2) && (a2.disabled || (R = o2.virtual.value) != null && R.disabled(a2.value) || f2.value && (o2.optionsPropsRef.value.hold || o2.goToOption(c.Nothing)));
  }
  return () => {
    let { disabled: p2 } = a2, R = { active: f2.value, selected: S2.value, disabled: p2 }, V = { id: u2, ref: e2, role: "option", tabIndex: p2 === true ? void 0 : -1, "aria-disabled": p2 === true ? true : void 0, "aria-selected": S2.value, disabled: void 0, onMousedown: D, onFocus: E2, onPointerenter: M, onMouseenter: M, onPointermove: $2, onMousemove: $2, onPointerleave: B, onMouseleave: B }, i2 = T(a2, ["order", "value"]);
    return A$1({ ourProps: V, theirProps: i2, slot: R, attrs: r2, slots: h2, name: "ComboboxOption" });
  };
} });
let a = /([\u2700-\u27BF]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF])/g;
function o(e2) {
  var r2, i2;
  let n2 = (r2 = e2.innerText) != null ? r2 : "", t2 = e2.cloneNode(true);
  if (!(t2 instanceof HTMLElement)) return n2;
  let u2 = false;
  for (let f2 of t2.querySelectorAll('[hidden],[aria-hidden],[role="img"]')) f2.remove(), u2 = true;
  let l2 = u2 ? (i2 = t2.innerText) != null ? i2 : "" : n2;
  return a.test(l2) && (l2 = l2.replace(a, "")), l2;
}
function g(e2) {
  let n2 = e2.getAttribute("aria-label");
  if (typeof n2 == "string") return n2.trim();
  let t2 = e2.getAttribute("aria-labelledby");
  if (t2) {
    let u2 = t2.split(" ").map((l2) => {
      let r2 = (void 0).getElementById(l2);
      if (r2) {
        let i2 = r2.getAttribute("aria-label");
        return typeof i2 == "string" ? i2.trim() : o(r2).trim();
      }
      return null;
    }).filter(Boolean);
    if (u2.length > 0) return u2.join(", ");
  }
  return o(e2).trim();
}
function p(a2) {
  let t2 = ref(""), r2 = ref("");
  return () => {
    let e2 = o$3(a2);
    if (!e2) return "";
    let l2 = e2.innerText;
    if (t2.value === l2) return r2.value;
    let u2 = g(e2).trim().toLowerCase();
    return t2.value = l2, r2.value = u2, u2;
  };
}
function pe(o2, b2) {
  return o2 === b2;
}
var ce = ((r2) => (r2[r2.Open = 0] = "Open", r2[r2.Closed = 1] = "Closed", r2))(ce || {}), ve = ((r2) => (r2[r2.Single = 0] = "Single", r2[r2.Multi = 1] = "Multi", r2))(ve || {}), be = ((r2) => (r2[r2.Pointer = 0] = "Pointer", r2[r2.Other = 1] = "Other", r2))(be || {});
function me(o2) {
  requestAnimationFrame(() => requestAnimationFrame(o2));
}
let $ = Symbol("ListboxContext");
function A(o2) {
  let b2 = inject($, null);
  if (b2 === null) {
    let r2 = new Error(`<${o2} /> is missing a parent <Listbox /> component.`);
    throw Error.captureStackTrace && Error.captureStackTrace(r2, A), r2;
  }
  return b2;
}
let Ie = defineComponent({ name: "Listbox", emits: { "update:modelValue": (o2) => true }, props: { as: { type: [Object, String], default: "template" }, disabled: { type: [Boolean], default: false }, by: { type: [String, Function], default: () => pe }, horizontal: { type: [Boolean], default: false }, modelValue: { type: [Object, String, Number, Boolean], default: void 0 }, defaultValue: { type: [Object, String, Number, Boolean], default: void 0 }, form: { type: String, optional: true }, name: { type: String, optional: true }, multiple: { type: [Boolean], default: false } }, inheritAttrs: false, setup(o2, { slots: b2, attrs: r2, emit: w$12 }) {
  let n2 = ref(1), e$12 = ref(null), f2 = ref(null), v2 = ref(null), s3 = ref([]), m = ref(""), p2 = ref(null), a2 = ref(1);
  function u2(t2 = (i2) => i2) {
    let i2 = p2.value !== null ? s3.value[p2.value] : null, l2 = O(t2(s3.value.slice()), (O2) => o$3(O2.dataRef.domRef)), d2 = i2 ? l2.indexOf(i2) : null;
    return d2 === -1 && (d2 = null), { options: l2, activeOptionIndex: d2 };
  }
  let D = computed(() => o2.multiple ? 1 : 0), [y2, L] = d$1(computed(() => o2.modelValue), (t2) => w$12("update:modelValue", t2), computed(() => o2.defaultValue)), M = computed(() => y2.value === void 0 ? u$4(D.value, { [1]: [], [0]: void 0 }) : y2.value), k = { listboxState: n2, value: M, mode: D, compare(t2, i2) {
    if (typeof o2.by == "string") {
      let l2 = o2.by;
      return (t2 == null ? void 0 : t2[l2]) === (i2 == null ? void 0 : i2[l2]);
    }
    return o2.by(t2, i2);
  }, orientation: computed(() => o2.horizontal ? "horizontal" : "vertical"), labelRef: e$12, buttonRef: f2, optionsRef: v2, disabled: computed(() => o2.disabled), options: s3, searchQuery: m, activeOptionIndex: p2, activationTrigger: a2, closeListbox() {
    o2.disabled || n2.value !== 1 && (n2.value = 1, p2.value = null);
  }, openListbox() {
    o2.disabled || n2.value !== 0 && (n2.value = 0);
  }, goToOption(t2, i2, l2) {
    if (o2.disabled || n2.value === 1) return;
    let d2 = u2(), O2 = f$1(t2 === c.Specific ? { focus: c.Specific, id: i2 } : { focus: t2 }, { resolveItems: () => d2.options, resolveActiveIndex: () => d2.activeOptionIndex, resolveId: (h2) => h2.id, resolveDisabled: (h2) => h2.dataRef.disabled });
    m.value = "", p2.value = O2, a2.value = l2 != null ? l2 : 1, s3.value = d2.options;
  }, search(t2) {
    if (o2.disabled || n2.value === 1) return;
    let l2 = m.value !== "" ? 0 : 1;
    m.value += t2.toLowerCase();
    let O2 = (p2.value !== null ? s3.value.slice(p2.value + l2).concat(s3.value.slice(0, p2.value + l2)) : s3.value).find((I) => I.dataRef.textValue.startsWith(m.value) && !I.dataRef.disabled), h2 = O2 ? s3.value.indexOf(O2) : -1;
    h2 === -1 || h2 === p2.value || (p2.value = h2, a2.value = 1);
  }, clearSearch() {
    o2.disabled || n2.value !== 1 && m.value !== "" && (m.value = "");
  }, registerOption(t2, i2) {
    let l2 = u2((d2) => [...d2, { id: t2, dataRef: i2 }]);
    s3.value = l2.options, p2.value = l2.activeOptionIndex;
  }, unregisterOption(t2) {
    let i2 = u2((l2) => {
      let d2 = l2.findIndex((O2) => O2.id === t2);
      return d2 !== -1 && l2.splice(d2, 1), l2;
    });
    s3.value = i2.options, p2.value = i2.activeOptionIndex, a2.value = 1;
  }, theirOnChange(t2) {
    o2.disabled || L(t2);
  }, select(t2) {
    o2.disabled || L(u$4(D.value, { [0]: () => t2, [1]: () => {
      let i2 = toRaw(k.value.value).slice(), l2 = toRaw(t2), d2 = i2.findIndex((O2) => k.compare(l2, toRaw(O2)));
      return d2 === -1 ? i2.push(l2) : i2.splice(d2, 1), i2;
    } }));
  } };
  w([f2, v2], (t2, i2) => {
    var l2;
    k.closeListbox(), w$2(i2, h.Loose) || (t2.preventDefault(), (l2 = o$3(f2)) == null || l2.focus());
  }, computed(() => n2.value === 0)), provide($, k), t$1(computed(() => u$4(n2.value, { [0]: i.Open, [1]: i.Closed })));
  let C = computed(() => {
    var t2;
    return (t2 = o$3(f2)) == null ? void 0 : t2.closest("form");
  });
  return onMounted(() => {
    watch([C], () => {
      if (!C.value || o2.defaultValue === void 0) return;
      function t2() {
        k.theirOnChange(o2.defaultValue);
      }
      return C.value.addEventListener("reset", t2), () => {
        var i2;
        (i2 = C.value) == null || i2.removeEventListener("reset", t2);
      };
    }, { immediate: true });
  }), () => {
    let { name: t2, modelValue: i2, disabled: l2, form: d2, ...O2 } = o2, h2 = { open: n2.value === 0, disabled: l2, value: M.value };
    return h$1(Fragment, [...t2 != null && M.value != null ? e({ [t2]: M.value }).map(([I, Q]) => h$1(f$2, E({ features: u$1.Hidden, key: I, as: "input", type: "hidden", hidden: true, readOnly: true, form: d2, disabled: l2, name: I, value: Q }))) : [], A$1({ ourProps: {}, theirProps: { ...r2, ...T(O2, ["defaultValue", "onUpdate:modelValue", "horizontal", "multiple", "by"]) }, slot: h2, slots: b2, attrs: r2, name: "Listbox" })]);
  };
} });
defineComponent({ name: "ListboxLabel", props: { as: { type: [Object, String], default: "label" }, id: { type: String, default: null } }, setup(o2, { attrs: b2, slots: r2 }) {
  var f2;
  let w2 = (f2 = o2.id) != null ? f2 : `headlessui-listbox-label-${i$5()}`, n2 = A("ListboxLabel");
  function e2() {
    var v2;
    (v2 = o$3(n2.buttonRef)) == null || v2.focus({ preventScroll: true });
  }
  return () => {
    let v2 = { open: n2.listboxState.value === 0, disabled: n2.disabled.value }, { ...s3 } = o2, m = { id: w2, ref: n2.labelRef, onClick: e2 };
    return A$1({ ourProps: m, theirProps: s3, slot: v2, attrs: b2, slots: r2, name: "ListboxLabel" });
  };
} });
let je = defineComponent({ name: "ListboxButton", props: { as: { type: [Object, String], default: "button" }, id: { type: String, default: null } }, setup(o2, { attrs: b2, slots: r2, expose: w2 }) {
  var p2;
  let n2 = (p2 = o2.id) != null ? p2 : `headlessui-listbox-button-${i$5()}`, e2 = A("ListboxButton");
  w2({ el: e2.buttonRef, $el: e2.buttonRef });
  function f2(a2) {
    switch (a2.key) {
      case o$2.Space:
      case o$2.Enter:
      case o$2.ArrowDown:
        a2.preventDefault(), e2.openListbox(), nextTick(() => {
          var u2;
          (u2 = o$3(e2.optionsRef)) == null || u2.focus({ preventScroll: true }), e2.value.value || e2.goToOption(c.First);
        });
        break;
      case o$2.ArrowUp:
        a2.preventDefault(), e2.openListbox(), nextTick(() => {
          var u2;
          (u2 = o$3(e2.optionsRef)) == null || u2.focus({ preventScroll: true }), e2.value.value || e2.goToOption(c.Last);
        });
        break;
    }
  }
  function v2(a2) {
    switch (a2.key) {
      case o$2.Space:
        a2.preventDefault();
        break;
    }
  }
  function s$12(a2) {
    e2.disabled.value || (e2.listboxState.value === 0 ? (e2.closeListbox(), nextTick(() => {
      var u2;
      return (u2 = o$3(e2.buttonRef)) == null ? void 0 : u2.focus({ preventScroll: true });
    })) : (a2.preventDefault(), e2.openListbox(), me(() => {
      var u2;
      return (u2 = o$3(e2.optionsRef)) == null ? void 0 : u2.focus({ preventScroll: true });
    })));
  }
  let m = s2(computed(() => ({ as: o2.as, type: b2.type })), e2.buttonRef);
  return () => {
    var y2, L;
    let a2 = { open: e2.listboxState.value === 0, disabled: e2.disabled.value, value: e2.value.value }, { ...u2 } = o2, D = { ref: e2.buttonRef, id: n2, type: m.value, "aria-haspopup": "listbox", "aria-controls": (y2 = o$3(e2.optionsRef)) == null ? void 0 : y2.id, "aria-expanded": e2.listboxState.value === 0, "aria-labelledby": e2.labelRef.value ? [(L = o$3(e2.labelRef)) == null ? void 0 : L.id, n2].join(" ") : void 0, disabled: e2.disabled.value === true ? true : void 0, onKeydown: f2, onKeyup: v2, onClick: s$12 };
    return A$1({ ourProps: D, theirProps: u2, slot: a2, attrs: b2, slots: r2, name: "ListboxButton" });
  };
} }), Ae = defineComponent({ name: "ListboxOptions", props: { as: { type: [Object, String], default: "ul" }, static: { type: Boolean, default: false }, unmount: { type: Boolean, default: true }, id: { type: String, default: null } }, setup(o2, { attrs: b2, slots: r2, expose: w2 }) {
  var p2;
  let n2 = (p2 = o2.id) != null ? p2 : `headlessui-listbox-options-${i$5()}`, e2 = A("ListboxOptions"), f2 = ref(null);
  w2({ el: e2.optionsRef, $el: e2.optionsRef });
  function v2(a2) {
    switch (f2.value && clearTimeout(f2.value), a2.key) {
      case o$2.Space:
        if (e2.searchQuery.value !== "") return a2.preventDefault(), a2.stopPropagation(), e2.search(a2.key);
      case o$2.Enter:
        if (a2.preventDefault(), a2.stopPropagation(), e2.activeOptionIndex.value !== null) {
          let u2 = e2.options.value[e2.activeOptionIndex.value];
          e2.select(u2.dataRef.value);
        }
        e2.mode.value === 0 && (e2.closeListbox(), nextTick(() => {
          var u2;
          return (u2 = o$3(e2.buttonRef)) == null ? void 0 : u2.focus({ preventScroll: true });
        }));
        break;
      case u$4(e2.orientation.value, { vertical: o$2.ArrowDown, horizontal: o$2.ArrowRight }):
        return a2.preventDefault(), a2.stopPropagation(), e2.goToOption(c.Next);
      case u$4(e2.orientation.value, { vertical: o$2.ArrowUp, horizontal: o$2.ArrowLeft }):
        return a2.preventDefault(), a2.stopPropagation(), e2.goToOption(c.Previous);
      case o$2.Home:
      case o$2.PageUp:
        return a2.preventDefault(), a2.stopPropagation(), e2.goToOption(c.First);
      case o$2.End:
      case o$2.PageDown:
        return a2.preventDefault(), a2.stopPropagation(), e2.goToOption(c.Last);
      case o$2.Escape:
        a2.preventDefault(), a2.stopPropagation(), e2.closeListbox(), nextTick(() => {
          var u2;
          return (u2 = o$3(e2.buttonRef)) == null ? void 0 : u2.focus({ preventScroll: true });
        });
        break;
      case o$2.Tab:
        a2.preventDefault(), a2.stopPropagation();
        break;
      default:
        a2.key.length === 1 && (e2.search(a2.key), f2.value = setTimeout(() => e2.clearSearch(), 350));
        break;
    }
  }
  let s3 = l(), m = computed(() => s3 !== null ? (s3.value & i.Open) === i.Open : e2.listboxState.value === 0);
  return () => {
    var y2, L;
    let a2 = { open: e2.listboxState.value === 0 }, { ...u2 } = o2, D = { "aria-activedescendant": e2.activeOptionIndex.value === null || (y2 = e2.options.value[e2.activeOptionIndex.value]) == null ? void 0 : y2.id, "aria-multiselectable": e2.mode.value === 1 ? true : void 0, "aria-labelledby": (L = o$3(e2.buttonRef)) == null ? void 0 : L.id, "aria-orientation": e2.orientation.value, id: n2, onKeydown: v2, role: "listbox", tabIndex: 0, ref: e2.optionsRef };
    return A$1({ ourProps: D, theirProps: u2, slot: a2, attrs: b2, slots: r2, features: N.RenderStrategy | N.Static, visible: m.value, name: "ListboxOptions" });
  };
} }), Fe = defineComponent({ name: "ListboxOption", props: { as: { type: [Object, String], default: "li" }, value: { type: [Object, String, Number, Boolean] }, disabled: { type: Boolean, default: false }, id: { type: String, default: null } }, setup(o2, { slots: b2, attrs: r2, expose: w2 }) {
  var C;
  let n2 = (C = o2.id) != null ? C : `headlessui-listbox-option-${i$5()}`, e2 = A("ListboxOption"), f2 = ref(null);
  w2({ el: f2, $el: f2 });
  let v2 = computed(() => e2.activeOptionIndex.value !== null ? e2.options.value[e2.activeOptionIndex.value].id === n2 : false), s3 = computed(() => u$4(e2.mode.value, { [0]: () => e2.compare(toRaw(e2.value.value), toRaw(o2.value)), [1]: () => toRaw(e2.value.value).some((t2) => e2.compare(toRaw(t2), toRaw(o2.value))) })), m = computed(() => u$4(e2.mode.value, { [1]: () => {
    var i2;
    let t2 = toRaw(e2.value.value);
    return ((i2 = e2.options.value.find((l2) => t2.some((d2) => e2.compare(toRaw(d2), toRaw(l2.dataRef.value))))) == null ? void 0 : i2.id) === n2;
  }, [0]: () => s3.value })), p$1 = p(f2), a2 = computed(() => ({ disabled: o2.disabled, value: o2.value, get textValue() {
    return p$1();
  }, domRef: f2 }));
  onMounted(() => e2.registerOption(n2, a2)), onUnmounted(() => e2.unregisterOption(n2)), onMounted(() => {
    watch([e2.listboxState, s3], () => {
      e2.listboxState.value === 0 && s3.value && u$4(e2.mode.value, { [1]: () => {
        m.value && e2.goToOption(c.Specific, n2);
      }, [0]: () => {
        e2.goToOption(c.Specific, n2);
      } });
    }, { immediate: true });
  }), watchEffect(() => {
    e2.listboxState.value === 0 && v2.value && e2.activationTrigger.value !== 0 && nextTick(() => {
      var t2, i2;
      return (i2 = (t2 = o$3(f2)) == null ? void 0 : t2.scrollIntoView) == null ? void 0 : i2.call(t2, { block: "nearest" });
    });
  });
  function u2(t2) {
    if (o2.disabled) return t2.preventDefault();
    e2.select(o2.value), e2.mode.value === 0 && (e2.closeListbox(), nextTick(() => {
      var i2;
      return (i2 = o$3(e2.buttonRef)) == null ? void 0 : i2.focus({ preventScroll: true });
    }));
  }
  function D() {
    if (o2.disabled) return e2.goToOption(c.Nothing);
    e2.goToOption(c.Specific, n2);
  }
  let y2 = u$2();
  function L(t2) {
    y2.update(t2);
  }
  function M(t2) {
    y2.wasMoved(t2) && (o2.disabled || v2.value || e2.goToOption(c.Specific, n2, 0));
  }
  function k(t2) {
    y2.wasMoved(t2) && (o2.disabled || v2.value && e2.goToOption(c.Nothing));
  }
  return () => {
    let { disabled: t2 } = o2, i2 = { active: v2.value, selected: s3.value, disabled: t2 }, { value: l2, disabled: d2, ...O2 } = o2, h2 = { id: n2, ref: f2, role: "option", tabIndex: t2 === true ? void 0 : -1, "aria-disabled": t2 === true ? true : void 0, "aria-selected": s3.value, disabled: void 0, onClick: u2, onFocus: D, onPointerenter: L, onMouseenter: L, onPointermove: M, onMousemove: M, onPointerleave: k, onMouseleave: k };
    return A$1({ ourProps: h2, theirProps: O2, slot: i2, attrs: r2, slots: b2, name: "ListboxOption" });
  };
} });
function getWindow(node) {
  if (node == null) {
    return void 0;
  }
  if (node.toString() !== "[object Window]") {
    var ownerDocument = node.ownerDocument;
    return ownerDocument ? ownerDocument.defaultView || void 0 : void 0;
  }
  return node;
}
function isElement(node) {
  var OwnElement = getWindow(node).Element;
  return node instanceof OwnElement || node instanceof Element;
}
function isHTMLElement(node) {
  var OwnElement = getWindow(node).HTMLElement;
  return node instanceof OwnElement || node instanceof HTMLElement;
}
function isShadowRoot(node) {
  if (typeof ShadowRoot === "undefined") {
    return false;
  }
  var OwnElement = getWindow(node).ShadowRoot;
  return node instanceof OwnElement || node instanceof ShadowRoot;
}
var max = Math.max;
var min = Math.min;
var round = Math.round;
function getUAString() {
  var uaData = (void 0).userAgentData;
  if (uaData != null && uaData.brands && Array.isArray(uaData.brands)) {
    return uaData.brands.map(function(item) {
      return item.brand + "/" + item.version;
    }).join(" ");
  }
  return (void 0).userAgent;
}
function isLayoutViewport() {
  return !/^((?!chrome|android).)*safari/i.test(getUAString());
}
function getBoundingClientRect(element, includeScale, isFixedStrategy) {
  if (includeScale === void 0) {
    includeScale = false;
  }
  if (isFixedStrategy === void 0) {
    isFixedStrategy = false;
  }
  var clientRect = element.getBoundingClientRect();
  var scaleX = 1;
  var scaleY = 1;
  if (includeScale && isHTMLElement(element)) {
    scaleX = element.offsetWidth > 0 ? round(clientRect.width) / element.offsetWidth || 1 : 1;
    scaleY = element.offsetHeight > 0 ? round(clientRect.height) / element.offsetHeight || 1 : 1;
  }
  var _ref = isElement(element) ? getWindow(element) : void 0, visualViewport = _ref.visualViewport;
  var addVisualOffsets = !isLayoutViewport() && isFixedStrategy;
  var x = (clientRect.left + (addVisualOffsets && visualViewport ? visualViewport.offsetLeft : 0)) / scaleX;
  var y2 = (clientRect.top + (addVisualOffsets && visualViewport ? visualViewport.offsetTop : 0)) / scaleY;
  var width = clientRect.width / scaleX;
  var height = clientRect.height / scaleY;
  return {
    width,
    height,
    top: y2,
    right: x + width,
    bottom: y2 + height,
    left: x,
    x,
    y: y2
  };
}
function getWindowScroll(node) {
  var win = getWindow(node);
  var scrollLeft = win.pageXOffset;
  var scrollTop = win.pageYOffset;
  return {
    scrollLeft,
    scrollTop
  };
}
function getHTMLElementScroll(element) {
  return {
    scrollLeft: element.scrollLeft,
    scrollTop: element.scrollTop
  };
}
function getNodeScroll(node) {
  if (node === getWindow(node) || !isHTMLElement(node)) {
    return getWindowScroll(node);
  } else {
    return getHTMLElementScroll(node);
  }
}
function getNodeName(element) {
  return element ? (element.nodeName || "").toLowerCase() : null;
}
function getDocumentElement(element) {
  return ((isElement(element) ? element.ownerDocument : (
    // $FlowFixMe[prop-missing]
    element.document
  )) || (void 0).document).documentElement;
}
function getWindowScrollBarX(element) {
  return getBoundingClientRect(getDocumentElement(element)).left + getWindowScroll(element).scrollLeft;
}
function getComputedStyle(element) {
  return getWindow(element).getComputedStyle(element);
}
function isScrollParent(element) {
  var _getComputedStyle = getComputedStyle(element), overflow = _getComputedStyle.overflow, overflowX = _getComputedStyle.overflowX, overflowY = _getComputedStyle.overflowY;
  return /auto|scroll|overlay|hidden/.test(overflow + overflowY + overflowX);
}
function isElementScaled(element) {
  var rect = element.getBoundingClientRect();
  var scaleX = round(rect.width) / element.offsetWidth || 1;
  var scaleY = round(rect.height) / element.offsetHeight || 1;
  return scaleX !== 1 || scaleY !== 1;
}
function getCompositeRect(elementOrVirtualElement, offsetParent, isFixed) {
  if (isFixed === void 0) {
    isFixed = false;
  }
  var isOffsetParentAnElement = isHTMLElement(offsetParent);
  var offsetParentIsScaled = isHTMLElement(offsetParent) && isElementScaled(offsetParent);
  var documentElement = getDocumentElement(offsetParent);
  var rect = getBoundingClientRect(elementOrVirtualElement, offsetParentIsScaled, isFixed);
  var scroll = {
    scrollLeft: 0,
    scrollTop: 0
  };
  var offsets = {
    x: 0,
    y: 0
  };
  if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
    if (getNodeName(offsetParent) !== "body" || // https://github.com/popperjs/popper-core/issues/1078
    isScrollParent(documentElement)) {
      scroll = getNodeScroll(offsetParent);
    }
    if (isHTMLElement(offsetParent)) {
      offsets = getBoundingClientRect(offsetParent, true);
      offsets.x += offsetParent.clientLeft;
      offsets.y += offsetParent.clientTop;
    } else if (documentElement) {
      offsets.x = getWindowScrollBarX(documentElement);
    }
  }
  return {
    x: rect.left + scroll.scrollLeft - offsets.x,
    y: rect.top + scroll.scrollTop - offsets.y,
    width: rect.width,
    height: rect.height
  };
}
function getLayoutRect(element) {
  var clientRect = getBoundingClientRect(element);
  var width = element.offsetWidth;
  var height = element.offsetHeight;
  if (Math.abs(clientRect.width - width) <= 1) {
    width = clientRect.width;
  }
  if (Math.abs(clientRect.height - height) <= 1) {
    height = clientRect.height;
  }
  return {
    x: element.offsetLeft,
    y: element.offsetTop,
    width,
    height
  };
}
function getParentNode(element) {
  if (getNodeName(element) === "html") {
    return element;
  }
  return (
    // this is a quicker (but less type safe) way to save quite some bytes from the bundle
    // $FlowFixMe[incompatible-return]
    // $FlowFixMe[prop-missing]
    element.assignedSlot || // step into the shadow DOM of the parent of a slotted node
    element.parentNode || // DOM Element detected
    (isShadowRoot(element) ? element.host : null) || // ShadowRoot detected
    // $FlowFixMe[incompatible-call]: HTMLElement is a Node
    getDocumentElement(element)
  );
}
function getScrollParent(node) {
  if (["html", "body", "#document"].indexOf(getNodeName(node)) >= 0) {
    return node.ownerDocument.body;
  }
  if (isHTMLElement(node) && isScrollParent(node)) {
    return node;
  }
  return getScrollParent(getParentNode(node));
}
function listScrollParents(element, list) {
  var _element$ownerDocumen;
  if (list === void 0) {
    list = [];
  }
  var scrollParent = getScrollParent(element);
  var isBody = scrollParent === ((_element$ownerDocumen = element.ownerDocument) == null ? void 0 : _element$ownerDocumen.body);
  var win = getWindow(scrollParent);
  var target = isBody ? [win].concat(win.visualViewport || [], isScrollParent(scrollParent) ? scrollParent : []) : scrollParent;
  var updatedList = list.concat(target);
  return isBody ? updatedList : (
    // $FlowFixMe[incompatible-call]: isBody tells us target will be an HTMLElement here
    updatedList.concat(listScrollParents(getParentNode(target)))
  );
}
function isTableElement(element) {
  return ["table", "td", "th"].indexOf(getNodeName(element)) >= 0;
}
function getTrueOffsetParent(element) {
  if (!isHTMLElement(element) || // https://github.com/popperjs/popper-core/issues/837
  getComputedStyle(element).position === "fixed") {
    return null;
  }
  return element.offsetParent;
}
function getContainingBlock(element) {
  var isFirefox = /firefox/i.test(getUAString());
  var isIE = /Trident/i.test(getUAString());
  if (isIE && isHTMLElement(element)) {
    var elementCss = getComputedStyle(element);
    if (elementCss.position === "fixed") {
      return null;
    }
  }
  var currentNode = getParentNode(element);
  if (isShadowRoot(currentNode)) {
    currentNode = currentNode.host;
  }
  while (isHTMLElement(currentNode) && ["html", "body"].indexOf(getNodeName(currentNode)) < 0) {
    var css = getComputedStyle(currentNode);
    if (css.transform !== "none" || css.perspective !== "none" || css.contain === "paint" || ["transform", "perspective"].indexOf(css.willChange) !== -1 || isFirefox && css.willChange === "filter" || isFirefox && css.filter && css.filter !== "none") {
      return currentNode;
    } else {
      currentNode = currentNode.parentNode;
    }
  }
  return null;
}
function getOffsetParent(element) {
  var window = getWindow(element);
  var offsetParent = getTrueOffsetParent(element);
  while (offsetParent && isTableElement(offsetParent) && getComputedStyle(offsetParent).position === "static") {
    offsetParent = getTrueOffsetParent(offsetParent);
  }
  if (offsetParent && (getNodeName(offsetParent) === "html" || getNodeName(offsetParent) === "body" && getComputedStyle(offsetParent).position === "static")) {
    return window;
  }
  return offsetParent || getContainingBlock(element) || window;
}
var top = "top";
var bottom = "bottom";
var right = "right";
var left = "left";
var auto = "auto";
var basePlacements = [top, bottom, right, left];
var start = "start";
var end = "end";
var clippingParents = "clippingParents";
var viewport = "viewport";
var popper = "popper";
var reference = "reference";
var variationPlacements = /* @__PURE__ */ basePlacements.reduce(function(acc, placement) {
  return acc.concat([placement + "-" + start, placement + "-" + end]);
}, []);
var placements = /* @__PURE__ */ [].concat(basePlacements, [auto]).reduce(function(acc, placement) {
  return acc.concat([placement, placement + "-" + start, placement + "-" + end]);
}, []);
var beforeRead = "beforeRead";
var read = "read";
var afterRead = "afterRead";
var beforeMain = "beforeMain";
var main = "main";
var afterMain = "afterMain";
var beforeWrite = "beforeWrite";
var write = "write";
var afterWrite = "afterWrite";
var modifierPhases = [beforeRead, read, afterRead, beforeMain, main, afterMain, beforeWrite, write, afterWrite];
function order(modifiers) {
  var map = /* @__PURE__ */ new Map();
  var visited = /* @__PURE__ */ new Set();
  var result = [];
  modifiers.forEach(function(modifier) {
    map.set(modifier.name, modifier);
  });
  function sort(modifier) {
    visited.add(modifier.name);
    var requires = [].concat(modifier.requires || [], modifier.requiresIfExists || []);
    requires.forEach(function(dep) {
      if (!visited.has(dep)) {
        var depModifier = map.get(dep);
        if (depModifier) {
          sort(depModifier);
        }
      }
    });
    result.push(modifier);
  }
  modifiers.forEach(function(modifier) {
    if (!visited.has(modifier.name)) {
      sort(modifier);
    }
  });
  return result;
}
function orderModifiers(modifiers) {
  var orderedModifiers = order(modifiers);
  return modifierPhases.reduce(function(acc, phase) {
    return acc.concat(orderedModifiers.filter(function(modifier) {
      return modifier.phase === phase;
    }));
  }, []);
}
function debounce(fn2) {
  var pending;
  return function() {
    if (!pending) {
      pending = new Promise(function(resolve) {
        Promise.resolve().then(function() {
          pending = void 0;
          resolve(fn2());
        });
      });
    }
    return pending;
  };
}
function mergeByName(modifiers) {
  var merged = modifiers.reduce(function(merged2, current) {
    var existing = merged2[current.name];
    merged2[current.name] = existing ? Object.assign({}, existing, current, {
      options: Object.assign({}, existing.options, current.options),
      data: Object.assign({}, existing.data, current.data)
    }) : current;
    return merged2;
  }, {});
  return Object.keys(merged).map(function(key) {
    return merged[key];
  });
}
function getViewportRect(element, strategy) {
  var win = getWindow(element);
  var html = getDocumentElement(element);
  var visualViewport = win.visualViewport;
  var width = html.clientWidth;
  var height = html.clientHeight;
  var x = 0;
  var y2 = 0;
  if (visualViewport) {
    width = visualViewport.width;
    height = visualViewport.height;
    var layoutViewport = isLayoutViewport();
    if (layoutViewport || !layoutViewport && strategy === "fixed") {
      x = visualViewport.offsetLeft;
      y2 = visualViewport.offsetTop;
    }
  }
  return {
    width,
    height,
    x: x + getWindowScrollBarX(element),
    y: y2
  };
}
function getDocumentRect(element) {
  var _element$ownerDocumen;
  var html = getDocumentElement(element);
  var winScroll = getWindowScroll(element);
  var body = (_element$ownerDocumen = element.ownerDocument) == null ? void 0 : _element$ownerDocumen.body;
  var width = max(html.scrollWidth, html.clientWidth, body ? body.scrollWidth : 0, body ? body.clientWidth : 0);
  var height = max(html.scrollHeight, html.clientHeight, body ? body.scrollHeight : 0, body ? body.clientHeight : 0);
  var x = -winScroll.scrollLeft + getWindowScrollBarX(element);
  var y2 = -winScroll.scrollTop;
  if (getComputedStyle(body || html).direction === "rtl") {
    x += max(html.clientWidth, body ? body.clientWidth : 0) - width;
  }
  return {
    width,
    height,
    x,
    y: y2
  };
}
function contains(parent, child) {
  var rootNode = child.getRootNode && child.getRootNode();
  if (parent.contains(child)) {
    return true;
  } else if (rootNode && isShadowRoot(rootNode)) {
    var next = child;
    do {
      if (next && parent.isSameNode(next)) {
        return true;
      }
      next = next.parentNode || next.host;
    } while (next);
  }
  return false;
}
function rectToClientRect(rect) {
  return Object.assign({}, rect, {
    left: rect.x,
    top: rect.y,
    right: rect.x + rect.width,
    bottom: rect.y + rect.height
  });
}
function getInnerBoundingClientRect(element, strategy) {
  var rect = getBoundingClientRect(element, false, strategy === "fixed");
  rect.top = rect.top + element.clientTop;
  rect.left = rect.left + element.clientLeft;
  rect.bottom = rect.top + element.clientHeight;
  rect.right = rect.left + element.clientWidth;
  rect.width = element.clientWidth;
  rect.height = element.clientHeight;
  rect.x = rect.left;
  rect.y = rect.top;
  return rect;
}
function getClientRectFromMixedType(element, clippingParent, strategy) {
  return clippingParent === viewport ? rectToClientRect(getViewportRect(element, strategy)) : isElement(clippingParent) ? getInnerBoundingClientRect(clippingParent, strategy) : rectToClientRect(getDocumentRect(getDocumentElement(element)));
}
function getClippingParents(element) {
  var clippingParents2 = listScrollParents(getParentNode(element));
  var canEscapeClipping = ["absolute", "fixed"].indexOf(getComputedStyle(element).position) >= 0;
  var clipperElement = canEscapeClipping && isHTMLElement(element) ? getOffsetParent(element) : element;
  if (!isElement(clipperElement)) {
    return [];
  }
  return clippingParents2.filter(function(clippingParent) {
    return isElement(clippingParent) && contains(clippingParent, clipperElement) && getNodeName(clippingParent) !== "body";
  });
}
function getClippingRect(element, boundary, rootBoundary, strategy) {
  var mainClippingParents = boundary === "clippingParents" ? getClippingParents(element) : [].concat(boundary);
  var clippingParents2 = [].concat(mainClippingParents, [rootBoundary]);
  var firstClippingParent = clippingParents2[0];
  var clippingRect = clippingParents2.reduce(function(accRect, clippingParent) {
    var rect = getClientRectFromMixedType(element, clippingParent, strategy);
    accRect.top = max(rect.top, accRect.top);
    accRect.right = min(rect.right, accRect.right);
    accRect.bottom = min(rect.bottom, accRect.bottom);
    accRect.left = max(rect.left, accRect.left);
    return accRect;
  }, getClientRectFromMixedType(element, firstClippingParent, strategy));
  clippingRect.width = clippingRect.right - clippingRect.left;
  clippingRect.height = clippingRect.bottom - clippingRect.top;
  clippingRect.x = clippingRect.left;
  clippingRect.y = clippingRect.top;
  return clippingRect;
}
function getBasePlacement(placement) {
  return placement.split("-")[0];
}
function getVariation(placement) {
  return placement.split("-")[1];
}
function getMainAxisFromPlacement(placement) {
  return ["top", "bottom"].indexOf(placement) >= 0 ? "x" : "y";
}
function computeOffsets(_ref) {
  var reference2 = _ref.reference, element = _ref.element, placement = _ref.placement;
  var basePlacement = placement ? getBasePlacement(placement) : null;
  var variation = placement ? getVariation(placement) : null;
  var commonX = reference2.x + reference2.width / 2 - element.width / 2;
  var commonY = reference2.y + reference2.height / 2 - element.height / 2;
  var offsets;
  switch (basePlacement) {
    case top:
      offsets = {
        x: commonX,
        y: reference2.y - element.height
      };
      break;
    case bottom:
      offsets = {
        x: commonX,
        y: reference2.y + reference2.height
      };
      break;
    case right:
      offsets = {
        x: reference2.x + reference2.width,
        y: commonY
      };
      break;
    case left:
      offsets = {
        x: reference2.x - element.width,
        y: commonY
      };
      break;
    default:
      offsets = {
        x: reference2.x,
        y: reference2.y
      };
  }
  var mainAxis = basePlacement ? getMainAxisFromPlacement(basePlacement) : null;
  if (mainAxis != null) {
    var len = mainAxis === "y" ? "height" : "width";
    switch (variation) {
      case start:
        offsets[mainAxis] = offsets[mainAxis] - (reference2[len] / 2 - element[len] / 2);
        break;
      case end:
        offsets[mainAxis] = offsets[mainAxis] + (reference2[len] / 2 - element[len] / 2);
        break;
    }
  }
  return offsets;
}
function getFreshSideObject() {
  return {
    top: 0,
    right: 0,
    bottom: 0,
    left: 0
  };
}
function mergePaddingObject(paddingObject) {
  return Object.assign({}, getFreshSideObject(), paddingObject);
}
function expandToHashMap(value, keys) {
  return keys.reduce(function(hashMap, key) {
    hashMap[key] = value;
    return hashMap;
  }, {});
}
function detectOverflow(state, options) {
  if (options === void 0) {
    options = {};
  }
  var _options = options, _options$placement = _options.placement, placement = _options$placement === void 0 ? state.placement : _options$placement, _options$strategy = _options.strategy, strategy = _options$strategy === void 0 ? state.strategy : _options$strategy, _options$boundary = _options.boundary, boundary = _options$boundary === void 0 ? clippingParents : _options$boundary, _options$rootBoundary = _options.rootBoundary, rootBoundary = _options$rootBoundary === void 0 ? viewport : _options$rootBoundary, _options$elementConte = _options.elementContext, elementContext = _options$elementConte === void 0 ? popper : _options$elementConte, _options$altBoundary = _options.altBoundary, altBoundary = _options$altBoundary === void 0 ? false : _options$altBoundary, _options$padding = _options.padding, padding = _options$padding === void 0 ? 0 : _options$padding;
  var paddingObject = mergePaddingObject(typeof padding !== "number" ? padding : expandToHashMap(padding, basePlacements));
  var altContext = elementContext === popper ? reference : popper;
  var popperRect = state.rects.popper;
  var element = state.elements[altBoundary ? altContext : elementContext];
  var clippingClientRect = getClippingRect(isElement(element) ? element : element.contextElement || getDocumentElement(state.elements.popper), boundary, rootBoundary, strategy);
  var referenceClientRect = getBoundingClientRect(state.elements.reference);
  var popperOffsets2 = computeOffsets({
    reference: referenceClientRect,
    element: popperRect,
    placement
  });
  var popperClientRect = rectToClientRect(Object.assign({}, popperRect, popperOffsets2));
  var elementClientRect = elementContext === popper ? popperClientRect : referenceClientRect;
  var overflowOffsets = {
    top: clippingClientRect.top - elementClientRect.top + paddingObject.top,
    bottom: elementClientRect.bottom - clippingClientRect.bottom + paddingObject.bottom,
    left: clippingClientRect.left - elementClientRect.left + paddingObject.left,
    right: elementClientRect.right - clippingClientRect.right + paddingObject.right
  };
  var offsetData = state.modifiersData.offset;
  if (elementContext === popper && offsetData) {
    var offset2 = offsetData[placement];
    Object.keys(overflowOffsets).forEach(function(key) {
      var multiply = [right, bottom].indexOf(key) >= 0 ? 1 : -1;
      var axis = [top, bottom].indexOf(key) >= 0 ? "y" : "x";
      overflowOffsets[key] += offset2[axis] * multiply;
    });
  }
  return overflowOffsets;
}
var DEFAULT_OPTIONS = {
  placement: "bottom",
  modifiers: [],
  strategy: "absolute"
};
function areValidElements() {
  for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
    args[_key] = arguments[_key];
  }
  return !args.some(function(element) {
    return !(element && typeof element.getBoundingClientRect === "function");
  });
}
function popperGenerator(generatorOptions) {
  if (generatorOptions === void 0) {
    generatorOptions = {};
  }
  var _generatorOptions = generatorOptions, _generatorOptions$def = _generatorOptions.defaultModifiers, defaultModifiers2 = _generatorOptions$def === void 0 ? [] : _generatorOptions$def, _generatorOptions$def2 = _generatorOptions.defaultOptions, defaultOptions = _generatorOptions$def2 === void 0 ? DEFAULT_OPTIONS : _generatorOptions$def2;
  return function createPopper(reference2, popper2, options) {
    if (options === void 0) {
      options = defaultOptions;
    }
    var state = {
      placement: "bottom",
      orderedModifiers: [],
      options: Object.assign({}, DEFAULT_OPTIONS, defaultOptions),
      modifiersData: {},
      elements: {
        reference: reference2,
        popper: popper2
      },
      attributes: {},
      styles: {}
    };
    var effectCleanupFns = [];
    var isDestroyed = false;
    var instance = {
      state,
      setOptions: function setOptions(setOptionsAction) {
        var options2 = typeof setOptionsAction === "function" ? setOptionsAction(state.options) : setOptionsAction;
        cleanupModifierEffects();
        state.options = Object.assign({}, defaultOptions, state.options, options2);
        state.scrollParents = {
          reference: isElement(reference2) ? listScrollParents(reference2) : reference2.contextElement ? listScrollParents(reference2.contextElement) : [],
          popper: listScrollParents(popper2)
        };
        var orderedModifiers = orderModifiers(mergeByName([].concat(defaultModifiers2, state.options.modifiers)));
        state.orderedModifiers = orderedModifiers.filter(function(m) {
          return m.enabled;
        });
        runModifierEffects();
        return instance.update();
      },
      // Sync update – it will always be executed, even if not necessary. This
      // is useful for low frequency updates where sync behavior simplifies the
      // logic.
      // For high frequency updates (e.g. `resize` and `scroll` events), always
      // prefer the async Popper#update method
      forceUpdate: function forceUpdate() {
        if (isDestroyed) {
          return;
        }
        var _state$elements = state.elements, reference3 = _state$elements.reference, popper3 = _state$elements.popper;
        if (!areValidElements(reference3, popper3)) {
          return;
        }
        state.rects = {
          reference: getCompositeRect(reference3, getOffsetParent(popper3), state.options.strategy === "fixed"),
          popper: getLayoutRect(popper3)
        };
        state.reset = false;
        state.placement = state.options.placement;
        state.orderedModifiers.forEach(function(modifier) {
          return state.modifiersData[modifier.name] = Object.assign({}, modifier.data);
        });
        for (var index = 0; index < state.orderedModifiers.length; index++) {
          if (state.reset === true) {
            state.reset = false;
            index = -1;
            continue;
          }
          var _state$orderedModifie = state.orderedModifiers[index], fn2 = _state$orderedModifie.fn, _state$orderedModifie2 = _state$orderedModifie.options, _options = _state$orderedModifie2 === void 0 ? {} : _state$orderedModifie2, name = _state$orderedModifie.name;
          if (typeof fn2 === "function") {
            state = fn2({
              state,
              options: _options,
              name,
              instance
            }) || state;
          }
        }
      },
      // Async and optimistically optimized update – it will not be executed if
      // not necessary (debounced to run at most once-per-tick)
      update: debounce(function() {
        return new Promise(function(resolve) {
          instance.forceUpdate();
          resolve(state);
        });
      }),
      destroy: function destroy() {
        cleanupModifierEffects();
        isDestroyed = true;
      }
    };
    if (!areValidElements(reference2, popper2)) {
      return instance;
    }
    instance.setOptions(options).then(function(state2) {
      if (!isDestroyed && options.onFirstUpdate) {
        options.onFirstUpdate(state2);
      }
    });
    function runModifierEffects() {
      state.orderedModifiers.forEach(function(_ref) {
        var name = _ref.name, _ref$options = _ref.options, options2 = _ref$options === void 0 ? {} : _ref$options, effect2 = _ref.effect;
        if (typeof effect2 === "function") {
          var cleanupFn = effect2({
            state,
            name,
            instance,
            options: options2
          });
          var noopFn = function noopFn2() {
          };
          effectCleanupFns.push(cleanupFn || noopFn);
        }
      });
    }
    function cleanupModifierEffects() {
      effectCleanupFns.forEach(function(fn2) {
        return fn2();
      });
      effectCleanupFns = [];
    }
    return instance;
  };
}
var passive = {
  passive: true
};
function effect$2(_ref) {
  var state = _ref.state, instance = _ref.instance, options = _ref.options;
  var _options$scroll = options.scroll, scroll = _options$scroll === void 0 ? true : _options$scroll, _options$resize = options.resize, resize = _options$resize === void 0 ? true : _options$resize;
  var window = getWindow(state.elements.popper);
  var scrollParents = [].concat(state.scrollParents.reference, state.scrollParents.popper);
  if (scroll) {
    scrollParents.forEach(function(scrollParent) {
      scrollParent.addEventListener("scroll", instance.update, passive);
    });
  }
  if (resize) {
    window.addEventListener("resize", instance.update, passive);
  }
  return function() {
    if (scroll) {
      scrollParents.forEach(function(scrollParent) {
        scrollParent.removeEventListener("scroll", instance.update, passive);
      });
    }
    if (resize) {
      window.removeEventListener("resize", instance.update, passive);
    }
  };
}
const eventListeners = {
  name: "eventListeners",
  enabled: true,
  phase: "write",
  fn: function fn() {
  },
  effect: effect$2,
  data: {}
};
function popperOffsets(_ref) {
  var state = _ref.state, name = _ref.name;
  state.modifiersData[name] = computeOffsets({
    reference: state.rects.reference,
    element: state.rects.popper,
    placement: state.placement
  });
}
const popperOffsets$1 = {
  name: "popperOffsets",
  enabled: true,
  phase: "read",
  fn: popperOffsets,
  data: {}
};
var unsetSides = {
  top: "auto",
  right: "auto",
  bottom: "auto",
  left: "auto"
};
function roundOffsetsByDPR(_ref, win) {
  var x = _ref.x, y2 = _ref.y;
  var dpr = win.devicePixelRatio || 1;
  return {
    x: round(x * dpr) / dpr || 0,
    y: round(y2 * dpr) / dpr || 0
  };
}
function mapToStyles(_ref2) {
  var _Object$assign2;
  var popper2 = _ref2.popper, popperRect = _ref2.popperRect, placement = _ref2.placement, variation = _ref2.variation, offsets = _ref2.offsets, position = _ref2.position, gpuAcceleration = _ref2.gpuAcceleration, adaptive = _ref2.adaptive, roundOffsets = _ref2.roundOffsets, isFixed = _ref2.isFixed;
  var _offsets$x = offsets.x, x = _offsets$x === void 0 ? 0 : _offsets$x, _offsets$y = offsets.y, y2 = _offsets$y === void 0 ? 0 : _offsets$y;
  var _ref3 = typeof roundOffsets === "function" ? roundOffsets({
    x,
    y: y2
  }) : {
    x,
    y: y2
  };
  x = _ref3.x;
  y2 = _ref3.y;
  var hasX = offsets.hasOwnProperty("x");
  var hasY = offsets.hasOwnProperty("y");
  var sideX = left;
  var sideY = top;
  var win = void 0;
  if (adaptive) {
    var offsetParent = getOffsetParent(popper2);
    var heightProp = "clientHeight";
    var widthProp = "clientWidth";
    if (offsetParent === getWindow(popper2)) {
      offsetParent = getDocumentElement(popper2);
      if (getComputedStyle(offsetParent).position !== "static" && position === "absolute") {
        heightProp = "scrollHeight";
        widthProp = "scrollWidth";
      }
    }
    offsetParent = offsetParent;
    if (placement === top || (placement === left || placement === right) && variation === end) {
      sideY = bottom;
      var offsetY = isFixed && offsetParent === win && win.visualViewport ? win.visualViewport.height : (
        // $FlowFixMe[prop-missing]
        offsetParent[heightProp]
      );
      y2 -= offsetY - popperRect.height;
      y2 *= gpuAcceleration ? 1 : -1;
    }
    if (placement === left || (placement === top || placement === bottom) && variation === end) {
      sideX = right;
      var offsetX = isFixed && offsetParent === win && win.visualViewport ? win.visualViewport.width : (
        // $FlowFixMe[prop-missing]
        offsetParent[widthProp]
      );
      x -= offsetX - popperRect.width;
      x *= gpuAcceleration ? 1 : -1;
    }
  }
  var commonStyles = Object.assign({
    position
  }, adaptive && unsetSides);
  var _ref4 = roundOffsets === true ? roundOffsetsByDPR({
    x,
    y: y2
  }, getWindow(popper2)) : {
    x,
    y: y2
  };
  x = _ref4.x;
  y2 = _ref4.y;
  if (gpuAcceleration) {
    var _Object$assign;
    return Object.assign({}, commonStyles, (_Object$assign = {}, _Object$assign[sideY] = hasY ? "0" : "", _Object$assign[sideX] = hasX ? "0" : "", _Object$assign.transform = (win.devicePixelRatio || 1) <= 1 ? "translate(" + x + "px, " + y2 + "px)" : "translate3d(" + x + "px, " + y2 + "px, 0)", _Object$assign));
  }
  return Object.assign({}, commonStyles, (_Object$assign2 = {}, _Object$assign2[sideY] = hasY ? y2 + "px" : "", _Object$assign2[sideX] = hasX ? x + "px" : "", _Object$assign2.transform = "", _Object$assign2));
}
function computeStyles(_ref5) {
  var state = _ref5.state, options = _ref5.options;
  var _options$gpuAccelerat = options.gpuAcceleration, gpuAcceleration = _options$gpuAccelerat === void 0 ? true : _options$gpuAccelerat, _options$adaptive = options.adaptive, adaptive = _options$adaptive === void 0 ? true : _options$adaptive, _options$roundOffsets = options.roundOffsets, roundOffsets = _options$roundOffsets === void 0 ? true : _options$roundOffsets;
  var commonStyles = {
    placement: getBasePlacement(state.placement),
    variation: getVariation(state.placement),
    popper: state.elements.popper,
    popperRect: state.rects.popper,
    gpuAcceleration,
    isFixed: state.options.strategy === "fixed"
  };
  if (state.modifiersData.popperOffsets != null) {
    state.styles.popper = Object.assign({}, state.styles.popper, mapToStyles(Object.assign({}, commonStyles, {
      offsets: state.modifiersData.popperOffsets,
      position: state.options.strategy,
      adaptive,
      roundOffsets
    })));
  }
  if (state.modifiersData.arrow != null) {
    state.styles.arrow = Object.assign({}, state.styles.arrow, mapToStyles(Object.assign({}, commonStyles, {
      offsets: state.modifiersData.arrow,
      position: "absolute",
      adaptive: false,
      roundOffsets
    })));
  }
  state.attributes.popper = Object.assign({}, state.attributes.popper, {
    "data-popper-placement": state.placement
  });
}
const computeStyles$1 = {
  name: "computeStyles",
  enabled: true,
  phase: "beforeWrite",
  fn: computeStyles,
  data: {}
};
function applyStyles(_ref) {
  var state = _ref.state;
  Object.keys(state.elements).forEach(function(name) {
    var style = state.styles[name] || {};
    var attributes = state.attributes[name] || {};
    var element = state.elements[name];
    if (!isHTMLElement(element) || !getNodeName(element)) {
      return;
    }
    Object.assign(element.style, style);
    Object.keys(attributes).forEach(function(name2) {
      var value = attributes[name2];
      if (value === false) {
        element.removeAttribute(name2);
      } else {
        element.setAttribute(name2, value === true ? "" : value);
      }
    });
  });
}
function effect$1(_ref2) {
  var state = _ref2.state;
  var initialStyles = {
    popper: {
      position: state.options.strategy,
      left: "0",
      top: "0",
      margin: "0"
    },
    arrow: {
      position: "absolute"
    },
    reference: {}
  };
  Object.assign(state.elements.popper.style, initialStyles.popper);
  state.styles = initialStyles;
  if (state.elements.arrow) {
    Object.assign(state.elements.arrow.style, initialStyles.arrow);
  }
  return function() {
    Object.keys(state.elements).forEach(function(name) {
      var element = state.elements[name];
      var attributes = state.attributes[name] || {};
      var styleProperties = Object.keys(state.styles.hasOwnProperty(name) ? state.styles[name] : initialStyles[name]);
      var style = styleProperties.reduce(function(style2, property) {
        style2[property] = "";
        return style2;
      }, {});
      if (!isHTMLElement(element) || !getNodeName(element)) {
        return;
      }
      Object.assign(element.style, style);
      Object.keys(attributes).forEach(function(attribute) {
        element.removeAttribute(attribute);
      });
    });
  };
}
const applyStyles$1 = {
  name: "applyStyles",
  enabled: true,
  phase: "write",
  fn: applyStyles,
  effect: effect$1,
  requires: ["computeStyles"]
};
var defaultModifiers = [eventListeners, popperOffsets$1, computeStyles$1, applyStyles$1];
var hash$1 = {
  left: "right",
  right: "left",
  bottom: "top",
  top: "bottom"
};
function getOppositePlacement(placement) {
  return placement.replace(/left|right|bottom|top/g, function(matched) {
    return hash$1[matched];
  });
}
var hash = {
  start: "end",
  end: "start"
};
function getOppositeVariationPlacement(placement) {
  return placement.replace(/start|end/g, function(matched) {
    return hash[matched];
  });
}
function computeAutoPlacement(state, options) {
  if (options === void 0) {
    options = {};
  }
  var _options = options, placement = _options.placement, boundary = _options.boundary, rootBoundary = _options.rootBoundary, padding = _options.padding, flipVariations = _options.flipVariations, _options$allowedAutoP = _options.allowedAutoPlacements, allowedAutoPlacements = _options$allowedAutoP === void 0 ? placements : _options$allowedAutoP;
  var variation = getVariation(placement);
  var placements$1 = variation ? flipVariations ? variationPlacements : variationPlacements.filter(function(placement2) {
    return getVariation(placement2) === variation;
  }) : basePlacements;
  var allowedPlacements = placements$1.filter(function(placement2) {
    return allowedAutoPlacements.indexOf(placement2) >= 0;
  });
  if (allowedPlacements.length === 0) {
    allowedPlacements = placements$1;
  }
  var overflows = allowedPlacements.reduce(function(acc, placement2) {
    acc[placement2] = detectOverflow(state, {
      placement: placement2,
      boundary,
      rootBoundary,
      padding
    })[getBasePlacement(placement2)];
    return acc;
  }, {});
  return Object.keys(overflows).sort(function(a2, b2) {
    return overflows[a2] - overflows[b2];
  });
}
function getExpandedFallbackPlacements(placement) {
  if (getBasePlacement(placement) === auto) {
    return [];
  }
  var oppositePlacement = getOppositePlacement(placement);
  return [getOppositeVariationPlacement(placement), oppositePlacement, getOppositeVariationPlacement(oppositePlacement)];
}
function flip(_ref) {
  var state = _ref.state, options = _ref.options, name = _ref.name;
  if (state.modifiersData[name]._skip) {
    return;
  }
  var _options$mainAxis = options.mainAxis, checkMainAxis = _options$mainAxis === void 0 ? true : _options$mainAxis, _options$altAxis = options.altAxis, checkAltAxis = _options$altAxis === void 0 ? true : _options$altAxis, specifiedFallbackPlacements = options.fallbackPlacements, padding = options.padding, boundary = options.boundary, rootBoundary = options.rootBoundary, altBoundary = options.altBoundary, _options$flipVariatio = options.flipVariations, flipVariations = _options$flipVariatio === void 0 ? true : _options$flipVariatio, allowedAutoPlacements = options.allowedAutoPlacements;
  var preferredPlacement = state.options.placement;
  var basePlacement = getBasePlacement(preferredPlacement);
  var isBasePlacement = basePlacement === preferredPlacement;
  var fallbackPlacements = specifiedFallbackPlacements || (isBasePlacement || !flipVariations ? [getOppositePlacement(preferredPlacement)] : getExpandedFallbackPlacements(preferredPlacement));
  var placements2 = [preferredPlacement].concat(fallbackPlacements).reduce(function(acc, placement2) {
    return acc.concat(getBasePlacement(placement2) === auto ? computeAutoPlacement(state, {
      placement: placement2,
      boundary,
      rootBoundary,
      padding,
      flipVariations,
      allowedAutoPlacements
    }) : placement2);
  }, []);
  var referenceRect = state.rects.reference;
  var popperRect = state.rects.popper;
  var checksMap = /* @__PURE__ */ new Map();
  var makeFallbackChecks = true;
  var firstFittingPlacement = placements2[0];
  for (var i2 = 0; i2 < placements2.length; i2++) {
    var placement = placements2[i2];
    var _basePlacement = getBasePlacement(placement);
    var isStartVariation = getVariation(placement) === start;
    var isVertical = [top, bottom].indexOf(_basePlacement) >= 0;
    var len = isVertical ? "width" : "height";
    var overflow = detectOverflow(state, {
      placement,
      boundary,
      rootBoundary,
      altBoundary,
      padding
    });
    var mainVariationSide = isVertical ? isStartVariation ? right : left : isStartVariation ? bottom : top;
    if (referenceRect[len] > popperRect[len]) {
      mainVariationSide = getOppositePlacement(mainVariationSide);
    }
    var altVariationSide = getOppositePlacement(mainVariationSide);
    var checks = [];
    if (checkMainAxis) {
      checks.push(overflow[_basePlacement] <= 0);
    }
    if (checkAltAxis) {
      checks.push(overflow[mainVariationSide] <= 0, overflow[altVariationSide] <= 0);
    }
    if (checks.every(function(check) {
      return check;
    })) {
      firstFittingPlacement = placement;
      makeFallbackChecks = false;
      break;
    }
    checksMap.set(placement, checks);
  }
  if (makeFallbackChecks) {
    var numberOfChecks = flipVariations ? 3 : 1;
    var _loop = function _loop2(_i2) {
      var fittingPlacement = placements2.find(function(placement2) {
        var checks2 = checksMap.get(placement2);
        if (checks2) {
          return checks2.slice(0, _i2).every(function(check) {
            return check;
          });
        }
      });
      if (fittingPlacement) {
        firstFittingPlacement = fittingPlacement;
        return "break";
      }
    };
    for (var _i = numberOfChecks; _i > 0; _i--) {
      var _ret = _loop(_i);
      if (_ret === "break") break;
    }
  }
  if (state.placement !== firstFittingPlacement) {
    state.modifiersData[name]._skip = true;
    state.placement = firstFittingPlacement;
    state.reset = true;
  }
}
const flip$1 = {
  name: "flip",
  enabled: true,
  phase: "main",
  fn: flip,
  requiresIfExists: ["offset"],
  data: {
    _skip: false
  }
};
function distanceAndSkiddingToXY(placement, rects, offset2) {
  var basePlacement = getBasePlacement(placement);
  var invertDistance = [left, top].indexOf(basePlacement) >= 0 ? -1 : 1;
  var _ref = typeof offset2 === "function" ? offset2(Object.assign({}, rects, {
    placement
  })) : offset2, skidding = _ref[0], distance = _ref[1];
  skidding = skidding || 0;
  distance = (distance || 0) * invertDistance;
  return [left, right].indexOf(basePlacement) >= 0 ? {
    x: distance,
    y: skidding
  } : {
    x: skidding,
    y: distance
  };
}
function offset(_ref2) {
  var state = _ref2.state, options = _ref2.options, name = _ref2.name;
  var _options$offset = options.offset, offset2 = _options$offset === void 0 ? [0, 0] : _options$offset;
  var data = placements.reduce(function(acc, placement) {
    acc[placement] = distanceAndSkiddingToXY(placement, state.rects, offset2);
    return acc;
  }, {});
  var _data$state$placement = data[state.placement], x = _data$state$placement.x, y2 = _data$state$placement.y;
  if (state.modifiersData.popperOffsets != null) {
    state.modifiersData.popperOffsets.x += x;
    state.modifiersData.popperOffsets.y += y2;
  }
  state.modifiersData[name] = data;
}
const offset$1 = {
  name: "offset",
  enabled: true,
  phase: "main",
  requires: ["popperOffsets"],
  fn: offset
};
function getAltAxis(axis) {
  return axis === "x" ? "y" : "x";
}
function within(min$1, value, max$1) {
  return max(min$1, min(value, max$1));
}
function withinMaxClamp(min2, value, max2) {
  var v2 = within(min2, value, max2);
  return v2 > max2 ? max2 : v2;
}
function preventOverflow(_ref) {
  var state = _ref.state, options = _ref.options, name = _ref.name;
  var _options$mainAxis = options.mainAxis, checkMainAxis = _options$mainAxis === void 0 ? true : _options$mainAxis, _options$altAxis = options.altAxis, checkAltAxis = _options$altAxis === void 0 ? false : _options$altAxis, boundary = options.boundary, rootBoundary = options.rootBoundary, altBoundary = options.altBoundary, padding = options.padding, _options$tether = options.tether, tether = _options$tether === void 0 ? true : _options$tether, _options$tetherOffset = options.tetherOffset, tetherOffset = _options$tetherOffset === void 0 ? 0 : _options$tetherOffset;
  var overflow = detectOverflow(state, {
    boundary,
    rootBoundary,
    padding,
    altBoundary
  });
  var basePlacement = getBasePlacement(state.placement);
  var variation = getVariation(state.placement);
  var isBasePlacement = !variation;
  var mainAxis = getMainAxisFromPlacement(basePlacement);
  var altAxis = getAltAxis(mainAxis);
  var popperOffsets2 = state.modifiersData.popperOffsets;
  var referenceRect = state.rects.reference;
  var popperRect = state.rects.popper;
  var tetherOffsetValue = typeof tetherOffset === "function" ? tetherOffset(Object.assign({}, state.rects, {
    placement: state.placement
  })) : tetherOffset;
  var normalizedTetherOffsetValue = typeof tetherOffsetValue === "number" ? {
    mainAxis: tetherOffsetValue,
    altAxis: tetherOffsetValue
  } : Object.assign({
    mainAxis: 0,
    altAxis: 0
  }, tetherOffsetValue);
  var offsetModifierState = state.modifiersData.offset ? state.modifiersData.offset[state.placement] : null;
  var data = {
    x: 0,
    y: 0
  };
  if (!popperOffsets2) {
    return;
  }
  if (checkMainAxis) {
    var _offsetModifierState$;
    var mainSide = mainAxis === "y" ? top : left;
    var altSide = mainAxis === "y" ? bottom : right;
    var len = mainAxis === "y" ? "height" : "width";
    var offset2 = popperOffsets2[mainAxis];
    var min$1 = offset2 + overflow[mainSide];
    var max$1 = offset2 - overflow[altSide];
    var additive = tether ? -popperRect[len] / 2 : 0;
    var minLen = variation === start ? referenceRect[len] : popperRect[len];
    var maxLen = variation === start ? -popperRect[len] : -referenceRect[len];
    var arrowElement = state.elements.arrow;
    var arrowRect = tether && arrowElement ? getLayoutRect(arrowElement) : {
      width: 0,
      height: 0
    };
    var arrowPaddingObject = state.modifiersData["arrow#persistent"] ? state.modifiersData["arrow#persistent"].padding : getFreshSideObject();
    var arrowPaddingMin = arrowPaddingObject[mainSide];
    var arrowPaddingMax = arrowPaddingObject[altSide];
    var arrowLen = within(0, referenceRect[len], arrowRect[len]);
    var minOffset = isBasePlacement ? referenceRect[len] / 2 - additive - arrowLen - arrowPaddingMin - normalizedTetherOffsetValue.mainAxis : minLen - arrowLen - arrowPaddingMin - normalizedTetherOffsetValue.mainAxis;
    var maxOffset = isBasePlacement ? -referenceRect[len] / 2 + additive + arrowLen + arrowPaddingMax + normalizedTetherOffsetValue.mainAxis : maxLen + arrowLen + arrowPaddingMax + normalizedTetherOffsetValue.mainAxis;
    var arrowOffsetParent = state.elements.arrow && getOffsetParent(state.elements.arrow);
    var clientOffset = arrowOffsetParent ? mainAxis === "y" ? arrowOffsetParent.clientTop || 0 : arrowOffsetParent.clientLeft || 0 : 0;
    var offsetModifierValue = (_offsetModifierState$ = offsetModifierState == null ? void 0 : offsetModifierState[mainAxis]) != null ? _offsetModifierState$ : 0;
    var tetherMin = offset2 + minOffset - offsetModifierValue - clientOffset;
    var tetherMax = offset2 + maxOffset - offsetModifierValue;
    var preventedOffset = within(tether ? min(min$1, tetherMin) : min$1, offset2, tether ? max(max$1, tetherMax) : max$1);
    popperOffsets2[mainAxis] = preventedOffset;
    data[mainAxis] = preventedOffset - offset2;
  }
  if (checkAltAxis) {
    var _offsetModifierState$2;
    var _mainSide = mainAxis === "x" ? top : left;
    var _altSide = mainAxis === "x" ? bottom : right;
    var _offset = popperOffsets2[altAxis];
    var _len = altAxis === "y" ? "height" : "width";
    var _min = _offset + overflow[_mainSide];
    var _max = _offset - overflow[_altSide];
    var isOriginSide = [top, left].indexOf(basePlacement) !== -1;
    var _offsetModifierValue = (_offsetModifierState$2 = offsetModifierState == null ? void 0 : offsetModifierState[altAxis]) != null ? _offsetModifierState$2 : 0;
    var _tetherMin = isOriginSide ? _min : _offset - referenceRect[_len] - popperRect[_len] - _offsetModifierValue + normalizedTetherOffsetValue.altAxis;
    var _tetherMax = isOriginSide ? _offset + referenceRect[_len] + popperRect[_len] - _offsetModifierValue - normalizedTetherOffsetValue.altAxis : _max;
    var _preventedOffset = tether && isOriginSide ? withinMaxClamp(_tetherMin, _offset, _tetherMax) : within(tether ? _tetherMin : _min, _offset, tether ? _tetherMax : _max);
    popperOffsets2[altAxis] = _preventedOffset;
    data[altAxis] = _preventedOffset - _offset;
  }
  state.modifiersData[name] = data;
}
const preventOverflow$1 = {
  name: "preventOverflow",
  enabled: true,
  phase: "main",
  fn: preventOverflow,
  requiresIfExists: ["offset"]
};
var toPaddingObject = function toPaddingObject2(padding, state) {
  padding = typeof padding === "function" ? padding(Object.assign({}, state.rects, {
    placement: state.placement
  })) : padding;
  return mergePaddingObject(typeof padding !== "number" ? padding : expandToHashMap(padding, basePlacements));
};
function arrow(_ref) {
  var _state$modifiersData$;
  var state = _ref.state, name = _ref.name, options = _ref.options;
  var arrowElement = state.elements.arrow;
  var popperOffsets2 = state.modifiersData.popperOffsets;
  var basePlacement = getBasePlacement(state.placement);
  var axis = getMainAxisFromPlacement(basePlacement);
  var isVertical = [left, right].indexOf(basePlacement) >= 0;
  var len = isVertical ? "height" : "width";
  if (!arrowElement || !popperOffsets2) {
    return;
  }
  var paddingObject = toPaddingObject(options.padding, state);
  var arrowRect = getLayoutRect(arrowElement);
  var minProp = axis === "y" ? top : left;
  var maxProp = axis === "y" ? bottom : right;
  var endDiff = state.rects.reference[len] + state.rects.reference[axis] - popperOffsets2[axis] - state.rects.popper[len];
  var startDiff = popperOffsets2[axis] - state.rects.reference[axis];
  var arrowOffsetParent = getOffsetParent(arrowElement);
  var clientSize = arrowOffsetParent ? axis === "y" ? arrowOffsetParent.clientHeight || 0 : arrowOffsetParent.clientWidth || 0 : 0;
  var centerToReference = endDiff / 2 - startDiff / 2;
  var min2 = paddingObject[minProp];
  var max2 = clientSize - arrowRect[len] - paddingObject[maxProp];
  var center = clientSize / 2 - arrowRect[len] / 2 + centerToReference;
  var offset2 = within(min2, center, max2);
  var axisProp = axis;
  state.modifiersData[name] = (_state$modifiersData$ = {}, _state$modifiersData$[axisProp] = offset2, _state$modifiersData$.centerOffset = offset2 - center, _state$modifiersData$);
}
function effect(_ref2) {
  var state = _ref2.state, options = _ref2.options;
  var _options$element = options.element, arrowElement = _options$element === void 0 ? "[data-popper-arrow]" : _options$element;
  if (arrowElement == null) {
    return;
  }
  if (typeof arrowElement === "string") {
    arrowElement = state.elements.popper.querySelector(arrowElement);
    if (!arrowElement) {
      return;
    }
  }
  if (!contains(state.elements.popper, arrowElement)) {
    return;
  }
  state.elements.arrow = arrowElement;
}
const arrowModifier = {
  name: "arrow",
  enabled: true,
  phase: "main",
  fn: arrow,
  effect,
  requires: ["popperOffsets"],
  requiresIfExists: ["preventOverflow"]
};
popperGenerator({
  defaultModifiers: [...defaultModifiers, offset$1, flip$1, preventOverflow$1, computeStyles$1, eventListeners, arrowModifier]
});
function usePopper({
  locked = false,
  overflowPadding = 8,
  offsetDistance = 8,
  offsetSkid = 0,
  gpuAcceleration = true,
  adaptive = true,
  scroll = true,
  resize = true,
  arrow: arrow2 = false,
  placement,
  strategy
}, virtualReference) {
  const reference2 = ref(null);
  const popper2 = ref(null);
  const instance = ref(null);
  return [reference2, popper2, instance];
}
const config = mergeConfig(appConfig.ui.strategy, appConfig.ui.select, select);
const configMenu = mergeConfig(appConfig.ui.strategy, appConfig.ui.selectMenu, selectMenu);
const _sfc_main = defineComponent({
  components: {
    HCombobox: lt,
    HComboboxButton: nt,
    HComboboxOptions: ut,
    HComboboxOption: rt,
    HComboboxInput: it,
    HListbox: Ie,
    HListboxButton: je,
    HListboxOptions: Ae,
    HListboxOption: Fe,
    UIcon: __nuxt_component_0,
    UAvatar: __nuxt_component_1
  },
  inheritAttrs: false,
  props: {
    modelValue: {
      type: [String, Number, Object, Array, Boolean],
      default: ""
    },
    query: {
      type: String,
      default: null
    },
    by: {
      type: String,
      default: void 0
    },
    options: {
      type: Array,
      default: () => []
    },
    id: {
      type: String,
      default: null
    },
    name: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
    icon: {
      type: String,
      default: null
    },
    loadingIcon: {
      type: String,
      default: () => config.default.loadingIcon
    },
    leadingIcon: {
      type: String,
      default: null
    },
    trailingIcon: {
      type: String,
      default: () => config.default.trailingIcon
    },
    trailing: {
      type: Boolean,
      default: false
    },
    leading: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    selectedIcon: {
      type: String,
      default: () => configMenu.default.selectedIcon
    },
    disabled: {
      type: Boolean,
      default: false
    },
    multiple: {
      type: Boolean,
      default: false
    },
    searchable: {
      type: [Boolean, Function],
      default: false
    },
    searchablePlaceholder: {
      type: String,
      default: () => configMenu.default.searchablePlaceholder.label
    },
    searchableLazy: {
      type: Boolean,
      default: false
    },
    clearSearchOnClose: {
      type: Boolean,
      default: () => configMenu.default.clearSearchOnClose
    },
    debounce: {
      type: Number,
      default: 200
    },
    creatable: {
      type: Boolean,
      default: false
    },
    showCreateOptionWhen: {
      type: [String, Function],
      default: () => configMenu.default.showCreateOptionWhen
    },
    placeholder: {
      type: String,
      default: null
    },
    padded: {
      type: Boolean,
      default: true
    },
    size: {
      type: String,
      default: null,
      validator(value) {
        return Object.keys(config.size).includes(value);
      }
    },
    color: {
      type: String,
      default: () => config.default.color,
      validator(value) {
        return [...appConfig.ui.colors, ...Object.keys(config.color)].includes(value);
      }
    },
    variant: {
      type: String,
      default: () => config.default.variant,
      validator(value) {
        return [
          ...Object.keys(config.variant),
          ...Object.values(config.color).flatMap((value2) => Object.keys(value2))
        ].includes(value);
      }
    },
    optionAttribute: {
      type: String,
      default: "label"
    },
    valueAttribute: {
      type: String,
      default: null
    },
    searchAttributes: {
      type: Array,
      default: null
    },
    inputTargetForm: {
      type: String,
      default: null
    },
    popper: {
      type: Object,
      default: () => ({})
    },
    selectClass: {
      type: String,
      default: null
    },
    class: {
      type: [String, Object, Array],
      default: () => ""
    },
    ui: {
      type: Object,
      default: () => ({})
    },
    uiMenu: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ["update:modelValue", "update:query", "open", "close", "change"],
  setup(props, { emit, slots }) {
    const { ui, attrs } = useUI("select", toRef(props, "ui"), config, toRef(props, "class"));
    const { ui: uiMenu } = useUI("selectMenu", toRef(props, "uiMenu"), configMenu);
    const popper2 = computed(() => defu({}, props.popper, uiMenu.value.popper));
    const [trigger, container] = usePopper(popper2.value);
    const by = computed(() => {
      if (!props.by) return void 0;
      if (typeof props.by === "function") {
        return props.by;
      }
      const key = props.by;
      const hasDot = key.indexOf(".");
      if (hasDot > 0) {
        return (a2, z) => {
          return accessor(a2, key) === accessor(z, key);
        };
      }
      return key;
    });
    const { size: sizeButtonGroup, rounded } = useInjectButtonGroup({ ui, props });
    const { emitFormBlur, emitFormChange, inputId, color, size: sizeFormGroup, name } = useFormGroup(props, config);
    const size = computed(() => sizeButtonGroup.value ?? sizeFormGroup.value);
    const internalQuery = ref("");
    const query = computed({
      get() {
        return props.query ?? internalQuery.value;
      },
      set(value) {
        internalQuery.value = value;
        emit("update:query", value);
      }
    });
    const selected = computed(() => {
      function compareValues(value1, value2) {
        if (by.value && typeof by.value !== "function" && typeof value1 === "object" && typeof value2 === "object") {
          return isEqual(value1[by.value], value2[by.value]);
        }
        return isEqual(value1, value2);
      }
      function getValue(value) {
        if (props.valueAttribute) {
          return accessor(value, props.valueAttribute);
        }
        return value;
      }
      if (props.multiple) {
        const modelValue = props.modelValue;
        if (!Array.isArray(modelValue) || !modelValue.length) {
          return [];
        }
        return options.value.filter((option) => {
          const optionValue = getValue(option);
          return modelValue.some((value) => compareValues(value, optionValue));
        });
      }
      return options.value.find((option) => {
        const optionValue = getValue(option);
        return compareValues(optionValue, toRaw(props.modelValue));
      }) ?? props.modelValue;
    });
    const label = computed(() => {
      if (!props.modelValue) return null;
      if (Array.isArray(props.modelValue) && props.modelValue.length) {
        return `${props.modelValue.length} selected`;
      } else if (["string", "number"].includes(typeof props.modelValue)) {
        return props.valueAttribute ? accessor(selected.value, props.optionAttribute) : props.modelValue;
      }
      return accessor(props.modelValue, props.optionAttribute);
    });
    const selectClass = computed(() => {
      var _a, _b;
      const variant = ((_b = (_a = ui.value.color) == null ? void 0 : _a[color.value]) == null ? void 0 : _b[props.variant]) || ui.value.variant[props.variant];
      return twMerge(twJoin(
        ui.value.base,
        uiMenu.value.select,
        rounded.value,
        ui.value.size[size.value],
        ui.value.gap[size.value],
        props.padded ? ui.value.padding[size.value] : "p-0",
        variant == null ? void 0 : variant.replaceAll("{color}", color.value),
        (isLeading.value || slots.leading) && ui.value.leading.padding[size.value],
        (isTrailing.value || slots.trailing) && ui.value.trailing.padding[size.value]
      ), props.placeholder && (!props.modelValue || Array.isArray(props.modelValue) && !props.modelValue.length) && ui.value.placeholder, props.selectClass);
    });
    const isLeading = computed(() => {
      return props.icon && props.leading || props.icon && !props.trailing || props.loading && !props.trailing || props.leadingIcon;
    });
    const isTrailing = computed(() => {
      return props.icon && props.trailing || props.loading && props.trailing || props.trailingIcon;
    });
    const leadingIconName = computed(() => {
      if (props.loading) {
        return props.loadingIcon;
      }
      return props.leadingIcon || props.icon;
    });
    const trailingIconName = computed(() => {
      if (props.loading && !isLeading.value) {
        return props.loadingIcon;
      }
      return props.trailingIcon || props.icon;
    });
    const leadingWrapperIconClass = computed(() => {
      return twJoin(
        ui.value.icon.leading.wrapper,
        ui.value.icon.leading.pointer,
        ui.value.icon.leading.padding[size.value]
      );
    });
    const leadingIconClass = computed(() => {
      return twJoin(
        ui.value.icon.base,
        color.value && appConfig.ui.colors.includes(color.value) && ui.value.icon.color.replaceAll("{color}", color.value),
        ui.value.icon.size[size.value],
        props.loading && ui.value.icon.loading
      );
    });
    const trailingWrapperIconClass = computed(() => {
      return twJoin(
        ui.value.icon.trailing.wrapper,
        ui.value.icon.trailing.pointer,
        ui.value.icon.trailing.padding[size.value]
      );
    });
    const trailingIconClass = computed(() => {
      return twJoin(
        ui.value.icon.base,
        color.value && appConfig.ui.colors.includes(color.value) && ui.value.icon.color.replaceAll("{color}", color.value),
        ui.value.icon.size[size.value],
        props.loading && !isLeading.value && ui.value.icon.loading
      );
    });
    const debouncedSearch = props.searchable && typeof props.searchable === "function" ? useDebounceFn(props.searchable, props.debounce) : void 0;
    const options = computedAsync(async () => {
      if (debouncedSearch) {
        return await debouncedSearch(query.value);
      }
      return props.options || [];
    }, [], {
      lazy: props.searchableLazy
    });
    function escapeRegExp(string) {
      return string.replace(/[.*+?^${}()|[\]\\]/g, (match) => `\\${match}`);
    }
    function accessor(obj, key) {
      return get(obj, key);
    }
    const filteredOptions = computed(() => {
      if (!query.value || debouncedSearch) {
        return options.value;
      }
      const escapedQuery = escapeRegExp(query.value);
      return options.value.filter((option) => {
        var _a;
        return (((_a = props.searchAttributes) == null ? void 0 : _a.length) ? props.searchAttributes : [props.optionAttribute]).some((searchAttribute) => {
          if (["string", "number"].includes(typeof option)) {
            return String(option).search(new RegExp(escapedQuery, "i")) !== -1;
          }
          const child = get(option, searchAttribute);
          return child !== null && child !== void 0 && String(child).search(new RegExp(escapedQuery, "i")) !== -1;
        });
      });
    });
    const createOption = computed(() => {
      if (query.value === "") {
        return null;
      }
      if (props.showCreateOptionWhen === "empty" && filteredOptions.value.length) {
        return null;
      }
      if (props.showCreateOptionWhen === "always") {
        const existingOption = filteredOptions.value.find((option) => ["string", "number"].includes(typeof option) ? option === query.value : accessor(option, props.optionAttribute) === query.value);
        if (existingOption) {
          return null;
        }
      }
      if (typeof props.showCreateOptionWhen === "function") {
        if (!props.showCreateOptionWhen(query.value, filteredOptions.value)) {
          return null;
        }
      }
      return ["string", "number"].includes(typeof props.modelValue) ? query.value : { [props.optionAttribute]: query.value };
    });
    function clearOnClose() {
      if (props.clearSearchOnClose) {
        query.value = "";
      }
    }
    watch(container, (value) => {
      if (value) {
        emit("open");
      } else {
        clearOnClose();
        emit("close");
        emitFormBlur();
      }
    });
    function onUpdate(value) {
      if (toRaw(props.modelValue) === value) {
        return;
      }
      emit("update:modelValue", value);
      emit("change", value);
      emitFormChange();
    }
    function onQueryChange(event) {
      query.value = event.target.value;
    }
    s$2(() => useId());
    return {
      // eslint-disable-next-line vue/no-dupe-keys
      ui,
      // eslint-disable-next-line vue/no-dupe-keys
      uiMenu,
      attrs,
      // eslint-disable-next-line vue/no-dupe-keys
      name,
      inputId,
      // eslint-disable-next-line vue/no-dupe-keys
      popper: popper2,
      trigger,
      container,
      selected,
      label,
      accessor,
      isLeading,
      isTrailing,
      // eslint-disable-next-line vue/no-dupe-keys
      selectClass,
      leadingIconName,
      leadingIconClass,
      leadingWrapperIconClass,
      trailingIconName,
      trailingIconClass,
      trailingWrapperIconClass,
      filteredOptions,
      createOption,
      // eslint-disable-next-line vue/no-dupe-keys
      query,
      onUpdate,
      onQueryChange,
      // eslint-disable-next-line vue/no-dupe-keys
      by
    };
  }
});
function _sfc_ssrRender(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  const _component_UIcon = __nuxt_component_0;
  const _component_HComboboxInput = resolveComponent("HComboboxInput");
  const _component_UAvatar = __nuxt_component_1;
  ssrRenderVNode(_push, createVNode(resolveDynamicComponent(_ctx.searchable ? "HCombobox" : "HListbox"), mergeProps({
    by: _ctx.by,
    name: _ctx.name,
    "model-value": _ctx.multiple ? Array.isArray(_ctx.modelValue) ? _ctx.modelValue : [] : _ctx.modelValue,
    multiple: _ctx.multiple,
    disabled: _ctx.disabled,
    as: "div",
    class: _ctx.ui.wrapper,
    "onUpdate:modelValue": _ctx.onUpdate
  }, _attrs), {
    default: withCtx(({ open }, _push2, _parent2, _scopeId) => {
      if (_push2) {
        if (_ctx.required) {
          _push2(`<input${ssrRenderAttr("value", _ctx.modelValue)}${ssrIncludeBooleanAttr(_ctx.required) ? " required" : ""} class="${ssrRenderClass(_ctx.uiMenu.required)}"${ssrRenderAttr("form", _ctx.inputTargetForm)} tabindex="-1" aria-hidden="true"${_scopeId}>`);
        } else {
          _push2(`<!---->`);
        }
        ssrRenderVNode(_push2, createVNode(resolveDynamicComponent(_ctx.searchable ? "HComboboxButton" : "HListboxButton"), {
          ref: "trigger",
          as: "div",
          role: "button",
          class: _ctx.uiMenu.trigger
        }, {
          default: withCtx((_, _push3, _parent3, _scopeId2) => {
            if (_push3) {
              ssrRenderSlot(_ctx.$slots, "default", {
                open,
                disabled: _ctx.disabled,
                loading: _ctx.loading
              }, () => {
                _push3(`<button${ssrRenderAttrs(mergeProps({
                  id: _ctx.inputId,
                  class: _ctx.selectClass,
                  disabled: _ctx.disabled,
                  type: "button"
                }, _ctx.attrs))}${_scopeId2}>`);
                if (_ctx.isLeading && _ctx.leadingIconName || _ctx.$slots.leading) {
                  _push3(`<span class="${ssrRenderClass(_ctx.leadingWrapperIconClass)}"${_scopeId2}>`);
                  ssrRenderSlot(_ctx.$slots, "leading", {
                    selected: _ctx.selected,
                    disabled: _ctx.disabled,
                    loading: _ctx.loading
                  }, () => {
                    _push3(ssrRenderComponent(_component_UIcon, {
                      name: _ctx.leadingIconName,
                      class: _ctx.leadingIconClass
                    }, null, _parent3, _scopeId2));
                  }, _push3, _parent3, _scopeId2);
                  _push3(`</span>`);
                } else {
                  _push3(`<!---->`);
                }
                ssrRenderSlot(_ctx.$slots, "label", { selected: _ctx.selected }, () => {
                  if (_ctx.label) {
                    _push3(`<span class="${ssrRenderClass(_ctx.uiMenu.label)}"${_scopeId2}>${ssrInterpolate(_ctx.label)}</span>`);
                  } else {
                    _push3(`<span class="${ssrRenderClass(_ctx.uiMenu.label)}"${_scopeId2}>${ssrInterpolate(_ctx.placeholder || " ")}</span>`);
                  }
                }, _push3, _parent3, _scopeId2);
                if (_ctx.isTrailing && _ctx.trailingIconName || _ctx.$slots.trailing) {
                  _push3(`<span class="${ssrRenderClass(_ctx.trailingWrapperIconClass)}"${_scopeId2}>`);
                  ssrRenderSlot(_ctx.$slots, "trailing", {
                    selected: _ctx.selected,
                    disabled: _ctx.disabled,
                    loading: _ctx.loading
                  }, () => {
                    _push3(ssrRenderComponent(_component_UIcon, {
                      name: _ctx.trailingIconName,
                      class: _ctx.trailingIconClass,
                      "aria-hidden": "true"
                    }, null, _parent3, _scopeId2));
                  }, _push3, _parent3, _scopeId2);
                  _push3(`</span>`);
                } else {
                  _push3(`<!---->`);
                }
                _push3(`</button>`);
              }, _push3, _parent3, _scopeId2);
            } else {
              return [
                renderSlot(_ctx.$slots, "default", {
                  open,
                  disabled: _ctx.disabled,
                  loading: _ctx.loading
                }, () => [
                  createVNode("button", mergeProps({
                    id: _ctx.inputId,
                    class: _ctx.selectClass,
                    disabled: _ctx.disabled,
                    type: "button"
                  }, _ctx.attrs), [
                    _ctx.isLeading && _ctx.leadingIconName || _ctx.$slots.leading ? (openBlock(), createBlock("span", {
                      key: 0,
                      class: _ctx.leadingWrapperIconClass
                    }, [
                      renderSlot(_ctx.$slots, "leading", {
                        selected: _ctx.selected,
                        disabled: _ctx.disabled,
                        loading: _ctx.loading
                      }, () => [
                        createVNode(_component_UIcon, {
                          name: _ctx.leadingIconName,
                          class: _ctx.leadingIconClass
                        }, null, 8, ["name", "class"])
                      ])
                    ], 2)) : createCommentVNode("", true),
                    renderSlot(_ctx.$slots, "label", { selected: _ctx.selected }, () => [
                      _ctx.label ? (openBlock(), createBlock("span", {
                        key: 0,
                        class: _ctx.uiMenu.label
                      }, toDisplayString(_ctx.label), 3)) : (openBlock(), createBlock("span", {
                        key: 1,
                        class: _ctx.uiMenu.label
                      }, toDisplayString(_ctx.placeholder || " "), 3))
                    ]),
                    _ctx.isTrailing && _ctx.trailingIconName || _ctx.$slots.trailing ? (openBlock(), createBlock("span", {
                      key: 1,
                      class: _ctx.trailingWrapperIconClass
                    }, [
                      renderSlot(_ctx.$slots, "trailing", {
                        selected: _ctx.selected,
                        disabled: _ctx.disabled,
                        loading: _ctx.loading
                      }, () => [
                        createVNode(_component_UIcon, {
                          name: _ctx.trailingIconName,
                          class: _ctx.trailingIconClass,
                          "aria-hidden": "true"
                        }, null, 8, ["name", "class"])
                      ])
                    ], 2)) : createCommentVNode("", true)
                  ], 16, ["id", "disabled"])
                ])
              ];
            }
          }),
          _: 2
        }), _parent2, _scopeId);
        if (open) {
          _push2(`<div class="${ssrRenderClass([_ctx.uiMenu.container, _ctx.uiMenu.width])}"${_scopeId}><template><div${_scopeId}>`);
          if (_ctx.popper.arrow) {
            _push2(`<div data-popper-arrow class="${ssrRenderClass(Object.values(_ctx.uiMenu.arrow))}"${_scopeId}></div>`);
          } else {
            _push2(`<!---->`);
          }
          ssrRenderVNode(_push2, createVNode(resolveDynamicComponent(_ctx.searchable ? "HComboboxOptions" : "HListboxOptions"), {
            static: "",
            class: [_ctx.uiMenu.base, _ctx.uiMenu.ring, _ctx.uiMenu.rounded, _ctx.uiMenu.shadow, _ctx.uiMenu.background, _ctx.uiMenu.padding, _ctx.uiMenu.height]
          }, {
            default: withCtx((_, _push3, _parent3, _scopeId2) => {
              var _a, _b, _c, _d;
              if (_push3) {
                if (_ctx.searchable) {
                  _push3(ssrRenderComponent(_component_HComboboxInput, {
                    "display-value": () => _ctx.query,
                    name: "q",
                    placeholder: _ctx.searchablePlaceholder,
                    autofocus: "",
                    autocomplete: "off",
                    class: _ctx.uiMenu.input,
                    onChange: _ctx.onQueryChange
                  }, null, _parent3, _scopeId2));
                } else {
                  _push3(`<!---->`);
                }
                _push3(`<!--[-->`);
                ssrRenderList(_ctx.filteredOptions, (option, index) => {
                  ssrRenderVNode(_push3, createVNode(resolveDynamicComponent(_ctx.searchable ? "HComboboxOption" : "HListboxOption"), {
                    key: index,
                    as: "template",
                    value: _ctx.valueAttribute ? _ctx.accessor(option, _ctx.valueAttribute) : option,
                    disabled: option.disabled
                  }, {
                    default: withCtx(({ active, selected: optionSelected, disabled: optionDisabled }, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(`<li class="${ssrRenderClass([_ctx.uiMenu.option.base, _ctx.uiMenu.option.rounded, _ctx.uiMenu.option.padding, _ctx.uiMenu.option.size, _ctx.uiMenu.option.color, active ? _ctx.uiMenu.option.active : _ctx.uiMenu.option.inactive, optionSelected && _ctx.uiMenu.option.selected, optionDisabled && _ctx.uiMenu.option.disabled])}"${_scopeId3}><div class="${ssrRenderClass(_ctx.uiMenu.option.container)}"${_scopeId3}>`);
                        ssrRenderSlot(_ctx.$slots, "option", {
                          option,
                          active,
                          selected: optionSelected
                        }, () => {
                          if (option.icon) {
                            _push4(ssrRenderComponent(_component_UIcon, {
                              name: option.icon,
                              class: [_ctx.uiMenu.option.icon.base, active ? _ctx.uiMenu.option.icon.active : _ctx.uiMenu.option.icon.inactive, option.iconClass],
                              "aria-hidden": "true"
                            }, null, _parent4, _scopeId3));
                          } else if (option.avatar) {
                            _push4(ssrRenderComponent(_component_UAvatar, mergeProps({ ref_for: true }, { size: _ctx.uiMenu.option.avatar.size, ...option.avatar }, {
                              class: _ctx.uiMenu.option.avatar.base,
                              "aria-hidden": "true"
                            }), null, _parent4, _scopeId3));
                          } else if (option.chip) {
                            _push4(`<span class="${ssrRenderClass(_ctx.uiMenu.option.chip.base)}" style="${ssrRenderStyle({ background: `#${option.chip}` })}"${_scopeId3}></span>`);
                          } else {
                            _push4(`<!---->`);
                          }
                          _push4(`<span class="truncate"${_scopeId3}>${ssrInterpolate(["string", "number"].includes(typeof option) ? option : _ctx.accessor(option, _ctx.optionAttribute))}</span>`);
                        }, _push4, _parent4, _scopeId3);
                        _push4(`</div>`);
                        if (optionSelected) {
                          _push4(`<span class="${ssrRenderClass([_ctx.uiMenu.option.selectedIcon.wrapper, _ctx.uiMenu.option.selectedIcon.padding])}"${_scopeId3}>`);
                          _push4(ssrRenderComponent(_component_UIcon, {
                            name: _ctx.selectedIcon,
                            class: _ctx.uiMenu.option.selectedIcon.base,
                            "aria-hidden": "true"
                          }, null, _parent4, _scopeId3));
                          _push4(`</span>`);
                        } else {
                          _push4(`<!---->`);
                        }
                        _push4(`</li>`);
                      } else {
                        return [
                          createVNode("li", {
                            class: [_ctx.uiMenu.option.base, _ctx.uiMenu.option.rounded, _ctx.uiMenu.option.padding, _ctx.uiMenu.option.size, _ctx.uiMenu.option.color, active ? _ctx.uiMenu.option.active : _ctx.uiMenu.option.inactive, optionSelected && _ctx.uiMenu.option.selected, optionDisabled && _ctx.uiMenu.option.disabled]
                          }, [
                            createVNode("div", {
                              class: _ctx.uiMenu.option.container
                            }, [
                              renderSlot(_ctx.$slots, "option", {
                                option,
                                active,
                                selected: optionSelected
                              }, () => [
                                option.icon ? (openBlock(), createBlock(_component_UIcon, {
                                  key: 0,
                                  name: option.icon,
                                  class: [_ctx.uiMenu.option.icon.base, active ? _ctx.uiMenu.option.icon.active : _ctx.uiMenu.option.icon.inactive, option.iconClass],
                                  "aria-hidden": "true"
                                }, null, 8, ["name", "class"])) : option.avatar ? (openBlock(), createBlock(_component_UAvatar, mergeProps({
                                  key: 1,
                                  ref_for: true
                                }, { size: _ctx.uiMenu.option.avatar.size, ...option.avatar }, {
                                  class: _ctx.uiMenu.option.avatar.base,
                                  "aria-hidden": "true"
                                }), null, 16, ["class"])) : option.chip ? (openBlock(), createBlock("span", {
                                  key: 2,
                                  class: _ctx.uiMenu.option.chip.base,
                                  style: { background: `#${option.chip}` }
                                }, null, 6)) : createCommentVNode("", true),
                                createVNode("span", { class: "truncate" }, toDisplayString(["string", "number"].includes(typeof option) ? option : _ctx.accessor(option, _ctx.optionAttribute)), 1)
                              ])
                            ], 2),
                            optionSelected ? (openBlock(), createBlock("span", {
                              key: 0,
                              class: [_ctx.uiMenu.option.selectedIcon.wrapper, _ctx.uiMenu.option.selectedIcon.padding]
                            }, [
                              createVNode(_component_UIcon, {
                                name: _ctx.selectedIcon,
                                class: _ctx.uiMenu.option.selectedIcon.base,
                                "aria-hidden": "true"
                              }, null, 8, ["name", "class"])
                            ], 2)) : createCommentVNode("", true)
                          ], 2)
                        ];
                      }
                    }),
                    _: 2
                  }), _parent3, _scopeId2);
                });
                _push3(`<!--]-->`);
                if (_ctx.creatable && _ctx.createOption) {
                  ssrRenderVNode(_push3, createVNode(resolveDynamicComponent(_ctx.searchable ? "HComboboxOption" : "HListboxOption"), {
                    value: _ctx.createOption,
                    as: "template"
                  }, {
                    default: withCtx(({ active, selected: optionSelected }, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(`<li class="${ssrRenderClass([_ctx.uiMenu.option.base, _ctx.uiMenu.option.rounded, _ctx.uiMenu.option.padding, _ctx.uiMenu.option.size, _ctx.uiMenu.option.color, active ? _ctx.uiMenu.option.active : _ctx.uiMenu.option.inactive])}"${_scopeId3}><div class="${ssrRenderClass(_ctx.uiMenu.option.container)}"${_scopeId3}>`);
                        ssrRenderSlot(_ctx.$slots, "option-create", {
                          option: _ctx.createOption,
                          active,
                          selected: optionSelected
                        }, () => {
                          _push4(`<span class="${ssrRenderClass(_ctx.uiMenu.option.create)}"${_scopeId3}>Create &quot;${ssrInterpolate(typeof _ctx.createOption === "string" ? _ctx.createOption : _ctx.accessor(_ctx.createOption, _ctx.optionAttribute))}&quot;</span>`);
                        }, _push4, _parent4, _scopeId3);
                        _push4(`</div></li>`);
                      } else {
                        return [
                          createVNode("li", {
                            class: [_ctx.uiMenu.option.base, _ctx.uiMenu.option.rounded, _ctx.uiMenu.option.padding, _ctx.uiMenu.option.size, _ctx.uiMenu.option.color, active ? _ctx.uiMenu.option.active : _ctx.uiMenu.option.inactive]
                          }, [
                            createVNode("div", {
                              class: _ctx.uiMenu.option.container
                            }, [
                              renderSlot(_ctx.$slots, "option-create", {
                                option: _ctx.createOption,
                                active,
                                selected: optionSelected
                              }, () => [
                                createVNode("span", {
                                  class: _ctx.uiMenu.option.create
                                }, 'Create "' + toDisplayString(typeof _ctx.createOption === "string" ? _ctx.createOption : _ctx.accessor(_ctx.createOption, _ctx.optionAttribute)) + '"', 3)
                              ])
                            ], 2)
                          ], 2)
                        ];
                      }
                    }),
                    _: 2
                  }), _parent3, _scopeId2);
                } else if (_ctx.searchable && _ctx.query && !((_a = _ctx.filteredOptions) == null ? void 0 : _a.length)) {
                  _push3(`<p class="${ssrRenderClass(_ctx.uiMenu.option.empty)}"${_scopeId2}>`);
                  ssrRenderSlot(_ctx.$slots, "option-empty", { query: _ctx.query }, () => {
                    _push3(`${ssrInterpolate(_ctx.uiMenu.default.optionEmpty.label.replace("{query}", _ctx.query))}`);
                  }, _push3, _parent3, _scopeId2);
                  _push3(`</p>`);
                } else if (!((_b = _ctx.filteredOptions) == null ? void 0 : _b.length)) {
                  _push3(`<p class="${ssrRenderClass(_ctx.uiMenu.empty)}"${_scopeId2}>`);
                  ssrRenderSlot(_ctx.$slots, "empty", { query: _ctx.query }, () => {
                    _push3(`${ssrInterpolate(_ctx.uiMenu.default.empty.label)}`);
                  }, _push3, _parent3, _scopeId2);
                  _push3(`</p>`);
                } else {
                  _push3(`<!---->`);
                }
              } else {
                return [
                  _ctx.searchable ? (openBlock(), createBlock(_component_HComboboxInput, {
                    key: 0,
                    "display-value": () => _ctx.query,
                    name: "q",
                    placeholder: _ctx.searchablePlaceholder,
                    autofocus: "",
                    autocomplete: "off",
                    class: _ctx.uiMenu.input,
                    onChange: _ctx.onQueryChange
                  }, null, 8, ["display-value", "placeholder", "class", "onChange"])) : createCommentVNode("", true),
                  (openBlock(true), createBlock(Fragment, null, renderList(_ctx.filteredOptions, (option, index) => {
                    return openBlock(), createBlock(resolveDynamicComponent(_ctx.searchable ? "HComboboxOption" : "HListboxOption"), {
                      key: index,
                      as: "template",
                      value: _ctx.valueAttribute ? _ctx.accessor(option, _ctx.valueAttribute) : option,
                      disabled: option.disabled
                    }, {
                      default: withCtx(({ active, selected: optionSelected, disabled: optionDisabled }) => [
                        createVNode("li", {
                          class: [_ctx.uiMenu.option.base, _ctx.uiMenu.option.rounded, _ctx.uiMenu.option.padding, _ctx.uiMenu.option.size, _ctx.uiMenu.option.color, active ? _ctx.uiMenu.option.active : _ctx.uiMenu.option.inactive, optionSelected && _ctx.uiMenu.option.selected, optionDisabled && _ctx.uiMenu.option.disabled]
                        }, [
                          createVNode("div", {
                            class: _ctx.uiMenu.option.container
                          }, [
                            renderSlot(_ctx.$slots, "option", {
                              option,
                              active,
                              selected: optionSelected
                            }, () => [
                              option.icon ? (openBlock(), createBlock(_component_UIcon, {
                                key: 0,
                                name: option.icon,
                                class: [_ctx.uiMenu.option.icon.base, active ? _ctx.uiMenu.option.icon.active : _ctx.uiMenu.option.icon.inactive, option.iconClass],
                                "aria-hidden": "true"
                              }, null, 8, ["name", "class"])) : option.avatar ? (openBlock(), createBlock(_component_UAvatar, mergeProps({
                                key: 1,
                                ref_for: true
                              }, { size: _ctx.uiMenu.option.avatar.size, ...option.avatar }, {
                                class: _ctx.uiMenu.option.avatar.base,
                                "aria-hidden": "true"
                              }), null, 16, ["class"])) : option.chip ? (openBlock(), createBlock("span", {
                                key: 2,
                                class: _ctx.uiMenu.option.chip.base,
                                style: { background: `#${option.chip}` }
                              }, null, 6)) : createCommentVNode("", true),
                              createVNode("span", { class: "truncate" }, toDisplayString(["string", "number"].includes(typeof option) ? option : _ctx.accessor(option, _ctx.optionAttribute)), 1)
                            ])
                          ], 2),
                          optionSelected ? (openBlock(), createBlock("span", {
                            key: 0,
                            class: [_ctx.uiMenu.option.selectedIcon.wrapper, _ctx.uiMenu.option.selectedIcon.padding]
                          }, [
                            createVNode(_component_UIcon, {
                              name: _ctx.selectedIcon,
                              class: _ctx.uiMenu.option.selectedIcon.base,
                              "aria-hidden": "true"
                            }, null, 8, ["name", "class"])
                          ], 2)) : createCommentVNode("", true)
                        ], 2)
                      ]),
                      _: 2
                    }, 1032, ["value", "disabled"]);
                  }), 128)),
                  _ctx.creatable && _ctx.createOption ? (openBlock(), createBlock(resolveDynamicComponent(_ctx.searchable ? "HComboboxOption" : "HListboxOption"), {
                    key: 1,
                    value: _ctx.createOption,
                    as: "template"
                  }, {
                    default: withCtx(({ active, selected: optionSelected }) => [
                      createVNode("li", {
                        class: [_ctx.uiMenu.option.base, _ctx.uiMenu.option.rounded, _ctx.uiMenu.option.padding, _ctx.uiMenu.option.size, _ctx.uiMenu.option.color, active ? _ctx.uiMenu.option.active : _ctx.uiMenu.option.inactive]
                      }, [
                        createVNode("div", {
                          class: _ctx.uiMenu.option.container
                        }, [
                          renderSlot(_ctx.$slots, "option-create", {
                            option: _ctx.createOption,
                            active,
                            selected: optionSelected
                          }, () => [
                            createVNode("span", {
                              class: _ctx.uiMenu.option.create
                            }, 'Create "' + toDisplayString(typeof _ctx.createOption === "string" ? _ctx.createOption : _ctx.accessor(_ctx.createOption, _ctx.optionAttribute)) + '"', 3)
                          ])
                        ], 2)
                      ], 2)
                    ]),
                    _: 3
                  }, 8, ["value"])) : _ctx.searchable && _ctx.query && !((_c = _ctx.filteredOptions) == null ? void 0 : _c.length) ? (openBlock(), createBlock("p", {
                    key: 2,
                    class: _ctx.uiMenu.option.empty
                  }, [
                    renderSlot(_ctx.$slots, "option-empty", { query: _ctx.query }, () => [
                      createTextVNode(toDisplayString(_ctx.uiMenu.default.optionEmpty.label.replace("{query}", _ctx.query)), 1)
                    ])
                  ], 2)) : !((_d = _ctx.filteredOptions) == null ? void 0 : _d.length) ? (openBlock(), createBlock("p", {
                    key: 3,
                    class: _ctx.uiMenu.empty
                  }, [
                    renderSlot(_ctx.$slots, "empty", { query: _ctx.query }, () => [
                      createTextVNode(toDisplayString(_ctx.uiMenu.default.empty.label), 1)
                    ])
                  ], 2)) : createCommentVNode("", true)
                ];
              }
            }),
            _: 2
          }), _parent2, _scopeId);
          _push2(`</div></template></div>`);
        } else {
          _push2(`<!---->`);
        }
      } else {
        return [
          _ctx.required ? (openBlock(), createBlock("input", {
            key: 0,
            value: _ctx.modelValue,
            required: _ctx.required,
            class: _ctx.uiMenu.required,
            form: _ctx.inputTargetForm,
            tabindex: "-1",
            "aria-hidden": "true"
          }, null, 10, ["value", "required", "form"])) : createCommentVNode("", true),
          (openBlock(), createBlock(resolveDynamicComponent(_ctx.searchable ? "HComboboxButton" : "HListboxButton"), {
            ref: "trigger",
            as: "div",
            role: "button",
            class: _ctx.uiMenu.trigger
          }, {
            default: withCtx(() => [
              renderSlot(_ctx.$slots, "default", {
                open,
                disabled: _ctx.disabled,
                loading: _ctx.loading
              }, () => [
                createVNode("button", mergeProps({
                  id: _ctx.inputId,
                  class: _ctx.selectClass,
                  disabled: _ctx.disabled,
                  type: "button"
                }, _ctx.attrs), [
                  _ctx.isLeading && _ctx.leadingIconName || _ctx.$slots.leading ? (openBlock(), createBlock("span", {
                    key: 0,
                    class: _ctx.leadingWrapperIconClass
                  }, [
                    renderSlot(_ctx.$slots, "leading", {
                      selected: _ctx.selected,
                      disabled: _ctx.disabled,
                      loading: _ctx.loading
                    }, () => [
                      createVNode(_component_UIcon, {
                        name: _ctx.leadingIconName,
                        class: _ctx.leadingIconClass
                      }, null, 8, ["name", "class"])
                    ])
                  ], 2)) : createCommentVNode("", true),
                  renderSlot(_ctx.$slots, "label", { selected: _ctx.selected }, () => [
                    _ctx.label ? (openBlock(), createBlock("span", {
                      key: 0,
                      class: _ctx.uiMenu.label
                    }, toDisplayString(_ctx.label), 3)) : (openBlock(), createBlock("span", {
                      key: 1,
                      class: _ctx.uiMenu.label
                    }, toDisplayString(_ctx.placeholder || " "), 3))
                  ]),
                  _ctx.isTrailing && _ctx.trailingIconName || _ctx.$slots.trailing ? (openBlock(), createBlock("span", {
                    key: 1,
                    class: _ctx.trailingWrapperIconClass
                  }, [
                    renderSlot(_ctx.$slots, "trailing", {
                      selected: _ctx.selected,
                      disabled: _ctx.disabled,
                      loading: _ctx.loading
                    }, () => [
                      createVNode(_component_UIcon, {
                        name: _ctx.trailingIconName,
                        class: _ctx.trailingIconClass,
                        "aria-hidden": "true"
                      }, null, 8, ["name", "class"])
                    ])
                  ], 2)) : createCommentVNode("", true)
                ], 16, ["id", "disabled"])
              ])
            ]),
            _: 2
          }, 1032, ["class"])),
          open ? (openBlock(), createBlock("div", {
            key: 1,
            ref: "container",
            class: [_ctx.uiMenu.container, _ctx.uiMenu.width]
          }, [
            createVNode(Transition, mergeProps({ appear: "" }, _ctx.uiMenu.transition), {
              default: withCtx(() => [
                createVNode("div", null, [
                  _ctx.popper.arrow ? (openBlock(), createBlock("div", {
                    key: 0,
                    "data-popper-arrow": "",
                    class: Object.values(_ctx.uiMenu.arrow)
                  }, null, 2)) : createCommentVNode("", true),
                  (openBlock(), createBlock(resolveDynamicComponent(_ctx.searchable ? "HComboboxOptions" : "HListboxOptions"), {
                    static: "",
                    class: [_ctx.uiMenu.base, _ctx.uiMenu.ring, _ctx.uiMenu.rounded, _ctx.uiMenu.shadow, _ctx.uiMenu.background, _ctx.uiMenu.padding, _ctx.uiMenu.height]
                  }, {
                    default: withCtx(() => {
                      var _a, _b;
                      return [
                        _ctx.searchable ? (openBlock(), createBlock(_component_HComboboxInput, {
                          key: 0,
                          "display-value": () => _ctx.query,
                          name: "q",
                          placeholder: _ctx.searchablePlaceholder,
                          autofocus: "",
                          autocomplete: "off",
                          class: _ctx.uiMenu.input,
                          onChange: _ctx.onQueryChange
                        }, null, 8, ["display-value", "placeholder", "class", "onChange"])) : createCommentVNode("", true),
                        (openBlock(true), createBlock(Fragment, null, renderList(_ctx.filteredOptions, (option, index) => {
                          return openBlock(), createBlock(resolveDynamicComponent(_ctx.searchable ? "HComboboxOption" : "HListboxOption"), {
                            key: index,
                            as: "template",
                            value: _ctx.valueAttribute ? _ctx.accessor(option, _ctx.valueAttribute) : option,
                            disabled: option.disabled
                          }, {
                            default: withCtx(({ active, selected: optionSelected, disabled: optionDisabled }) => [
                              createVNode("li", {
                                class: [_ctx.uiMenu.option.base, _ctx.uiMenu.option.rounded, _ctx.uiMenu.option.padding, _ctx.uiMenu.option.size, _ctx.uiMenu.option.color, active ? _ctx.uiMenu.option.active : _ctx.uiMenu.option.inactive, optionSelected && _ctx.uiMenu.option.selected, optionDisabled && _ctx.uiMenu.option.disabled]
                              }, [
                                createVNode("div", {
                                  class: _ctx.uiMenu.option.container
                                }, [
                                  renderSlot(_ctx.$slots, "option", {
                                    option,
                                    active,
                                    selected: optionSelected
                                  }, () => [
                                    option.icon ? (openBlock(), createBlock(_component_UIcon, {
                                      key: 0,
                                      name: option.icon,
                                      class: [_ctx.uiMenu.option.icon.base, active ? _ctx.uiMenu.option.icon.active : _ctx.uiMenu.option.icon.inactive, option.iconClass],
                                      "aria-hidden": "true"
                                    }, null, 8, ["name", "class"])) : option.avatar ? (openBlock(), createBlock(_component_UAvatar, mergeProps({
                                      key: 1,
                                      ref_for: true
                                    }, { size: _ctx.uiMenu.option.avatar.size, ...option.avatar }, {
                                      class: _ctx.uiMenu.option.avatar.base,
                                      "aria-hidden": "true"
                                    }), null, 16, ["class"])) : option.chip ? (openBlock(), createBlock("span", {
                                      key: 2,
                                      class: _ctx.uiMenu.option.chip.base,
                                      style: { background: `#${option.chip}` }
                                    }, null, 6)) : createCommentVNode("", true),
                                    createVNode("span", { class: "truncate" }, toDisplayString(["string", "number"].includes(typeof option) ? option : _ctx.accessor(option, _ctx.optionAttribute)), 1)
                                  ])
                                ], 2),
                                optionSelected ? (openBlock(), createBlock("span", {
                                  key: 0,
                                  class: [_ctx.uiMenu.option.selectedIcon.wrapper, _ctx.uiMenu.option.selectedIcon.padding]
                                }, [
                                  createVNode(_component_UIcon, {
                                    name: _ctx.selectedIcon,
                                    class: _ctx.uiMenu.option.selectedIcon.base,
                                    "aria-hidden": "true"
                                  }, null, 8, ["name", "class"])
                                ], 2)) : createCommentVNode("", true)
                              ], 2)
                            ]),
                            _: 2
                          }, 1032, ["value", "disabled"]);
                        }), 128)),
                        _ctx.creatable && _ctx.createOption ? (openBlock(), createBlock(resolveDynamicComponent(_ctx.searchable ? "HComboboxOption" : "HListboxOption"), {
                          key: 1,
                          value: _ctx.createOption,
                          as: "template"
                        }, {
                          default: withCtx(({ active, selected: optionSelected }) => [
                            createVNode("li", {
                              class: [_ctx.uiMenu.option.base, _ctx.uiMenu.option.rounded, _ctx.uiMenu.option.padding, _ctx.uiMenu.option.size, _ctx.uiMenu.option.color, active ? _ctx.uiMenu.option.active : _ctx.uiMenu.option.inactive]
                            }, [
                              createVNode("div", {
                                class: _ctx.uiMenu.option.container
                              }, [
                                renderSlot(_ctx.$slots, "option-create", {
                                  option: _ctx.createOption,
                                  active,
                                  selected: optionSelected
                                }, () => [
                                  createVNode("span", {
                                    class: _ctx.uiMenu.option.create
                                  }, 'Create "' + toDisplayString(typeof _ctx.createOption === "string" ? _ctx.createOption : _ctx.accessor(_ctx.createOption, _ctx.optionAttribute)) + '"', 3)
                                ])
                              ], 2)
                            ], 2)
                          ]),
                          _: 3
                        }, 8, ["value"])) : _ctx.searchable && _ctx.query && !((_a = _ctx.filteredOptions) == null ? void 0 : _a.length) ? (openBlock(), createBlock("p", {
                          key: 2,
                          class: _ctx.uiMenu.option.empty
                        }, [
                          renderSlot(_ctx.$slots, "option-empty", { query: _ctx.query }, () => [
                            createTextVNode(toDisplayString(_ctx.uiMenu.default.optionEmpty.label.replace("{query}", _ctx.query)), 1)
                          ])
                        ], 2)) : !((_b = _ctx.filteredOptions) == null ? void 0 : _b.length) ? (openBlock(), createBlock("p", {
                          key: 3,
                          class: _ctx.uiMenu.empty
                        }, [
                          renderSlot(_ctx.$slots, "empty", { query: _ctx.query }, () => [
                            createTextVNode(toDisplayString(_ctx.uiMenu.default.empty.label), 1)
                          ])
                        ], 2)) : createCommentVNode("", true)
                      ];
                    }),
                    _: 3
                  }, 8, ["class"]))
                ])
              ]),
              _: 3
            }, 16)
          ], 2)) : createCommentVNode("", true)
        ];
      }
    }),
    _: 3
  }), _parent);
}
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/forms/SelectMenu.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const __nuxt_component_5 = /* @__PURE__ */ _export_sfc(_sfc_main, [["ssrRender", _sfc_ssrRender]]);
export {
  __nuxt_component_5 as _
};
//# sourceMappingURL=SelectMenu-CzPdyyER.js.map
