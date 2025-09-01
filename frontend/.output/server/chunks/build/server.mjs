import { hasInjectionContext, inject, defineComponent, shallowRef, h, resolveComponent, computed, getCurrentInstance, ref, reactive, effectScope, isRef, isReactive, toRaw, getCurrentScope, onScopeDispose, watch, nextTick, toRefs, markRaw, createElementBlock, provide, cloneVNode, defineAsyncComponent, Suspense, Fragment, readonly, createApp, shallowReactive, toRef, onErrorCaptured, onServerPrefetch, unref, createVNode, resolveDynamicComponent, isReadonly, isShallow, mergeProps, withCtx, createTextVNode, toDisplayString, useSSRContext } from 'vue';
import { k as defuFn, l as klona, m as parseQuery, i as createError$1, n as defu, o as createDefu, q as hasProtocol, r as isScriptProtocol, v as joinURL, w as withQuery, x as sanitizeStatusCode, y as withTrailingSlash, z as withoutTrailingSlash, A as getContext, $ as $fetch$1, B as baseURL, C as createHooks, D as executeAsync, E as toRouteMatcher, F as createRouter$1 } from '../_/nitro.mjs';
import { RouterView, createMemoryHistory, createRouter, START_LOCATION } from 'vue-router';
import { createSharedComposable } from '@vueuse/core';
import { extendTailwindMerge } from 'tailwind-merge';
import { _api, addAPIProvider, setCustomIconsLoader } from '@iconify/vue';
import { ssrRenderSuspense, ssrRenderComponent, ssrRenderVNode, ssrRenderAttrs, ssrRenderClass, ssrInterpolate, ssrRenderAttr, ssrRenderList, ssrIncludeBooleanAttr, ssrRenderStyle } from 'vue/server-renderer';
import { FolderIcon, QuestionMarkCircleIcon, MagnifyingGlassIcon, ArrowRightIcon, HomeIcon, UsersIcon, CheckIcon, Bars3Icon, ChevronRightIcon, GlobeAltIcon, SunIcon, MoonIcon, BellIcon, ChevronDownIcon, ArrowRightOnRectangleIcon, ChevronLeftIcon, XMarkIcon, WrenchScrewdriverIcon, InformationCircleIcon, ShieldExclamationIcon, DocumentTextIcon, UserPlusIcon, ExclamationCircleIcon, UserGroupIcon, CogIcon, ChartBarIcon } from '@heroicons/vue/24/outline';
import { u as useHead$1, h as headSymbol } from '../routes/renderer.mjs';
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
import 'vue-bundle-renderer/runtime';
import 'unhead/server';
import 'devalue';
import 'unhead/utils';
import 'unhead/plugins';

var _a;
if (!globalThis.$fetch) {
  globalThis.$fetch = $fetch$1.create({
    baseURL: baseURL()
  });
}
if (!("global" in globalThis)) {
  globalThis.global = globalThis;
}
const appLayoutTransition = false;
const nuxtLinkDefaults = { "componentName": "NuxtLink" };
const asyncDataDefaults = { "value": null, "errorValue": null, "deep": true };
const appId = "nuxt-app";
function getNuxtAppCtx(id = appId) {
  return getContext(id, {
    asyncContext: false
  });
}
const NuxtPluginIndicator = "__nuxt_plugin";
function createNuxtApp(options) {
  var _a2;
  let hydratingCount = 0;
  const nuxtApp = {
    _id: options.id || appId || "nuxt-app",
    _scope: effectScope(),
    provide: void 0,
    globalName: "nuxt",
    versions: {
      get nuxt() {
        return "3.17.7";
      },
      get vue() {
        return nuxtApp.vueApp.version;
      }
    },
    payload: shallowReactive({
      ...((_a2 = options.ssrContext) == null ? void 0 : _a2.payload) || {},
      data: shallowReactive({}),
      state: reactive({}),
      once: /* @__PURE__ */ new Set(),
      _errors: shallowReactive({})
    }),
    static: {
      data: {}
    },
    runWithContext(fn) {
      if (nuxtApp._scope.active && !getCurrentScope()) {
        return nuxtApp._scope.run(() => callWithNuxt(nuxtApp, fn));
      }
      return callWithNuxt(nuxtApp, fn);
    },
    isHydrating: false,
    deferHydration() {
      if (!nuxtApp.isHydrating) {
        return () => {
        };
      }
      hydratingCount++;
      let called = false;
      return () => {
        if (called) {
          return;
        }
        called = true;
        hydratingCount--;
        if (hydratingCount === 0) {
          nuxtApp.isHydrating = false;
          return nuxtApp.callHook("app:suspense:resolve");
        }
      };
    },
    _asyncDataPromises: {},
    _asyncData: shallowReactive({}),
    _payloadRevivers: {},
    ...options
  };
  {
    nuxtApp.payload.serverRendered = true;
  }
  if (nuxtApp.ssrContext) {
    nuxtApp.payload.path = nuxtApp.ssrContext.url;
    nuxtApp.ssrContext.nuxt = nuxtApp;
    nuxtApp.ssrContext.payload = nuxtApp.payload;
    nuxtApp.ssrContext.config = {
      public: nuxtApp.ssrContext.runtimeConfig.public,
      app: nuxtApp.ssrContext.runtimeConfig.app
    };
  }
  nuxtApp.hooks = createHooks();
  nuxtApp.hook = nuxtApp.hooks.hook;
  {
    const contextCaller = async function(hooks, args) {
      for (const hook of hooks) {
        await nuxtApp.runWithContext(() => hook(...args));
      }
    };
    nuxtApp.hooks.callHook = (name, ...args) => nuxtApp.hooks.callHookWith(contextCaller, name, ...args);
  }
  nuxtApp.callHook = nuxtApp.hooks.callHook;
  nuxtApp.provide = (name, value) => {
    const $name = "$" + name;
    defineGetter(nuxtApp, $name, value);
    defineGetter(nuxtApp.vueApp.config.globalProperties, $name, value);
  };
  defineGetter(nuxtApp.vueApp, "$nuxt", nuxtApp);
  defineGetter(nuxtApp.vueApp.config.globalProperties, "$nuxt", nuxtApp);
  const runtimeConfig = options.ssrContext.runtimeConfig;
  nuxtApp.provide("config", runtimeConfig);
  return nuxtApp;
}
function registerPluginHooks(nuxtApp, plugin2) {
  if (plugin2.hooks) {
    nuxtApp.hooks.addHooks(plugin2.hooks);
  }
}
async function applyPlugin(nuxtApp, plugin2) {
  if (typeof plugin2 === "function") {
    const { provide: provide2 } = await nuxtApp.runWithContext(() => plugin2(nuxtApp)) || {};
    if (provide2 && typeof provide2 === "object") {
      for (const key in provide2) {
        nuxtApp.provide(key, provide2[key]);
      }
    }
  }
}
async function applyPlugins(nuxtApp, plugins2) {
  var _a2, _b, _c, _d;
  const resolvedPlugins = /* @__PURE__ */ new Set();
  const unresolvedPlugins = [];
  const parallels = [];
  const errors = [];
  let promiseDepth = 0;
  async function executePlugin(plugin2) {
    var _a3;
    const unresolvedPluginsForThisPlugin = ((_a3 = plugin2.dependsOn) == null ? void 0 : _a3.filter((name) => plugins2.some((p) => p._name === name) && !resolvedPlugins.has(name))) ?? [];
    if (unresolvedPluginsForThisPlugin.length > 0) {
      unresolvedPlugins.push([new Set(unresolvedPluginsForThisPlugin), plugin2]);
    } else {
      const promise = applyPlugin(nuxtApp, plugin2).then(async () => {
        if (plugin2._name) {
          resolvedPlugins.add(plugin2._name);
          await Promise.all(unresolvedPlugins.map(async ([dependsOn, unexecutedPlugin]) => {
            if (dependsOn.has(plugin2._name)) {
              dependsOn.delete(plugin2._name);
              if (dependsOn.size === 0) {
                promiseDepth++;
                await executePlugin(unexecutedPlugin);
              }
            }
          }));
        }
      });
      if (plugin2.parallel) {
        parallels.push(promise.catch((e) => errors.push(e)));
      } else {
        await promise;
      }
    }
  }
  for (const plugin2 of plugins2) {
    if (((_a2 = nuxtApp.ssrContext) == null ? void 0 : _a2.islandContext) && ((_b = plugin2.env) == null ? void 0 : _b.islands) === false) {
      continue;
    }
    registerPluginHooks(nuxtApp, plugin2);
  }
  for (const plugin2 of plugins2) {
    if (((_c = nuxtApp.ssrContext) == null ? void 0 : _c.islandContext) && ((_d = plugin2.env) == null ? void 0 : _d.islands) === false) {
      continue;
    }
    await executePlugin(plugin2);
  }
  await Promise.all(parallels);
  if (promiseDepth) {
    for (let i = 0; i < promiseDepth; i++) {
      await Promise.all(parallels);
    }
  }
  if (errors.length) {
    throw errors[0];
  }
}
// @__NO_SIDE_EFFECTS__
function defineNuxtPlugin(plugin2) {
  if (typeof plugin2 === "function") {
    return plugin2;
  }
  const _name = plugin2._name || plugin2.name;
  delete plugin2.name;
  return Object.assign(plugin2.setup || (() => {
  }), plugin2, { [NuxtPluginIndicator]: true, _name });
}
function callWithNuxt(nuxt, setup, args) {
  const fn = () => setup();
  const nuxtAppCtx = getNuxtAppCtx(nuxt._id);
  {
    return nuxt.vueApp.runWithContext(() => nuxtAppCtx.callAsync(nuxt, fn));
  }
}
function tryUseNuxtApp(id) {
  var _a2;
  let nuxtAppInstance;
  if (hasInjectionContext()) {
    nuxtAppInstance = (_a2 = getCurrentInstance()) == null ? void 0 : _a2.appContext.app.$nuxt;
  }
  nuxtAppInstance || (nuxtAppInstance = getNuxtAppCtx(id).tryUse());
  return nuxtAppInstance || null;
}
function useNuxtApp(id) {
  const nuxtAppInstance = tryUseNuxtApp(id);
  if (!nuxtAppInstance) {
    {
      throw new Error("[nuxt] instance unavailable");
    }
  }
  return nuxtAppInstance;
}
// @__NO_SIDE_EFFECTS__
function useRuntimeConfig(_event) {
  return useNuxtApp().$config;
}
function defineGetter(obj, key, val) {
  Object.defineProperty(obj, key, { get: () => val });
}
const LayoutMetaSymbol = Symbol("layout-meta");
const PageRouteSymbol = Symbol("route");
const useRouter = () => {
  var _a2;
  return (_a2 = useNuxtApp()) == null ? void 0 : _a2.$router;
};
const useRoute = () => {
  if (hasInjectionContext()) {
    return inject(PageRouteSymbol, useNuxtApp()._route);
  }
  return useNuxtApp()._route;
};
// @__NO_SIDE_EFFECTS__
function defineNuxtRouteMiddleware(middleware) {
  return middleware;
}
const isProcessingMiddleware = () => {
  try {
    if (useNuxtApp()._processingMiddleware) {
      return true;
    }
  } catch {
    return false;
  }
  return false;
};
const URL_QUOTE_RE = /"/g;
const navigateTo = (to, options) => {
  to || (to = "/");
  const toPath = typeof to === "string" ? to : "path" in to ? resolveRouteObject(to) : useRouter().resolve(to).href;
  const isExternalHost = hasProtocol(toPath, { acceptRelative: true });
  const isExternal = (options == null ? void 0 : options.external) || isExternalHost;
  if (isExternal) {
    if (!(options == null ? void 0 : options.external)) {
      throw new Error("Navigating to an external URL is not allowed by default. Use `navigateTo(url, { external: true })`.");
    }
    const { protocol } = new URL(toPath, "http://localhost");
    if (protocol && isScriptProtocol(protocol)) {
      throw new Error(`Cannot navigate to a URL with '${protocol}' protocol.`);
    }
  }
  const inMiddleware = isProcessingMiddleware();
  const router = useRouter();
  const nuxtApp = useNuxtApp();
  {
    if (nuxtApp.ssrContext) {
      const fullPath = typeof to === "string" || isExternal ? toPath : router.resolve(to).fullPath || "/";
      const location2 = isExternal ? toPath : joinURL((/* @__PURE__ */ useRuntimeConfig()).app.baseURL, fullPath);
      const redirect = async function(response) {
        await nuxtApp.callHook("app:redirected");
        const encodedLoc = location2.replace(URL_QUOTE_RE, "%22");
        const encodedHeader = encodeURL(location2, isExternalHost);
        nuxtApp.ssrContext._renderResponse = {
          statusCode: sanitizeStatusCode((options == null ? void 0 : options.redirectCode) || 302, 302),
          body: `<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0; url=${encodedLoc}"></head></html>`,
          headers: { location: encodedHeader }
        };
        return response;
      };
      if (!isExternal && inMiddleware) {
        router.afterEach((final) => final.fullPath === fullPath ? redirect(false) : void 0);
        return to;
      }
      return redirect(!inMiddleware ? void 0 : (
        /* abort route navigation */
        false
      ));
    }
  }
  if (isExternal) {
    nuxtApp._scope.stop();
    if (options == null ? void 0 : options.replace) {
      (void 0).replace(toPath);
    } else {
      (void 0).href = toPath;
    }
    if (inMiddleware) {
      if (!nuxtApp.isHydrating) {
        return false;
      }
      return new Promise(() => {
      });
    }
    return Promise.resolve();
  }
  return (options == null ? void 0 : options.replace) ? router.replace(to) : router.push(to);
};
function resolveRouteObject(to) {
  return withQuery(to.path || "", to.query || {}) + (to.hash || "");
}
function encodeURL(location2, isExternalHost = false) {
  const url = new URL(location2, "http://localhost");
  if (!isExternalHost) {
    return url.pathname + url.search + url.hash;
  }
  if (location2.startsWith("//")) {
    return url.toString().replace(url.protocol, "");
  }
  return url.toString();
}
const NUXT_ERROR_SIGNATURE = "__nuxt_error";
const useError = () => toRef(useNuxtApp().payload, "error");
const showError = (error) => {
  const nuxtError = createError(error);
  try {
    const nuxtApp = useNuxtApp();
    const error2 = useError();
    if (false) ;
    error2.value || (error2.value = nuxtError);
  } catch {
    throw nuxtError;
  }
  return nuxtError;
};
const isNuxtError = (error) => !!error && typeof error === "object" && NUXT_ERROR_SIGNATURE in error;
const createError = (error) => {
  const nuxtError = createError$1(error);
  Object.defineProperty(nuxtError, NUXT_ERROR_SIGNATURE, {
    value: true,
    configurable: false,
    writable: false
  });
  return nuxtError;
};
const unhead_k2P3m_ZDyjlr2mMYnoDPwavjsDN8hBlk9cFai0bbopU = /* @__PURE__ */ defineNuxtPlugin({
  name: "nuxt:head",
  enforce: "pre",
  setup(nuxtApp) {
    const head = nuxtApp.ssrContext.head;
    nuxtApp.vueApp.use(head);
  }
});
function toArray(value) {
  return Array.isArray(value) ? value : [value];
}
async function getRouteRules(arg) {
  const path = typeof arg === "string" ? arg : arg.path;
  {
    useNuxtApp().ssrContext._preloadManifest = true;
    const _routeRulesMatcher = toRouteMatcher(
      createRouter$1({ routes: (/* @__PURE__ */ useRuntimeConfig()).nitro.routeRules })
    );
    return defu({}, ..._routeRulesMatcher.matchAll(path).reverse());
  }
}
const _routes = [
  {
    name: "index",
    path: "/",
    component: () => import('./index-yhTarYOi.mjs')
  },
  {
    name: "login",
    path: "/login",
    component: () => import('./login-FLIRiSDt.mjs')
  },
  {
    name: "signup",
    path: "/signup",
    component: () => import('./signup-ByYqj2Kp.mjs')
  }
];
const _wrapInTransition = (props, children) => {
  return { default: () => {
    var _a2;
    return (_a2 = children.default) == null ? void 0 : _a2.call(children);
  } };
};
const ROUTE_KEY_PARENTHESES_RE = /(:\w+)\([^)]+\)/g;
const ROUTE_KEY_SYMBOLS_RE = /(:\w+)[?+*]/g;
const ROUTE_KEY_NORMAL_RE = /:\w+/g;
function generateRouteKey(route) {
  const source = (route == null ? void 0 : route.meta.key) ?? route.path.replace(ROUTE_KEY_PARENTHESES_RE, "$1").replace(ROUTE_KEY_SYMBOLS_RE, "$1").replace(ROUTE_KEY_NORMAL_RE, (r) => {
    var _a2;
    return ((_a2 = route.params[r.slice(1)]) == null ? void 0 : _a2.toString()) || "";
  });
  return typeof source === "function" ? source(route) : source;
}
function isChangingPage(to, from) {
  if (to === from || from === START_LOCATION) {
    return false;
  }
  if (generateRouteKey(to) !== generateRouteKey(from)) {
    return true;
  }
  const areComponentsSame = to.matched.every(
    (comp, index) => {
      var _a2, _b;
      return comp.components && comp.components.default === ((_b = (_a2 = from.matched[index]) == null ? void 0 : _a2.components) == null ? void 0 : _b.default);
    }
  );
  if (areComponentsSame) {
    return false;
  }
  return true;
}
const routerOptions0 = {
  scrollBehavior(to, from, savedPosition) {
    var _a2;
    const nuxtApp = useNuxtApp();
    const behavior = ((_a2 = useRouter().options) == null ? void 0 : _a2.scrollBehaviorType) ?? "auto";
    if (to.path === from.path) {
      if (from.hash && !to.hash) {
        return { left: 0, top: 0 };
      }
      if (to.hash) {
        return { el: to.hash, top: _getHashElementScrollMarginTop(to.hash), behavior };
      }
      return false;
    }
    const routeAllowsScrollToTop = typeof to.meta.scrollToTop === "function" ? to.meta.scrollToTop(to, from) : to.meta.scrollToTop;
    if (routeAllowsScrollToTop === false) {
      return false;
    }
    const hookToWait = nuxtApp._runningTransition ? "page:transition:finish" : "page:loading:end";
    return new Promise((resolve) => {
      if (from === START_LOCATION) {
        resolve(_calculatePosition(to, from, savedPosition, behavior));
        return;
      }
      nuxtApp.hooks.hookOnce(hookToWait, () => {
        requestAnimationFrame(() => resolve(_calculatePosition(to, from, savedPosition, behavior)));
      });
    });
  }
};
function _getHashElementScrollMarginTop(selector) {
  try {
    const elem = (void 0).querySelector(selector);
    if (elem) {
      return (Number.parseFloat(getComputedStyle(elem).scrollMarginTop) || 0) + (Number.parseFloat(getComputedStyle((void 0).documentElement).scrollPaddingTop) || 0);
    }
  } catch {
  }
  return 0;
}
function _calculatePosition(to, from, savedPosition, defaultBehavior) {
  if (savedPosition) {
    return savedPosition;
  }
  const isPageNavigation = isChangingPage(to, from);
  if (to.hash) {
    return {
      el: to.hash,
      top: _getHashElementScrollMarginTop(to.hash),
      behavior: isPageNavigation ? defaultBehavior : "instant"
    };
  }
  return {
    left: 0,
    top: 0,
    behavior: isPageNavigation ? defaultBehavior : "instant"
  };
}
const configRouterOptions = {
  hashMode: false,
  scrollBehaviorType: "auto"
};
const routerOptions = {
  ...configRouterOptions,
  ...routerOptions0
};
const validate = /* @__PURE__ */ defineNuxtRouteMiddleware(async (to, from) => {
  var _a2;
  let __temp, __restore;
  if (!((_a2 = to.meta) == null ? void 0 : _a2.validate)) {
    return;
  }
  const result = ([__temp, __restore] = executeAsync(() => Promise.resolve(to.meta.validate(to))), __temp = await __temp, __restore(), __temp);
  if (result === true) {
    return;
  }
  const error = createError({
    fatal: false,
    statusCode: result && result.statusCode || 404,
    statusMessage: result && result.statusMessage || `Page Not Found: ${to.fullPath}`,
    data: {
      path: to.fullPath
    }
  });
  return error;
});
const manifest_45route_45rule = /* @__PURE__ */ defineNuxtRouteMiddleware(async (to) => {
  {
    return;
  }
});
const globalMiddleware = [
  validate,
  manifest_45route_45rule
];
const namedMiddleware = {
  admin: () => import('./admin-DS4uJVbm.mjs'),
  auth: () => import('./auth-EwQPT9JN.mjs'),
  guest: () => import('./guest-DCMvgAf0.mjs')
};
const plugin$1 = /* @__PURE__ */ defineNuxtPlugin({
  name: "nuxt:router",
  enforce: "pre",
  async setup(nuxtApp) {
    var _a2, _b, _c;
    let __temp, __restore;
    let routerBase = (/* @__PURE__ */ useRuntimeConfig()).app.baseURL;
    const history = ((_a2 = routerOptions.history) == null ? void 0 : _a2.call(routerOptions, routerBase)) ?? createMemoryHistory(routerBase);
    const routes = routerOptions.routes ? ([__temp, __restore] = executeAsync(() => routerOptions.routes(_routes)), __temp = await __temp, __restore(), __temp) ?? _routes : _routes;
    let startPosition;
    const router = createRouter({
      ...routerOptions,
      scrollBehavior: (to, from, savedPosition) => {
        if (from === START_LOCATION) {
          startPosition = savedPosition;
          return;
        }
        if (routerOptions.scrollBehavior) {
          router.options.scrollBehavior = routerOptions.scrollBehavior;
          if ("scrollRestoration" in (void 0).history) {
            const unsub = router.beforeEach(() => {
              unsub();
              (void 0).history.scrollRestoration = "manual";
            });
          }
          return routerOptions.scrollBehavior(to, START_LOCATION, startPosition || savedPosition);
        }
      },
      history,
      routes
    });
    nuxtApp.vueApp.use(router);
    const previousRoute = shallowRef(router.currentRoute.value);
    router.afterEach((_to, from) => {
      previousRoute.value = from;
    });
    Object.defineProperty(nuxtApp.vueApp.config.globalProperties, "previousRoute", {
      get: () => previousRoute.value
    });
    const initialURL = nuxtApp.ssrContext.url;
    const _route = shallowRef(router.currentRoute.value);
    const syncCurrentRoute = () => {
      _route.value = router.currentRoute.value;
    };
    nuxtApp.hook("page:finish", syncCurrentRoute);
    router.afterEach((to, from) => {
      var _a3, _b2, _c2, _d;
      if (((_b2 = (_a3 = to.matched[0]) == null ? void 0 : _a3.components) == null ? void 0 : _b2.default) === ((_d = (_c2 = from.matched[0]) == null ? void 0 : _c2.components) == null ? void 0 : _d.default)) {
        syncCurrentRoute();
      }
    });
    const route = {};
    for (const key in _route.value) {
      Object.defineProperty(route, key, {
        get: () => _route.value[key],
        enumerable: true
      });
    }
    nuxtApp._route = shallowReactive(route);
    nuxtApp._middleware || (nuxtApp._middleware = {
      global: [],
      named: {}
    });
    useError();
    if (!((_b = nuxtApp.ssrContext) == null ? void 0 : _b.islandContext)) {
      router.afterEach(async (to, _from, failure) => {
        delete nuxtApp._processingMiddleware;
        if (failure) {
          await nuxtApp.callHook("page:loading:end");
        }
        if ((failure == null ? void 0 : failure.type) === 4) {
          return;
        }
        if (to.redirectedFrom && to.fullPath !== initialURL) {
          await nuxtApp.runWithContext(() => navigateTo(to.fullPath || "/"));
        }
      });
    }
    try {
      if (true) {
        ;
        [__temp, __restore] = executeAsync(() => router.push(initialURL)), await __temp, __restore();
        ;
      }
      ;
      [__temp, __restore] = executeAsync(() => router.isReady()), await __temp, __restore();
      ;
    } catch (error2) {
      [__temp, __restore] = executeAsync(() => nuxtApp.runWithContext(() => showError(error2))), await __temp, __restore();
    }
    const resolvedInitialRoute = router.currentRoute.value;
    syncCurrentRoute();
    if ((_c = nuxtApp.ssrContext) == null ? void 0 : _c.islandContext) {
      return { provide: { router } };
    }
    const initialLayout = nuxtApp.payload.state._layout;
    router.beforeEach(async (to, from) => {
      var _a3, _b2;
      await nuxtApp.callHook("page:loading:start");
      to.meta = reactive(to.meta);
      if (nuxtApp.isHydrating && initialLayout && !isReadonly(to.meta.layout)) {
        to.meta.layout = initialLayout;
      }
      nuxtApp._processingMiddleware = true;
      if (!((_a3 = nuxtApp.ssrContext) == null ? void 0 : _a3.islandContext)) {
        const middlewareEntries = /* @__PURE__ */ new Set([...globalMiddleware, ...nuxtApp._middleware.global]);
        for (const component of to.matched) {
          const componentMiddleware = component.meta.middleware;
          if (!componentMiddleware) {
            continue;
          }
          for (const entry2 of toArray(componentMiddleware)) {
            middlewareEntries.add(entry2);
          }
        }
        {
          const routeRules = await nuxtApp.runWithContext(() => getRouteRules({ path: to.path }));
          if (routeRules.appMiddleware) {
            for (const key in routeRules.appMiddleware) {
              if (routeRules.appMiddleware[key]) {
                middlewareEntries.add(key);
              } else {
                middlewareEntries.delete(key);
              }
            }
          }
        }
        for (const entry2 of middlewareEntries) {
          const middleware = typeof entry2 === "string" ? nuxtApp._middleware.named[entry2] || await ((_b2 = namedMiddleware[entry2]) == null ? void 0 : _b2.call(namedMiddleware).then((r) => r.default || r)) : entry2;
          if (!middleware) {
            throw new Error(`Unknown route middleware: '${entry2}'.`);
          }
          try {
            const result = await nuxtApp.runWithContext(() => middleware(to, from));
            if (true) {
              if (result === false || result instanceof Error) {
                const error2 = result || createError({
                  statusCode: 404,
                  statusMessage: `Page Not Found: ${initialURL}`
                });
                await nuxtApp.runWithContext(() => showError(error2));
                return false;
              }
            }
            if (result === true) {
              continue;
            }
            if (result === false) {
              return result;
            }
            if (result) {
              if (isNuxtError(result) && result.fatal) {
                await nuxtApp.runWithContext(() => showError(result));
              }
              return result;
            }
          } catch (err) {
            const error2 = createError(err);
            if (error2.fatal) {
              await nuxtApp.runWithContext(() => showError(error2));
            }
            return error2;
          }
        }
      }
    });
    router.onError(async () => {
      delete nuxtApp._processingMiddleware;
      await nuxtApp.callHook("page:loading:end");
    });
    router.afterEach(async (to, _from) => {
      if (to.matched.length === 0) {
        await nuxtApp.runWithContext(() => showError(createError({
          statusCode: 404,
          fatal: false,
          statusMessage: `Page not found: ${to.fullPath}`,
          data: {
            path: to.fullPath
          }
        })));
      }
    });
    nuxtApp.hooks.hookOnce("app:created", async () => {
      try {
        if ("name" in resolvedInitialRoute) {
          resolvedInitialRoute.name = void 0;
        }
        await router.replace({
          ...resolvedInitialRoute,
          force: true
        });
        router.options.scrollBehavior = routerOptions.scrollBehavior;
      } catch (error2) {
        await nuxtApp.runWithContext(() => showError(error2));
      }
    });
    return { provide: { router } };
  }
});
function injectHead(nuxtApp) {
  var _a2;
  const nuxt = nuxtApp || tryUseNuxtApp();
  return ((_a2 = nuxt == null ? void 0 : nuxt.ssrContext) == null ? void 0 : _a2.head) || (nuxt == null ? void 0 : nuxt.runWithContext(() => {
    if (hasInjectionContext()) {
      return inject(headSymbol);
    }
  }));
}
function useHead(input, options = {}) {
  const head = injectHead(options.nuxt);
  if (head) {
    return useHead$1(input, { head, ...options });
  }
}
function definePayloadReducer(name, reduce) {
  {
    useNuxtApp().ssrContext._payloadReducers[name] = reduce;
  }
}
const reducers = [
  ["NuxtError", (data) => isNuxtError(data) && data.toJSON()],
  ["EmptyShallowRef", (data) => isRef(data) && isShallow(data) && !data.value && (typeof data.value === "bigint" ? "0n" : JSON.stringify(data.value) || "_")],
  ["EmptyRef", (data) => isRef(data) && !data.value && (typeof data.value === "bigint" ? "0n" : JSON.stringify(data.value) || "_")],
  ["ShallowRef", (data) => isRef(data) && isShallow(data) && data.value],
  ["ShallowReactive", (data) => isReactive(data) && isShallow(data) && toRaw(data)],
  ["Ref", (data) => isRef(data) && data.value],
  ["Reactive", (data) => isReactive(data) && toRaw(data)]
];
const revive_payload_server_MVtmlZaQpj6ApFmshWfUWl5PehCebzaBf2NuRMiIbms = /* @__PURE__ */ defineNuxtPlugin({
  name: "nuxt:revive-payload:server",
  setup() {
    for (const [reducer, fn] of reducers) {
      definePayloadReducer(reducer, fn);
    }
  }
});
/*!
 * pinia v2.3.1
 * (c) 2025 Eduardo San Martin Morote
 * @license MIT
 */
let activePinia;
const setActivePinia = (pinia) => activePinia = pinia;
const piniaSymbol = (
  /* istanbul ignore next */
  Symbol()
);
function isPlainObject(o) {
  return o && typeof o === "object" && Object.prototype.toString.call(o) === "[object Object]" && typeof o.toJSON !== "function";
}
var MutationType;
(function(MutationType2) {
  MutationType2["direct"] = "direct";
  MutationType2["patchObject"] = "patch object";
  MutationType2["patchFunction"] = "patch function";
})(MutationType || (MutationType = {}));
function createPinia() {
  const scope = effectScope(true);
  const state = scope.run(() => ref({}));
  let _p = [];
  let toBeInstalled = [];
  const pinia = markRaw({
    install(app) {
      setActivePinia(pinia);
      {
        pinia._a = app;
        app.provide(piniaSymbol, pinia);
        app.config.globalProperties.$pinia = pinia;
        toBeInstalled.forEach((plugin2) => _p.push(plugin2));
        toBeInstalled = [];
      }
    },
    use(plugin2) {
      if (!this._a && true) {
        toBeInstalled.push(plugin2);
      } else {
        _p.push(plugin2);
      }
      return this;
    },
    _p,
    // it's actually undefined here
    // @ts-expect-error
    _a: null,
    _e: scope,
    _s: /* @__PURE__ */ new Map(),
    state
  });
  return pinia;
}
const noop = () => {
};
function addSubscription(subscriptions, callback, detached, onCleanup = noop) {
  subscriptions.push(callback);
  const removeSubscription = () => {
    const idx = subscriptions.indexOf(callback);
    if (idx > -1) {
      subscriptions.splice(idx, 1);
      onCleanup();
    }
  };
  if (!detached && getCurrentScope()) {
    onScopeDispose(removeSubscription);
  }
  return removeSubscription;
}
function triggerSubscriptions(subscriptions, ...args) {
  subscriptions.slice().forEach((callback) => {
    callback(...args);
  });
}
const fallbackRunWithContext = (fn) => fn();
const ACTION_MARKER = Symbol();
const ACTION_NAME = Symbol();
function mergeReactiveObjects(target, patchToApply) {
  if (target instanceof Map && patchToApply instanceof Map) {
    patchToApply.forEach((value, key) => target.set(key, value));
  } else if (target instanceof Set && patchToApply instanceof Set) {
    patchToApply.forEach(target.add, target);
  }
  for (const key in patchToApply) {
    if (!patchToApply.hasOwnProperty(key))
      continue;
    const subPatch = patchToApply[key];
    const targetValue = target[key];
    if (isPlainObject(targetValue) && isPlainObject(subPatch) && target.hasOwnProperty(key) && !isRef(subPatch) && !isReactive(subPatch)) {
      target[key] = mergeReactiveObjects(targetValue, subPatch);
    } else {
      target[key] = subPatch;
    }
  }
  return target;
}
const skipHydrateSymbol = (
  /* istanbul ignore next */
  Symbol()
);
function shouldHydrate(obj) {
  return !isPlainObject(obj) || !obj.hasOwnProperty(skipHydrateSymbol);
}
const { assign } = Object;
function isComputed(o) {
  return !!(isRef(o) && o.effect);
}
function createOptionsStore(id, options, pinia, hot) {
  const { state, actions, getters } = options;
  const initialState = pinia.state.value[id];
  let store;
  function setup() {
    if (!initialState && (true)) {
      {
        pinia.state.value[id] = state ? state() : {};
      }
    }
    const localState = toRefs(pinia.state.value[id]);
    return assign(localState, actions, Object.keys(getters || {}).reduce((computedGetters, name) => {
      computedGetters[name] = markRaw(computed(() => {
        setActivePinia(pinia);
        const store2 = pinia._s.get(id);
        return getters[name].call(store2, store2);
      }));
      return computedGetters;
    }, {}));
  }
  store = createSetupStore(id, setup, options, pinia, hot, true);
  return store;
}
function createSetupStore($id, setup, options = {}, pinia, hot, isOptionsStore) {
  let scope;
  const optionsForPlugin = assign({ actions: {} }, options);
  const $subscribeOptions = { deep: true };
  let isListening;
  let isSyncListening;
  let subscriptions = [];
  let actionSubscriptions = [];
  let debuggerEvents;
  const initialState = pinia.state.value[$id];
  if (!isOptionsStore && !initialState && (true)) {
    {
      pinia.state.value[$id] = {};
    }
  }
  ref({});
  let activeListener;
  function $patch(partialStateOrMutator) {
    let subscriptionMutation;
    isListening = isSyncListening = false;
    if (typeof partialStateOrMutator === "function") {
      partialStateOrMutator(pinia.state.value[$id]);
      subscriptionMutation = {
        type: MutationType.patchFunction,
        storeId: $id,
        events: debuggerEvents
      };
    } else {
      mergeReactiveObjects(pinia.state.value[$id], partialStateOrMutator);
      subscriptionMutation = {
        type: MutationType.patchObject,
        payload: partialStateOrMutator,
        storeId: $id,
        events: debuggerEvents
      };
    }
    const myListenerId = activeListener = Symbol();
    nextTick().then(() => {
      if (activeListener === myListenerId) {
        isListening = true;
      }
    });
    isSyncListening = true;
    triggerSubscriptions(subscriptions, subscriptionMutation, pinia.state.value[$id]);
  }
  const $reset = isOptionsStore ? function $reset2() {
    const { state } = options;
    const newState = state ? state() : {};
    this.$patch(($state) => {
      assign($state, newState);
    });
  } : (
    /* istanbul ignore next */
    noop
  );
  function $dispose() {
    scope.stop();
    subscriptions = [];
    actionSubscriptions = [];
    pinia._s.delete($id);
  }
  const action = (fn, name = "") => {
    if (ACTION_MARKER in fn) {
      fn[ACTION_NAME] = name;
      return fn;
    }
    const wrappedAction = function() {
      setActivePinia(pinia);
      const args = Array.from(arguments);
      const afterCallbackList = [];
      const onErrorCallbackList = [];
      function after(callback) {
        afterCallbackList.push(callback);
      }
      function onError(callback) {
        onErrorCallbackList.push(callback);
      }
      triggerSubscriptions(actionSubscriptions, {
        args,
        name: wrappedAction[ACTION_NAME],
        store,
        after,
        onError
      });
      let ret;
      try {
        ret = fn.apply(this && this.$id === $id ? this : store, args);
      } catch (error) {
        triggerSubscriptions(onErrorCallbackList, error);
        throw error;
      }
      if (ret instanceof Promise) {
        return ret.then((value) => {
          triggerSubscriptions(afterCallbackList, value);
          return value;
        }).catch((error) => {
          triggerSubscriptions(onErrorCallbackList, error);
          return Promise.reject(error);
        });
      }
      triggerSubscriptions(afterCallbackList, ret);
      return ret;
    };
    wrappedAction[ACTION_MARKER] = true;
    wrappedAction[ACTION_NAME] = name;
    return wrappedAction;
  };
  const partialStore = {
    _p: pinia,
    // _s: scope,
    $id,
    $onAction: addSubscription.bind(null, actionSubscriptions),
    $patch,
    $reset,
    $subscribe(callback, options2 = {}) {
      const removeSubscription = addSubscription(subscriptions, callback, options2.detached, () => stopWatcher());
      const stopWatcher = scope.run(() => watch(() => pinia.state.value[$id], (state) => {
        if (options2.flush === "sync" ? isSyncListening : isListening) {
          callback({
            storeId: $id,
            type: MutationType.direct,
            events: debuggerEvents
          }, state);
        }
      }, assign({}, $subscribeOptions, options2)));
      return removeSubscription;
    },
    $dispose
  };
  const store = reactive(partialStore);
  pinia._s.set($id, store);
  const runWithContext = pinia._a && pinia._a.runWithContext || fallbackRunWithContext;
  const setupStore = runWithContext(() => pinia._e.run(() => (scope = effectScope()).run(() => setup({ action }))));
  for (const key in setupStore) {
    const prop = setupStore[key];
    if (isRef(prop) && !isComputed(prop) || isReactive(prop)) {
      if (!isOptionsStore) {
        if (initialState && shouldHydrate(prop)) {
          if (isRef(prop)) {
            prop.value = initialState[key];
          } else {
            mergeReactiveObjects(prop, initialState[key]);
          }
        }
        {
          pinia.state.value[$id][key] = prop;
        }
      }
    } else if (typeof prop === "function") {
      const actionValue = action(prop, key);
      {
        setupStore[key] = actionValue;
      }
      optionsForPlugin.actions[key] = prop;
    } else ;
  }
  {
    assign(store, setupStore);
    assign(toRaw(store), setupStore);
  }
  Object.defineProperty(store, "$state", {
    get: () => pinia.state.value[$id],
    set: (state) => {
      $patch(($state) => {
        assign($state, state);
      });
    }
  });
  pinia._p.forEach((extender) => {
    {
      assign(store, scope.run(() => extender({
        store,
        app: pinia._a,
        pinia,
        options: optionsForPlugin
      })));
    }
  });
  if (initialState && isOptionsStore && options.hydrate) {
    options.hydrate(store.$state, initialState);
  }
  isListening = true;
  isSyncListening = true;
  return store;
}
/*! #__NO_SIDE_EFFECTS__ */
// @__NO_SIDE_EFFECTS__
function defineStore(idOrOptions, setup, setupOptions) {
  let id;
  let options;
  const isSetupStore = typeof setup === "function";
  if (typeof idOrOptions === "string") {
    id = idOrOptions;
    options = isSetupStore ? setupOptions : setup;
  } else {
    options = idOrOptions;
    id = idOrOptions.id;
  }
  function useStore(pinia, hot) {
    const hasContext = hasInjectionContext();
    pinia = // in test mode, ignore the argument provided as we can always retrieve a
    // pinia instance with getActivePinia()
    (pinia) || (hasContext ? inject(piniaSymbol, null) : null);
    if (pinia)
      setActivePinia(pinia);
    pinia = activePinia;
    if (!pinia._s.has(id)) {
      if (isSetupStore) {
        createSetupStore(id, setup, options, pinia);
      } else {
        createOptionsStore(id, options, pinia);
      }
    }
    const store = pinia._s.get(id);
    return store;
  }
  useStore.$id = id;
  return useStore;
}
function storeToRefs(store) {
  {
    const rawStore = toRaw(store);
    const refs = {};
    for (const key in rawStore) {
      const value = rawStore[key];
      if (value.effect) {
        refs[key] = // ...
        computed({
          get: () => store[key],
          set(value2) {
            store[key] = value2;
          }
        });
      } else if (isRef(value) || isReactive(value)) {
        refs[key] = // ---
        toRef(store, key);
      }
    }
    return refs;
  }
}
defineComponent({
  name: "ServerPlaceholder",
  render() {
    return createElementBlock("div");
  }
});
const clientOnlySymbol = Symbol.for("nuxt:client-only");
defineComponent({
  name: "ClientOnly",
  inheritAttrs: false,
  props: ["fallback", "placeholder", "placeholderTag", "fallbackTag"],
  setup(props, { slots, attrs }) {
    const mounted = shallowRef(false);
    const vm = getCurrentInstance();
    if (vm) {
      vm._nuxtClientOnly = true;
    }
    provide(clientOnlySymbol, true);
    return () => {
      var _a2;
      if (mounted.value) {
        const vnodes = (_a2 = slots.default) == null ? void 0 : _a2.call(slots);
        if (vnodes && vnodes.length === 1) {
          return [cloneVNode(vnodes[0], attrs)];
        }
        return vnodes;
      }
      const slot = slots.fallback || slots.placeholder;
      if (slot) {
        return h(slot);
      }
      const fallbackStr = props.fallback || props.placeholder || "";
      const fallbackTag = props.fallbackTag || props.placeholderTag || "span";
      return createElementBlock(fallbackTag, attrs, fallbackStr);
    };
  }
});
const useStateKeyPrefix = "$s";
function useState(...args) {
  const autoKey = typeof args[args.length - 1] === "string" ? args.pop() : void 0;
  if (typeof args[0] !== "string") {
    args.unshift(autoKey);
  }
  const [_key, init] = args;
  if (!_key || typeof _key !== "string") {
    throw new TypeError("[nuxt] [useState] key must be a string: " + _key);
  }
  if (init !== void 0 && typeof init !== "function") {
    throw new Error("[nuxt] [useState] init must be a function: " + init);
  }
  const key = useStateKeyPrefix + _key;
  const nuxtApp = useNuxtApp();
  const state = toRef(nuxtApp.payload.state, key);
  if (state.value === void 0 && init) {
    const initialValue = init();
    if (isRef(initialValue)) {
      nuxtApp.payload.state[key] = initialValue;
      return initialValue;
    }
    state.value = initialValue;
  }
  return state;
}
const firstNonUndefined = (...args) => args.find((arg) => arg !== void 0);
// @__NO_SIDE_EFFECTS__
function defineNuxtLink(options) {
  const componentName = options.componentName || "NuxtLink";
  function isHashLinkWithoutHashMode(link) {
    return typeof link === "string" && link.startsWith("#");
  }
  function resolveTrailingSlashBehavior(to, resolve, trailingSlash) {
    const effectiveTrailingSlash = trailingSlash ?? options.trailingSlash;
    if (!to || effectiveTrailingSlash !== "append" && effectiveTrailingSlash !== "remove") {
      return to;
    }
    if (typeof to === "string") {
      return applyTrailingSlashBehavior(to, effectiveTrailingSlash);
    }
    const path = "path" in to && to.path !== void 0 ? to.path : resolve(to).path;
    const resolvedPath = {
      ...to,
      name: void 0,
      // named routes would otherwise always override trailing slash behavior
      path: applyTrailingSlashBehavior(path, effectiveTrailingSlash)
    };
    return resolvedPath;
  }
  function useNuxtLink(props) {
    const router = useRouter();
    const config2 = /* @__PURE__ */ useRuntimeConfig();
    const hasTarget = computed(() => !!props.target && props.target !== "_self");
    const isAbsoluteUrl = computed(() => {
      const path = props.to || props.href || "";
      return typeof path === "string" && hasProtocol(path, { acceptRelative: true });
    });
    const builtinRouterLink = resolveComponent("RouterLink");
    const useBuiltinLink = builtinRouterLink && typeof builtinRouterLink !== "string" ? builtinRouterLink.useLink : void 0;
    const isExternal = computed(() => {
      if (props.external) {
        return true;
      }
      const path = props.to || props.href || "";
      if (typeof path === "object") {
        return false;
      }
      return path === "" || isAbsoluteUrl.value;
    });
    const to = computed(() => {
      const path = props.to || props.href || "";
      if (isExternal.value) {
        return path;
      }
      return resolveTrailingSlashBehavior(path, router.resolve, props.trailingSlash);
    });
    const link = isExternal.value ? void 0 : useBuiltinLink == null ? void 0 : useBuiltinLink({ ...props, to });
    const href = computed(() => {
      var _a2;
      const effectiveTrailingSlash = props.trailingSlash ?? options.trailingSlash;
      if (!to.value || isAbsoluteUrl.value || isHashLinkWithoutHashMode(to.value)) {
        return to.value;
      }
      if (isExternal.value) {
        const path = typeof to.value === "object" && "path" in to.value ? resolveRouteObject(to.value) : to.value;
        const href2 = typeof path === "object" ? router.resolve(path).href : path;
        return applyTrailingSlashBehavior(href2, effectiveTrailingSlash);
      }
      if (typeof to.value === "object") {
        return ((_a2 = router.resolve(to.value)) == null ? void 0 : _a2.href) ?? null;
      }
      return applyTrailingSlashBehavior(joinURL(config2.app.baseURL, to.value), effectiveTrailingSlash);
    });
    return {
      to,
      hasTarget,
      isAbsoluteUrl,
      isExternal,
      //
      href,
      isActive: (link == null ? void 0 : link.isActive) ?? computed(() => to.value === router.currentRoute.value.path),
      isExactActive: (link == null ? void 0 : link.isExactActive) ?? computed(() => to.value === router.currentRoute.value.path),
      route: (link == null ? void 0 : link.route) ?? computed(() => router.resolve(to.value)),
      async navigate(_e) {
        await navigateTo(href.value, { replace: props.replace, external: isExternal.value || hasTarget.value });
      }
    };
  }
  return defineComponent({
    name: componentName,
    props: {
      // Routing
      to: {
        type: [String, Object],
        default: void 0,
        required: false
      },
      href: {
        type: [String, Object],
        default: void 0,
        required: false
      },
      // Attributes
      target: {
        type: String,
        default: void 0,
        required: false
      },
      rel: {
        type: String,
        default: void 0,
        required: false
      },
      noRel: {
        type: Boolean,
        default: void 0,
        required: false
      },
      // Prefetching
      prefetch: {
        type: Boolean,
        default: void 0,
        required: false
      },
      prefetchOn: {
        type: [String, Object],
        default: void 0,
        required: false
      },
      noPrefetch: {
        type: Boolean,
        default: void 0,
        required: false
      },
      // Styling
      activeClass: {
        type: String,
        default: void 0,
        required: false
      },
      exactActiveClass: {
        type: String,
        default: void 0,
        required: false
      },
      prefetchedClass: {
        type: String,
        default: void 0,
        required: false
      },
      // Vue Router's `<RouterLink>` additional props
      replace: {
        type: Boolean,
        default: void 0,
        required: false
      },
      ariaCurrentValue: {
        type: String,
        default: void 0,
        required: false
      },
      // Edge cases handling
      external: {
        type: Boolean,
        default: void 0,
        required: false
      },
      // Slot API
      custom: {
        type: Boolean,
        default: void 0,
        required: false
      },
      // Behavior
      trailingSlash: {
        type: String,
        default: void 0,
        required: false
      }
    },
    useLink: useNuxtLink,
    setup(props, { slots }) {
      const router = useRouter();
      const { to, href, navigate, isExternal, hasTarget, isAbsoluteUrl } = useNuxtLink(props);
      shallowRef(false);
      const el = void 0;
      const elRef = void 0;
      async function prefetch(nuxtApp = useNuxtApp()) {
        {
          return;
        }
      }
      return () => {
        var _a2;
        if (!isExternal.value && !hasTarget.value && !isHashLinkWithoutHashMode(to.value)) {
          const routerLinkProps = {
            ref: elRef,
            to: to.value,
            activeClass: props.activeClass || options.activeClass,
            exactActiveClass: props.exactActiveClass || options.exactActiveClass,
            replace: props.replace,
            ariaCurrentValue: props.ariaCurrentValue,
            custom: props.custom
          };
          if (!props.custom) {
            routerLinkProps.rel = props.rel || void 0;
          }
          return h(
            resolveComponent("RouterLink"),
            routerLinkProps,
            slots.default
          );
        }
        const target = props.target || null;
        const rel = firstNonUndefined(
          // converts `""` to `null` to prevent the attribute from being added as empty (`rel=""`)
          props.noRel ? "" : props.rel,
          options.externalRelAttribute,
          /*
          * A fallback rel of `noopener noreferrer` is applied for external links or links that open in a new tab.
          * This solves a reverse tabnapping security flaw in browsers pre-2021 as well as improving privacy.
          */
          isAbsoluteUrl.value || hasTarget.value ? "noopener noreferrer" : ""
        ) || null;
        if (props.custom) {
          if (!slots.default) {
            return null;
          }
          return slots.default({
            href: href.value,
            navigate,
            prefetch,
            get route() {
              if (!href.value) {
                return void 0;
              }
              const url = new URL(href.value, "http://localhost");
              return {
                path: url.pathname,
                fullPath: url.pathname,
                get query() {
                  return parseQuery(url.search);
                },
                hash: url.hash,
                params: {},
                name: void 0,
                matched: [],
                redirectedFrom: void 0,
                meta: {},
                href: href.value
              };
            },
            rel,
            target,
            isExternal: isExternal.value || hasTarget.value,
            isActive: false,
            isExactActive: false
          });
        }
        return h("a", {
          ref: el,
          href: href.value || null,
          // converts `""` to `null` to prevent the attribute from being added as empty (`href=""`)
          rel,
          target,
          onClick: (event) => {
            if (isExternal.value || hasTarget.value) {
              return;
            }
            event.preventDefault();
            return props.replace ? router.replace(href.value) : router.push(href.value);
          }
        }, (_a2 = slots.default) == null ? void 0 : _a2.call(slots));
      };
    }
    // }) as unknown as DefineComponent<NuxtLinkProps, object, object, ComputedOptions, MethodOptions, object, object, EmitsOptions, string, object, NuxtLinkProps, object, SlotsType<NuxtLinkSlots>>
  });
}
const __nuxt_component_0$2 = /* @__PURE__ */ defineNuxtLink(nuxtLinkDefaults);
function applyTrailingSlashBehavior(to, trailingSlash) {
  const normalizeFn = trailingSlash === "append" ? withTrailingSlash : withoutTrailingSlash;
  const hasProtocolDifferentFromHttp = hasProtocol(to) && !to.startsWith("http");
  if (hasProtocolDifferentFromHttp) {
    return to;
  }
  return normalizeFn(to, true);
}
const inlineConfig = {
  "nuxt": {},
  "icon": {
    "provider": "server",
    "class": "",
    "aliases": {},
    "iconifyApiEndpoint": "https://api.iconify.design",
    "localApiEndpoint": "/api/_nuxt_icon",
    "fallbackToApi": true,
    "cssSelectorPrefix": "i-",
    "cssWherePseudo": true,
    "mode": "css",
    "attrs": {
      "aria-hidden": true
    },
    "collections": [
      "academicons",
      "akar-icons",
      "ant-design",
      "arcticons",
      "basil",
      "bi",
      "bitcoin-icons",
      "bpmn",
      "brandico",
      "bx",
      "bxl",
      "bxs",
      "bytesize",
      "carbon",
      "catppuccin",
      "cbi",
      "charm",
      "ci",
      "cib",
      "cif",
      "cil",
      "circle-flags",
      "circum",
      "clarity",
      "codicon",
      "covid",
      "cryptocurrency",
      "cryptocurrency-color",
      "dashicons",
      "devicon",
      "devicon-plain",
      "ei",
      "el",
      "emojione",
      "emojione-monotone",
      "emojione-v1",
      "entypo",
      "entypo-social",
      "eos-icons",
      "ep",
      "et",
      "eva",
      "f7",
      "fa",
      "fa-brands",
      "fa-regular",
      "fa-solid",
      "fa6-brands",
      "fa6-regular",
      "fa6-solid",
      "fad",
      "fe",
      "feather",
      "file-icons",
      "flag",
      "flagpack",
      "flat-color-icons",
      "flat-ui",
      "flowbite",
      "fluent",
      "fluent-emoji",
      "fluent-emoji-flat",
      "fluent-emoji-high-contrast",
      "fluent-mdl2",
      "fontelico",
      "fontisto",
      "formkit",
      "foundation",
      "fxemoji",
      "gala",
      "game-icons",
      "geo",
      "gg",
      "gis",
      "gravity-ui",
      "gridicons",
      "grommet-icons",
      "guidance",
      "healthicons",
      "heroicons",
      "heroicons-outline",
      "heroicons-solid",
      "hugeicons",
      "humbleicons",
      "ic",
      "icomoon-free",
      "icon-park",
      "icon-park-outline",
      "icon-park-solid",
      "icon-park-twotone",
      "iconamoon",
      "iconoir",
      "icons8",
      "il",
      "ion",
      "iwwa",
      "jam",
      "la",
      "lets-icons",
      "line-md",
      "logos",
      "ls",
      "lucide",
      "lucide-lab",
      "mage",
      "majesticons",
      "maki",
      "map",
      "marketeq",
      "material-symbols",
      "material-symbols-light",
      "mdi",
      "mdi-light",
      "medical-icon",
      "memory",
      "meteocons",
      "mi",
      "mingcute",
      "mono-icons",
      "mynaui",
      "nimbus",
      "nonicons",
      "noto",
      "noto-v1",
      "octicon",
      "oi",
      "ooui",
      "openmoji",
      "oui",
      "pajamas",
      "pepicons",
      "pepicons-pencil",
      "pepicons-pop",
      "pepicons-print",
      "ph",
      "pixelarticons",
      "prime",
      "ps",
      "quill",
      "radix-icons",
      "raphael",
      "ri",
      "rivet-icons",
      "si-glyph",
      "simple-icons",
      "simple-line-icons",
      "skill-icons",
      "solar",
      "streamline",
      "streamline-emojis",
      "subway",
      "svg-spinners",
      "system-uicons",
      "tabler",
      "tdesign",
      "teenyicons",
      "token",
      "token-branded",
      "topcoat",
      "twemoji",
      "typcn",
      "uil",
      "uim",
      "uis",
      "uit",
      "uiw",
      "unjs",
      "vaadin",
      "vs",
      "vscode-icons",
      "websymbol",
      "weui",
      "whh",
      "wi",
      "wpf",
      "zmdi",
      "zondicons"
    ],
    "fetchTimeout": 1500
  },
  "ui": {
    "primary": "green",
    "gray": "cool",
    "colors": [
      "red",
      "orange",
      "amber",
      "yellow",
      "lime",
      "green",
      "emerald",
      "teal",
      "cyan",
      "sky",
      "blue",
      "indigo",
      "violet",
      "purple",
      "fuchsia",
      "pink",
      "rose",
      "primary"
    ],
    "strategy": "merge"
  }
};
const appConfig = /* @__PURE__ */ defuFn(inlineConfig);
function useAppConfig() {
  const nuxtApp = useNuxtApp();
  nuxtApp._appConfig || (nuxtApp._appConfig = klona(appConfig));
  return nuxtApp._appConfig;
}
const plugin = /* @__PURE__ */ defineNuxtPlugin({
  name: "pinia",
  setup(nuxtApp) {
    const pinia = createPinia();
    nuxtApp.vueApp.use(pinia);
    setActivePinia(pinia);
    {
      nuxtApp.payload.pinia = pinia.state.value;
    }
    return {
      provide: {
        pinia
      }
    };
  }
});
const LazyIcon = defineAsyncComponent(() => import('./index-BRDSpBS1.mjs').then((r) => r["default"] || r.default || r));
const lazyGlobalComponents = [
  ["Icon", LazyIcon]
];
const components_plugin_z4hgvsiddfKkfXTP6M8M4zG5Cb7sGnDhcryKVM45Di4 = /* @__PURE__ */ defineNuxtPlugin({
  name: "nuxt:global-components",
  setup(nuxtApp) {
    for (const [name, component] of lazyGlobalComponents) {
      nuxtApp.vueApp.component(name, component);
      nuxtApp.vueApp.component("Lazy" + name, component);
    }
  }
});
const slidOverInjectionKey = Symbol("nuxt-ui.slideover");
function _useSlideover() {
  const slideoverState = inject(slidOverInjectionKey);
  const isOpen = ref(false);
  function open(component, props) {
    if (!slideoverState) {
      throw new Error("useSlideover() is called without provider");
    }
    slideoverState.value = {
      component,
      props: props ?? {}
    };
    isOpen.value = true;
  }
  async function close() {
    if (!slideoverState) return;
    isOpen.value = false;
  }
  function reset() {
    slideoverState.value = {
      component: "div",
      props: {}
    };
  }
  function patch(props) {
    if (!slideoverState) return;
    slideoverState.value = {
      ...slideoverState.value,
      props: {
        ...slideoverState.value.props,
        ...props
      }
    };
  }
  return {
    open,
    close,
    reset,
    patch,
    isOpen
  };
}
createSharedComposable(_useSlideover);
const slideovers_g1RyjnFCC2XCqS3NLpv7yngCZ5AzjpmT7UJr_dW3BbY = /* @__PURE__ */ defineNuxtPlugin((nuxtApp) => {
  const slideoverState = shallowRef({
    component: "div",
    props: {}
  });
  nuxtApp.vueApp.provide(slidOverInjectionKey, slideoverState);
});
const modalInjectionKey = Symbol("nuxt-ui.modal");
function _useModal() {
  const modalState = inject(modalInjectionKey);
  const isOpen = ref(false);
  function open(component, props) {
    if (!modalState) {
      throw new Error("useModal() is called without provider");
    }
    modalState.value = {
      component,
      props: props ?? {}
    };
    isOpen.value = true;
  }
  async function close() {
    if (!modalState) return;
    isOpen.value = false;
  }
  function reset() {
    modalState.value = {
      component: "div",
      props: {}
    };
  }
  function patch(props) {
    if (!modalState) return;
    modalState.value = {
      ...modalState.value,
      props: {
        ...modalState.value.props,
        ...props
      }
    };
  }
  return {
    open,
    close,
    reset,
    patch,
    isOpen
  };
}
createSharedComposable(_useModal);
const modals_JhH8M1KzF3pQyhcHsoNTBLd8tKGet6zo2zTBVDe7nK4 = /* @__PURE__ */ defineNuxtPlugin((nuxtApp) => {
  const modalState = shallowRef({
    component: "div",
    props: {}
  });
  nuxtApp.vueApp.provide(modalInjectionKey, modalState);
});
function omit(object, keysToOmit) {
  const result = { ...object };
  for (const key of keysToOmit) {
    delete result[key];
  }
  return result;
}
function get(object, path, defaultValue) {
  if (typeof path === "string") {
    path = path.split(".").map((key) => {
      const numKey = Number(key);
      return Number.isNaN(numKey) ? key : numKey;
    });
  }
  let result = object;
  for (const key of path) {
    if (result === void 0 || result === null) {
      return defaultValue;
    }
    result = result[key];
  }
  return result !== void 0 ? result : defaultValue;
}
const twMerge = extendTailwindMerge(defu({
  extend: {
    classGroups: {
      icons: [(classPart) => classPart.startsWith("i-")]
    }
  }
}, (_a = appConfig.ui) == null ? void 0 : _a.tailwindMerge));
const defuTwMerge = createDefu((obj, key, value, namespace) => {
  if (namespace === "default" || namespace.startsWith("default.")) {
    return false;
  }
  if (namespace === "popper" || namespace.startsWith("popper.")) {
    return false;
  }
  if (namespace.endsWith("avatar") && key === "size") {
    return false;
  }
  if (namespace.endsWith("chip") && key === "size") {
    return false;
  }
  if (namespace.endsWith("badge") && key === "size" || key === "color" || key === "variant") {
    return false;
  }
  if (typeof obj[key] === "string" && typeof value === "string" && obj[key] && value) {
    obj[key] = twMerge(obj[key], value);
    return true;
  }
});
function mergeConfig(strategy, ...configs) {
  if (strategy === "override") {
    return defu({}, ...configs);
  }
  return defuTwMerge({}, ...configs);
}
const rxHex = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i;
function parseConfigValue(value) {
  return rxHex.test(value) ? hexToRgb(value) : value;
}
function hexToRgb(hex) {
  const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
  hex = hex.replace(shorthandRegex, function(_, r, g, b) {
    return r + r + g + g + b + b;
  });
  const result = rxHex.exec(hex);
  return result ? `${Number.parseInt(result[1], 16)} ${Number.parseInt(result[2], 16)} ${Number.parseInt(result[3], 16)}` : null;
}
function looseToNumber(val) {
  const n = Number.parseFloat(val);
  return Number.isNaN(n) ? val : n;
}
const _inherit = "inherit";
const _current = "currentColor";
const _transparent = "transparent";
const _black = "#000";
const _white = "#fff";
const _slate = { "50": "#f8fafc", "100": "#f1f5f9", "200": "#e2e8f0", "300": "#cbd5e1", "400": "#94a3b8", "500": "#64748b", "600": "#475569", "700": "#334155", "800": "#1e293b", "900": "#0f172a", "950": "#020617" };
const _gray = { "50": "rgb(var(--color-gray-50) / <alpha-value>)", "100": "rgb(var(--color-gray-100) / <alpha-value>)", "200": "rgb(var(--color-gray-200) / <alpha-value>)", "300": "rgb(var(--color-gray-300) / <alpha-value>)", "400": "rgb(var(--color-gray-400) / <alpha-value>)", "500": "rgb(var(--color-gray-500) / <alpha-value>)", "600": "rgb(var(--color-gray-600) / <alpha-value>)", "700": "rgb(var(--color-gray-700) / <alpha-value>)", "800": "rgb(var(--color-gray-800) / <alpha-value>)", "900": "rgb(var(--color-gray-900) / <alpha-value>)", "950": "rgb(var(--color-gray-950) / <alpha-value>)" };
const _zinc = { "50": "#fafafa", "100": "#f4f4f5", "200": "#e4e4e7", "300": "#d4d4d8", "400": "#a1a1aa", "500": "#71717a", "600": "#52525b", "700": "#3f3f46", "800": "#27272a", "900": "#18181b", "950": "#09090b" };
const _neutral = { "50": "#fafafa", "100": "#f5f5f5", "200": "#e5e5e5", "300": "#d4d4d4", "400": "#a3a3a3", "500": "#737373", "600": "#525252", "700": "#404040", "800": "#262626", "900": "#171717", "950": "#0a0a0a" };
const _stone = { "50": "#fafaf9", "100": "#f5f5f4", "200": "#e7e5e4", "300": "#d6d3d1", "400": "#a8a29e", "500": "#78716c", "600": "#57534e", "700": "#44403c", "800": "#292524", "900": "#1c1917", "950": "#0c0a09" };
const _red = { "50": "#fef2f2", "100": "#fee2e2", "200": "#fecaca", "300": "#fca5a5", "400": "#f87171", "500": "#ef4444", "600": "#dc2626", "700": "#b91c1c", "800": "#991b1b", "900": "#7f1d1d", "950": "#450a0a" };
const _orange = { "50": "#fff7ed", "100": "#ffedd5", "200": "#fed7aa", "300": "#fdba74", "400": "#fb923c", "500": "#f97316", "600": "#ea580c", "700": "#c2410c", "800": "#9a3412", "900": "#7c2d12", "950": "#431407" };
const _amber = { "50": "#fffbeb", "100": "#fef3c7", "200": "#fde68a", "300": "#fcd34d", "400": "#fbbf24", "500": "#f59e0b", "600": "#d97706", "700": "#b45309", "800": "#92400e", "900": "#78350f", "950": "#451a03" };
const _yellow = { "50": "#fefce8", "100": "#fef9c3", "200": "#fef08a", "300": "#fde047", "400": "#facc15", "500": "#eab308", "600": "#ca8a04", "700": "#a16207", "800": "#854d0e", "900": "#713f12", "950": "#422006" };
const _lime = { "50": "#f7fee7", "100": "#ecfccb", "200": "#d9f99d", "300": "#bef264", "400": "#a3e635", "500": "#84cc16", "600": "#65a30d", "700": "#4d7c0f", "800": "#3f6212", "900": "#365314", "950": "#1a2e05" };
const _green = { "50": "#f0fdf4", "100": "#dcfce7", "200": "#bbf7d0", "300": "#86efac", "400": "#4ade80", "500": "#22c55e", "600": "#16a34a", "700": "#15803d", "800": "#166534", "900": "#14532d", "950": "#052e16" };
const _emerald = { "50": "#ecfdf5", "100": "#d1fae5", "200": "#a7f3d0", "300": "#6ee7b7", "400": "#34d399", "500": "#10b981", "600": "#059669", "700": "#047857", "800": "#065f46", "900": "#064e3b", "950": "#022c22" };
const _teal = { "50": "#f0fdfa", "100": "#ccfbf1", "200": "#99f6e4", "300": "#5eead4", "400": "#2dd4bf", "500": "#14b8a6", "600": "#0d9488", "700": "#0f766e", "800": "#115e59", "900": "#134e4a", "950": "#042f2e" };
const _cyan = { "50": "#ecfeff", "100": "#cffafe", "200": "#a5f3fc", "300": "#67e8f9", "400": "#22d3ee", "500": "#06b6d4", "600": "#0891b2", "700": "#0e7490", "800": "#155e75", "900": "#164e63", "950": "#083344" };
const _sky = { "50": "#f0f9ff", "100": "#e0f2fe", "200": "#bae6fd", "300": "#7dd3fc", "400": "#38bdf8", "500": "#0ea5e9", "600": "#0284c7", "700": "#0369a1", "800": "#075985", "900": "#0c4a6e", "950": "#082f49" };
const _blue = { "50": "#eff6ff", "100": "#dbeafe", "200": "#bfdbfe", "300": "#93c5fd", "400": "#60a5fa", "500": "#3b82f6", "600": "#2563eb", "700": "#1d4ed8", "800": "#1e40af", "900": "#1e3a8a", "950": "#172554" };
const _indigo = { "50": "#eef2ff", "100": "#e0e7ff", "200": "#c7d2fe", "300": "#a5b4fc", "400": "#818cf8", "500": "#6366f1", "600": "#4f46e5", "700": "#4338ca", "800": "#3730a3", "900": "#312e81", "950": "#1e1b4b" };
const _violet = { "50": "#f5f3ff", "100": "#ede9fe", "200": "#ddd6fe", "300": "#c4b5fd", "400": "#a78bfa", "500": "#8b5cf6", "600": "#7c3aed", "700": "#6d28d9", "800": "#5b21b6", "900": "#4c1d95", "950": "#2e1065" };
const _purple = { "50": "#faf5ff", "100": "#f3e8ff", "200": "#e9d5ff", "300": "#d8b4fe", "400": "#c084fc", "500": "#a855f7", "600": "#9333ea", "700": "#7e22ce", "800": "#6b21a8", "900": "#581c87", "950": "#3b0764" };
const _fuchsia = { "50": "#fdf4ff", "100": "#fae8ff", "200": "#f5d0fe", "300": "#f0abfc", "400": "#e879f9", "500": "#d946ef", "600": "#c026d3", "700": "#a21caf", "800": "#86198f", "900": "#701a75", "950": "#4a044e" };
const _pink = { "50": "#fdf2f8", "100": "#fce7f3", "200": "#fbcfe8", "300": "#f9a8d4", "400": "#f472b6", "500": "#ec4899", "600": "#db2777", "700": "#be185d", "800": "#9d174d", "900": "#831843", "950": "#500724" };
const _rose = { "50": "#fff1f2", "100": "#ffe4e6", "200": "#fecdd3", "300": "#fda4af", "400": "#fb7185", "500": "#f43f5e", "600": "#e11d48", "700": "#be123c", "800": "#9f1239", "900": "#881337", "950": "#4c0519" };
const _primary = { "50": "rgb(var(--color-primary-50) / <alpha-value>)", "100": "rgb(var(--color-primary-100) / <alpha-value>)", "200": "rgb(var(--color-primary-200) / <alpha-value>)", "300": "rgb(var(--color-primary-300) / <alpha-value>)", "400": "rgb(var(--color-primary-400) / <alpha-value>)", "500": "rgb(var(--color-primary-500) / <alpha-value>)", "600": "rgb(var(--color-primary-600) / <alpha-value>)", "700": "rgb(var(--color-primary-700) / <alpha-value>)", "800": "rgb(var(--color-primary-800) / <alpha-value>)", "900": "rgb(var(--color-primary-900) / <alpha-value>)", "950": "rgb(var(--color-primary-950) / <alpha-value>)", "DEFAULT": "rgb(var(--color-primary-DEFAULT) / <alpha-value>)" };
const _cool = { "50": "#f9fafb", "100": "#f3f4f6", "200": "#e5e7eb", "300": "#d1d5db", "400": "#9ca3af", "500": "#6b7280", "600": "#4b5563", "700": "#374151", "800": "#1f2937", "900": "#111827", "950": "#030712" };
const config = { "inherit": _inherit, "current": _current, "transparent": _transparent, "black": _black, "white": _white, "slate": _slate, "gray": _gray, "zinc": _zinc, "neutral": _neutral, "stone": _stone, "red": _red, "orange": _orange, "amber": _amber, "yellow": _yellow, "lime": _lime, "green": _green, "emerald": _emerald, "teal": _teal, "cyan": _cyan, "sky": _sky, "blue": _blue, "indigo": _indigo, "violet": _violet, "purple": _purple, "fuchsia": _fuchsia, "pink": _pink, "rose": _rose, "primary": _primary, "cool": _cool };
const colors_E7kSti5pGZ28QhUUurq6gGRU3l65WuXO_KJC3GQgzFo = /* @__PURE__ */ defineNuxtPlugin(() => {
  const appConfig2 = useAppConfig();
  useNuxtApp();
  const root = computed(() => {
    const primary = get(config, appConfig2.ui.primary);
    const gray = get(config, appConfig2.ui.gray);
    if (!primary) {
      console.warn(`[@nuxt/ui] Primary color '${appConfig2.ui.primary}' not found in Tailwind config`);
    }
    if (!gray) {
      console.warn(`[@nuxt/ui] Gray color '${appConfig2.ui.gray}' not found in Tailwind config`);
    }
    return `:root {
${Object.entries(primary || config.green).map(([key, value]) => `--color-primary-${key}: ${parseConfigValue(value)};`).join("\n")}
--color-primary-DEFAULT: var(--color-primary-500);

${Object.entries(gray || config.cool).map(([key, value]) => `--color-gray-${key}: ${parseConfigValue(value)};`).join("\n")}
}

.dark {
  --color-primary-DEFAULT: var(--color-primary-400);
}
`;
  });
  const headData = {
    style: [{
      innerHTML: () => root.value,
      tagPriority: -2,
      id: "nuxt-ui-colors"
    }]
  };
  useHead(headData);
});
const preference = "system";
const dataValue = "theme";
const plugin_server_9Ca9_HhnjAGwBWpwAydRauMHxWoxTDY60BrArRnXN_A = /* @__PURE__ */ defineNuxtPlugin((nuxtApp) => {
  var _a2;
  const colorMode = ((_a2 = nuxtApp.ssrContext) == null ? void 0 : _a2.islandContext) ? ref({}) : useState("color-mode", () => reactive({
    preference,
    value: preference,
    unknown: true,
    forced: false
  })).value;
  const htmlAttrs = {};
  {
    useHead({ htmlAttrs });
  }
  useRouter().afterEach((to) => {
    const forcedColorMode = to.meta.colorMode;
    if (forcedColorMode && forcedColorMode !== "system") {
      colorMode.value = htmlAttrs["data-color-mode-forced"] = forcedColorMode;
      {
        htmlAttrs[`data-${dataValue}`] = colorMode.value;
      }
      colorMode.forced = true;
    } else if (forcedColorMode === "system") {
      console.warn("You cannot force the colorMode to system at the page level.");
    }
  });
  nuxtApp.provide("colorMode", colorMode);
});
const plugin_MeUvTuoKUi51yb_kBguab6hdcExVXeTtZtTg9TZZBB8 = /* @__PURE__ */ defineNuxtPlugin({
  name: "@nuxt/icon",
  setup() {
    var _a2, _b;
    const configs = /* @__PURE__ */ useRuntimeConfig();
    const options = useAppConfig().icon;
    _api.setFetch($fetch.native);
    const resources = [];
    if (options.provider === "server") {
      const baseURL2 = ((_b = (_a2 = configs.app) == null ? void 0 : _a2.baseURL) == null ? void 0 : _b.replace(/\/$/, "")) ?? "";
      resources.push(baseURL2 + (options.localApiEndpoint || "/api/_nuxt_icon"));
      if (options.fallbackToApi === true || options.fallbackToApi === "client-only") {
        resources.push(options.iconifyApiEndpoint);
      }
    } else if (options.provider === "none") {
      _api.setFetch(() => Promise.resolve(new Response()));
    } else {
      resources.push(options.iconifyApiEndpoint);
    }
    async function customIconLoader(icons, prefix) {
      try {
        const data = await $fetch(resources[0] + "/" + prefix + ".json", {
          query: {
            icons: icons.join(",")
          }
        });
        if (!data || data.prefix !== prefix || !data.icons)
          throw new Error("Invalid data" + JSON.stringify(data));
        return data;
      } catch (e) {
        console.error("Failed to load custom icons", e);
        return null;
      }
    }
    addAPIProvider("", { resources });
    for (const prefix of options.customCollections || []) {
      if (prefix)
        setCustomIconsLoader(customIconLoader, prefix);
    }
  }
  // For type portability
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
});
const plugins = [
  unhead_k2P3m_ZDyjlr2mMYnoDPwavjsDN8hBlk9cFai0bbopU,
  plugin$1,
  revive_payload_server_MVtmlZaQpj6ApFmshWfUWl5PehCebzaBf2NuRMiIbms,
  plugin,
  components_plugin_z4hgvsiddfKkfXTP6M8M4zG5Cb7sGnDhcryKVM45Di4,
  slideovers_g1RyjnFCC2XCqS3NLpv7yngCZ5AzjpmT7UJr_dW3BbY,
  modals_JhH8M1KzF3pQyhcHsoNTBLd8tKGet6zo2zTBVDe7nK4,
  colors_E7kSti5pGZ28QhUUurq6gGRU3l65WuXO_KJC3GQgzFo,
  plugin_server_9Ca9_HhnjAGwBWpwAydRauMHxWoxTDY60BrArRnXN_A,
  plugin_MeUvTuoKUi51yb_kBguab6hdcExVXeTtZtTg9TZZBB8
];
const defineRouteProvider = (name = "RouteProvider") => defineComponent({
  name,
  props: {
    route: {
      type: Object,
      required: true
    },
    vnode: Object,
    vnodeRef: Object,
    renderKey: String,
    trackRootNodes: Boolean
  },
  setup(props) {
    const previousKey = props.renderKey;
    const previousRoute = props.route;
    const route = {};
    for (const key in props.route) {
      Object.defineProperty(route, key, {
        get: () => previousKey === props.renderKey ? props.route[key] : previousRoute[key],
        enumerable: true
      });
    }
    provide(PageRouteSymbol, shallowReactive(route));
    return () => {
      if (!props.vnode) {
        return props.vnode;
      }
      return h(props.vnode, { ref: props.vnodeRef });
    };
  }
});
const RouteProvider = defineRouteProvider();
const __nuxt_component_0$1 = defineComponent({
  name: "NuxtPage",
  inheritAttrs: false,
  props: {
    name: {
      type: String
    },
    transition: {
      type: [Boolean, Object],
      default: void 0
    },
    keepalive: {
      type: [Boolean, Object],
      default: void 0
    },
    route: {
      type: Object
    },
    pageKey: {
      type: [Function, String],
      default: null
    }
  },
  setup(props, { attrs, slots, expose }) {
    const nuxtApp = useNuxtApp();
    const pageRef = ref();
    inject(PageRouteSymbol, null);
    expose({ pageRef });
    inject(LayoutMetaSymbol, null);
    nuxtApp.deferHydration();
    return () => {
      return h(RouterView, { name: props.name, route: props.route, ...attrs }, {
        default: (routeProps) => {
          return h(Suspense, { suspensible: true }, {
            default() {
              return h(RouteProvider, {
                vnode: slots.default ? normalizeSlot(slots.default, routeProps) : routeProps.Component,
                route: routeProps.route,
                vnodeRef: pageRef
              });
            }
          });
        }
      });
    };
  }
});
function normalizeSlot(slot, data) {
  const slotContent = slot(data);
  return slotContent.length === 1 ? h(slotContent[0]) : h(Fragment, void 0, slotContent);
}
const useApi = () => {
  const config2 = /* @__PURE__ */ useRuntimeConfig();
  const getBaseURL = () => {
    const apiBaseUrl = config2.public.apiBaseUrl || config2.public.backendUrl;
    if (apiBaseUrl && apiBaseUrl !== "/api") {
      console.log("[API] Using production API URL:", apiBaseUrl);
      return apiBaseUrl;
    }
    const productionUrl = "https://project.mercylife.cc/api";
    console.log("[API] Using fallback production URL:", productionUrl);
    return productionUrl;
  };
  const baseURL2 = getBaseURL();
  console.log("[API] Base URL resolved to:", baseURL2);
  const getAuthToken = () => {
    return null;
  };
  const getAuthHeaders = () => {
    return {};
  };
  const apiRequest = async (endpoint, options = {}) => {
    var _a2, _b, _c, _d, _e, _f;
    const authHeaders = getAuthHeaders();
    const defaultOptions = {
      baseURL: baseURL2,
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        ...authHeaders,
        ...options.headers
      },
      ...options
    };
    try {
      console.log(`[API] ${defaultOptions.method || "GET"} ${baseURL2}${endpoint}`);
      const response = await $fetch(endpoint, defaultOptions);
      console.log(`[API] Success: ${baseURL2}${endpoint}`);
      return {
        success: true,
        data: response,
        error: null
      };
    } catch (error) {
      console.error(`[API] Error ${defaultOptions.method || "GET"} ${baseURL2}${endpoint}:`, error);
      console.error("[API] Full URL:", `${baseURL2}${endpoint}`);
      console.error("[API] Options:", defaultOptions);
      if (error.status === 401 || error.statusCode === 401) {
        console.warn("[API] Authentication error - clearing auth state");
      }
      const errorDetails = {
        message: ((_a2 = error.data) == null ? void 0 : _a2.message) || error.message || "請求失敗",
        status: error.status || error.statusCode || 500,
        statusText: error.statusText || error.statusMessage || "Internal Server Error",
        url: `${baseURL2}${endpoint}`,
        method: defaultOptions.method || "GET",
        errors: ((_b = error.data) == null ? void 0 : _b.errors) || null
      };
      if (errorDetails.status === 404) {
        errorDetails.message = `API endpoint not found: ${errorDetails.url}`;
      } else if (errorDetails.status === 401) {
        errorDetails.message = ((_c = error.data) == null ? void 0 : _c.message) || "登入已過期，請重新登入";
      } else if (errorDetails.status === 403) {
        errorDetails.message = ((_d = error.data) == null ? void 0 : _d.message) || "權限不足";
      } else if (errorDetails.status === 422) {
        errorDetails.message = ((_e = error.data) == null ? void 0 : _e.message) || "表單驗證失敗";
      } else if (errorDetails.status >= 500) {
        errorDetails.message = ((_f = error.data) == null ? void 0 : _f.message) || "伺服器錯誤，請稍後再試";
      }
      return {
        success: false,
        data: null,
        error: errorDetails
      };
    }
  };
  const get2 = async (endpoint, params = {}) => {
    return await apiRequest(endpoint, {
      method: "GET",
      params
    });
  };
  const post = async (endpoint, body = {}) => {
    return await apiRequest(endpoint, {
      method: "POST",
      body
    });
  };
  const put = async (endpoint, body = {}) => {
    return await apiRequest(endpoint, {
      method: "PUT",
      body
    });
  };
  const patch = async (endpoint, body = {}) => {
    return await apiRequest(endpoint, {
      method: "PATCH",
      body
    });
  };
  const del2 = async (endpoint) => {
    return await apiRequest(endpoint, {
      method: "DELETE"
    });
  };
  const setAuthToken = (token) => {
  };
  const clearAuthToken = () => {
  };
  return {
    get: get2,
    post,
    put,
    patch,
    delete: del2,
    apiRequest,
    getAuthToken,
    getAuthHeaders,
    setAuthToken,
    clearAuthToken
  };
};
const useWebsiteSettingsApi = () => {
  const api = useApi();
  const getSettings = async () => {
    return await api.get("/website-settings");
  };
  const updateSettings = async (settings) => {
    return await api.post("/website-settings", settings);
  };
  const getSetting = async (key) => {
    return await api.get(`/website-settings/${key}`);
  };
  const resetDefaults = async () => {
    return await api.post("/website-settings/reset-defaults");
  };
  return {
    getSettings,
    updateSettings,
    getSetting,
    resetDefaults
  };
};
const useWebsiteSettingsStore = /* @__PURE__ */ defineStore("websiteSettings", () => {
  const { resetDefaults: apiResetDefaults } = useWebsiteSettingsApi();
  const websiteName = ref("專案管理系統");
  const websiteSecondaryName = ref("Project Management");
  const websiteTitle = ref("專案管理系統");
  const showLogo = ref(false);
  const logoUrl = ref("");
  const faviconUrl = ref("/favicon.ico");
  const enableMultilingual = ref(true);
  const enableSearch = ref(true);
  const enableNotifications = ref(true);
  const showFooter = ref(true);
  const showTime = ref(false);
  const enableDarkMode = ref(true);
  const themeMode = ref("system");
  const primaryColor = ref("#6366f1");
  const loadSettings = async () => {
  };
  const loadFromLocalStorage = () => {
  };
  const saveToLocalStorage = (markAsModified = false) => {
  };
  const saveSettings = async () => {
  };
  const applyThemeSettings = () => {
  };
  const updateDocumentTitle = (pageTitle = null) => {
  };
  const updateFavicon = () => {
  };
  const uploadLogo = async (file) => {
    return new Promise((resolve, reject) => {
      if (!file) {
        reject(new Error("No file provided"));
        return;
      }
      const reader = new FileReader();
      reader.onload = (e) => {
        const dataURL = e.target.result;
        logoUrl.value = dataURL;
        showLogo.value = true;
        saveSettings();
        resolve(dataURL);
      };
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  };
  const uploadFavicon = async (file) => {
    return new Promise((resolve, reject) => {
      if (!file) {
        reject(new Error("No file provided"));
        return;
      }
      const reader = new FileReader();
      reader.onload = (e) => {
        const dataURL = e.target.result;
        faviconUrl.value = dataURL;
        saveSettings();
        resolve(dataURL);
      };
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  };
  const resetToDefaults = async () => {
    try {
      const response = await apiResetDefaults();
      if (response.success) {
        await loadSettings();
        return;
      }
    } catch (error) {
      console.warn("API reset failed, using local defaults:", error);
    }
    websiteName.value = "專案管理系統";
    websiteSecondaryName.value = "Project Management";
    websiteTitle.value = "專案管理系統";
    showLogo.value = false;
    logoUrl.value = "";
    faviconUrl.value = "/favicon.ico";
    enableMultilingual.value = false;
    enableSearch.value = true;
    enableNotifications.value = true;
    showFooter.value = true;
    showTime.value = false;
    enableDarkMode.value = true;
    themeMode.value = "system";
    primaryColor.value = "#6366f1";
    await saveSettings();
  };
  const displayName = computed(() => {
    return showLogo.value && logoUrl.value ? "" : websiteName.value;
  });
  const displaySecondaryName = computed(() => {
    return showLogo.value && logoUrl.value ? "" : websiteSecondaryName.value;
  });
  return {
    // State
    websiteName,
    websiteSecondaryName,
    websiteTitle,
    showLogo,
    logoUrl,
    faviconUrl,
    enableMultilingual,
    enableSearch,
    enableNotifications,
    showFooter,
    showTime,
    enableDarkMode,
    themeMode,
    primaryColor,
    // Computed
    displayName,
    displaySecondaryName,
    // Methods
    loadSettings,
    loadFromLocalStorage,
    saveSettings,
    saveToLocalStorage,
    updateDocumentTitle,
    updateFavicon,
    uploadLogo,
    uploadFavicon,
    resetToDefaults,
    applyThemeSettings
  };
});
const _sfc_main$6 = {
  __name: "app",
  __ssrInlineRender: true,
  setup(__props) {
    const websiteSettingsStore = useWebsiteSettingsStore();
    useHead({
      title: "Admin Template",
      meta: [
        { name: "description", content: "Modern admin template built with Nuxt 3" }
      ]
    });
    watch(() => websiteSettingsStore.websiteTitle, (newTitle) => {
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtPage = __nuxt_component_0$1;
      _push(ssrRenderComponent(_component_NuxtPage, _attrs, null, _parent));
    };
  }
};
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("app.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const _sfc_main$5 = {
  __name: "SidebarMenuItem",
  __ssrInlineRender: true,
  props: {
    item: {
      type: Object,
      required: true
    },
    collapsed: {
      type: Boolean,
      default: false
    }
  },
  setup(__props) {
    const props = __props;
    const route = useRoute();
    const isExpanded = ref(false);
    const showTooltip = ref(false);
    const showCollapsedSubmenu = ref(false);
    ref(null);
    const isCurrentRoute = computed(() => {
      if (props.item.href === route.path) {
        return true;
      }
      if (props.item.children) {
        return props.item.children.some((child) => child.href === route.path);
      }
      return false;
    });
    watch(() => route.path, (newPath) => {
      if (props.item.children) {
        const hasActiveChild = props.item.children.some((child) => child.href === newPath);
        if (hasActiveChild) {
          isExpanded.value = true;
        }
      }
    }, { immediate: true });
    const iconComponents = {
      ChartBarIcon,
      CogIcon,
      QuestionMarkCircleIcon,
      FolderIcon,
      UsersIcon,
      UserGroupIcon
    };
    const getIcon = (iconName) => {
      return iconComponents[iconName] || ChartBarIcon;
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLink = __nuxt_component_0$2;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "relative" }, _attrs))}><button class="${ssrRenderClass([{
        "justify-center": __props.collapsed,
        "bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400": unref(isCurrentRoute)
      }, "w-full flex items-center px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 group"])}">`);
      ssrRenderVNode(_push, createVNode(resolveDynamicComponent(getIcon(__props.item.icon)), {
        class: ["w-5 h-5 transition-colors duration-200", unref(isCurrentRoute) ? "text-primary-600 dark:text-primary-400" : "text-gray-500 group-hover:text-primary-500"]
      }, null), _parent);
      if (!__props.collapsed) {
        _push(`<div class="flex items-center justify-between flex-1 ml-3 overflow-hidden"><span class="font-medium whitespace-nowrap">${ssrInterpolate(__props.item.name)}</span>`);
        if (__props.item.children) {
          _push(ssrRenderComponent(unref(ChevronDownIcon), {
            class: ["w-4 h-4 transition-transform duration-300 flex-shrink-0", { "rotate-180": unref(isExpanded) }]
          }, null, _parent));
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</button>`);
      if (__props.collapsed && unref(showTooltip) && !__props.item.children) {
        _push(`<div class="absolute left-full top-1/2 ml-3 px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg pointer-events-none z-50 whitespace-nowrap shadow-lg" style="${ssrRenderStyle({ "transform": "translateY(-50%)" })}">${ssrInterpolate(__props.item.name)} <div class="absolute right-full top-1/2 w-0 h-0 border-t-4 border-b-4 border-r-4 border-transparent border-r-gray-900 dark:border-r-gray-700" style="${ssrRenderStyle({ "transform": "translateY(-50%)" })}"></div></div>`);
      } else {
        _push(`<!---->`);
      }
      if (unref(isExpanded) && !__props.collapsed && __props.item.children) {
        _push(`<div class="ml-8 mt-2 space-y-1 overflow-hidden"><!--[-->`);
        ssrRenderList(__props.item.children, (child) => {
          _push(ssrRenderComponent(_component_NuxtLink, {
            key: child.name,
            to: child.href,
            class: "block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-500 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-all duration-200"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`${ssrInterpolate(child.name)}`);
              } else {
                return [
                  createTextVNode(toDisplayString(child.name), 1)
                ];
              }
            }),
            _: 2
          }, _parent));
        });
        _push(`<!--]--></div>`);
      } else {
        _push(`<!---->`);
      }
      if (__props.collapsed && unref(showCollapsedSubmenu) && __props.item.children) {
        _push(`<div class="absolute left-full top-0 ml-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 min-w-48"><div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700"><span class="text-sm font-medium text-gray-700 dark:text-gray-300">${ssrInterpolate(__props.item.name)}</span></div><div class="py-1"><!--[-->`);
        ssrRenderList(__props.item.children, (child) => {
          _push(ssrRenderComponent(_component_NuxtLink, {
            key: child.name,
            to: child.href,
            class: "block px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200",
            onClick: ($event) => showCollapsedSubmenu.value = false
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`${ssrInterpolate(child.name)}`);
              } else {
                return [
                  createTextVNode(toDisplayString(child.name), 1)
                ];
              }
            }),
            _: 2
          }, _parent));
        });
        _push(`<!--]--></div><div class="absolute right-full top-4 w-0 h-0 border-t-4 border-b-4 border-r-4 border-transparent border-r-white dark:border-r-gray-800"></div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
    };
  }
};
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("components/SidebarMenuItem.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const useSidebarStore = /* @__PURE__ */ defineStore("sidebar", () => {
  const collapsed = ref(false);
  const mobileOpen = ref(false);
  const transitioning = ref(false);
  const toggleSidebar = async () => {
    if (transitioning.value) return;
    transitioning.value = true;
    collapsed.value = !collapsed.value;
    setTimeout(() => {
      transitioning.value = false;
    }, 300);
  };
  const toggleMobileSidebar = () => {
    mobileOpen.value = !mobileOpen.value;
  };
  const closeMobileSidebar = () => {
    mobileOpen.value = false;
  };
  const setSidebarState = (isCollapsed) => {
    if (collapsed.value !== isCollapsed) {
      toggleSidebar();
    }
  };
  return {
    sidebarCollapsed: collapsed,
    sidebarMobileOpen: mobileOpen,
    sidebarTransitioning: transitioning,
    toggleSidebar,
    toggleMobileSidebar,
    closeMobileSidebar,
    setSidebarState
  };
});
const useSettingsStore = /* @__PURE__ */ defineStore("settings", () => {
  const showFootbar = ref(true);
  const sidebarMenuItems = ref([
    {
      name: "儀表板",
      icon: "ChartBarIcon",
      href: "/"
    },
    {
      name: "專案管理",
      icon: "FolderIcon",
      children: [
        { name: "專案列表", href: "/projects" },
        { name: "新增專案", href: "/projects/create" }
      ]
    },
    {
      name: "業主管理",
      icon: "UsersIcon",
      children: [
        { name: "業主列表", href: "/clients" },
        { name: "新增業主", href: "/clients/create" }
      ]
    },
    {
      name: "用戶管理",
      icon: "UserGroupIcon",
      children: [
        { name: "用戶列表", href: "/settings/users" }
      ]
    },
    {
      name: "設定",
      icon: "CogIcon",
      children: [
        { name: "設定總覽", href: "/settings" },
        { name: "主題設定", href: "/settings/theme" },
        { name: "網站設定", href: "/settings/website" },
        { name: "介面設定", href: "/settings/ui" }
      ]
    }
  ]);
  const toggleFootbar = () => {
    showFootbar.value = !showFootbar.value;
  };
  const updateMenuItems = (newItems) => {
    sidebarMenuItems.value = newItems;
  };
  return {
    showFootbar,
    sidebarMenuItems,
    toggleFootbar,
    updateMenuItems
  };
});
const useAuthStore = /* @__PURE__ */ defineStore("auth", () => {
  const user = ref(null);
  const token = ref(null);
  const isLoggedIn = computed(() => !!user.value && !!token.value);
  const isAdmin = computed(() => {
    var _a2;
    return ((_a2 = user.value) == null ? void 0 : _a2.role) === "admin";
  });
  const isLoading = ref(false);
  const login = async (credentials) => {
    var _a2;
    try {
      isLoading.value = true;
      const loginData = {
        login: credentials.username,
        password: credentials.password
      };
      const { post } = useApi();
      const response = await post("/auth/login", loginData);
      if (!response.success) {
        throw new Error(((_a2 = response.error) == null ? void 0 : _a2.message) || "登入失敗");
      }
      const { user: userData, token: userToken } = response.data.data;
      user.value = userData;
      token.value = userToken;
      if (false) ;
      return { success: true, user: userData, token: userToken };
    } catch (error) {
      console.error("Login error:", error);
      throw new Error(error.message || "登入失敗");
    } finally {
      isLoading.value = false;
    }
  };
  const logout = async (skipApiCall = false) => {
    try {
      if (token.value && !skipApiCall) {
        const { post } = useApi();
        await post("/auth/logout");
      }
    } catch (error) {
      console.error("Logout API error:", error);
    } finally {
      user.value = null;
      token.value = null;
      await navigateTo("/auth/login");
    }
  };
  const fetchUser = async () => {
    try {
      if (!token.value) return null;
      const { get: get2 } = useApi();
      const response = await get2("/auth/me");
      if (!response.success) {
        throw new Error("Failed to fetch user data");
      }
      const userData = response.data.data.user;
      user.value = userData;
      if (false) ;
      return userData;
    } catch (error) {
      console.error("Fetch user error:", error);
      await logout(true);
      return null;
    }
  };
  const initializeAuth = async () => {
  };
  const updateProfile = async (profileData) => {
    var _a2;
    try {
      const { put } = useApi();
      const response = await put("/profile", profileData);
      if (!response.success) {
        throw new Error(((_a2 = response.error) == null ? void 0 : _a2.message) || "更新失敗");
      }
      const updatedUser = response.data.data.user;
      user.value = updatedUser;
      if (false) ;
      return { success: true, user: updatedUser };
    } catch (error) {
      console.error("Update profile error:", error);
      throw new Error(error.message || "更新失敗");
    }
  };
  const changePassword = async (passwordData) => {
    var _a2;
    try {
      const { put } = useApi();
      const response = await put("/auth/change-password", passwordData);
      if (!response.success) {
        throw new Error(((_a2 = response.error) == null ? void 0 : _a2.message) || "密碼更改失敗");
      }
      return { success: true, message: "密碼更改成功" };
    } catch (error) {
      console.error("Change password error:", error);
      throw new Error(error.message || "密碼更改失敗");
    }
  };
  const refreshToken = async () => {
    try {
      const { post } = useApi();
      const response = await post("/auth/refresh");
      if (!response.success) {
        throw new Error("Token refresh failed");
      }
      const newToken = response.data.data.token;
      token.value = newToken;
      if (false) ;
      return newToken;
    } catch (error) {
      console.error("Refresh token error:", error);
      await logout(true);
      throw error;
    }
  };
  return {
    // 狀態
    user: readonly(user),
    token: readonly(token),
    isLoggedIn,
    isAdmin,
    isLoading: readonly(isLoading),
    // 認證方法
    login,
    logout,
    initializeAuth,
    fetchUser,
    // 用戶管理
    updateProfile,
    changePassword,
    refreshToken
  };
});
const _export_sfc = (sfc, props) => {
  const target = sfc.__vccOpts || sfc;
  for (const [key, val] of props) {
    target[key] = val;
  }
  return target;
};
const _sfc_main$4 = {
  __name: "AppSidebar",
  __ssrInlineRender: true,
  setup(__props) {
    const sidebarStore = useSidebarStore();
    const { sidebarCollapsed, sidebarMobileOpen, sidebarTransitioning } = storeToRefs(sidebarStore);
    const { toggleSidebar, closeMobileSidebar } = sidebarStore;
    const settingsStore = useSettingsStore();
    const { sidebarMenuItems } = storeToRefs(settingsStore);
    const websiteSettingsStore = useWebsiteSettingsStore();
    const { websiteName, showLogo, logoUrl, displayName, displaySecondaryName } = storeToRefs(websiteSettingsStore);
    const authStore = useAuthStore();
    const { isLoading } = storeToRefs(authStore);
    const { logout } = authStore;
    const menuItems = computed(() => sidebarMenuItems.value);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_SidebarMenuItem = _sfc_main$5;
      _push(`<div${ssrRenderAttrs(_attrs)} data-v-68d18aac><aside class="${ssrRenderClass([[
        unref(sidebarCollapsed) ? "sidebar-collapsed" : "sidebar-expanded",
        { "sidebar-transitioning": unref(sidebarTransitioning) }
      ], "fixed top-0 left-0 h-full bg-white dark:bg-gray-800 shadow-lg transition-sidebar z-40 hidden lg:block flex flex-col"])}" data-v-68d18aac><div class="h-16 flex items-center justify-center border-b border-gray-200 dark:border-gray-700 px-4" data-v-68d18aac>`);
      if (!unref(sidebarCollapsed)) {
        _push(`<div class="flex flex-col items-center" data-v-68d18aac>`);
        if (unref(showLogo) && unref(logoUrl)) {
          _push(`<div class="h-10 flex items-center" data-v-68d18aac><img${ssrRenderAttr("src", unref(logoUrl))}${ssrRenderAttr("alt", unref(websiteName))} class="h-8 object-contain" data-v-68d18aac></div>`);
        } else {
          _push(`<div class="text-center" data-v-68d18aac><div class="text-lg font-bold text-gray-800 dark:text-white truncate" data-v-68d18aac>${ssrInterpolate(unref(displayName))}</div>`);
          if (unref(displaySecondaryName)) {
            _push(`<div class="text-xs text-gray-600 dark:text-gray-400 truncate" data-v-68d18aac>${ssrInterpolate(unref(displaySecondaryName))}</div>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</div>`);
        }
        _push(`</div>`);
      } else {
        _push(`<div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" data-v-68d18aac>`);
        if (unref(showLogo) && unref(logoUrl)) {
          _push(`<div data-v-68d18aac><img${ssrRenderAttr("src", unref(logoUrl))}${ssrRenderAttr("alt", unref(websiteName))} class="w-8 h-8 object-contain" data-v-68d18aac></div>`);
        } else {
          _push(`<div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center" data-v-68d18aac><span class="text-white font-bold text-sm" data-v-68d18aac>${ssrInterpolate((unref(displayName) || "A").charAt(0).toUpperCase())}</span></div>`);
        }
        _push(`</div>`);
      }
      _push(`</div><nav class="flex-1 px-4 py-6 space-y-2" data-v-68d18aac><!--[-->`);
      ssrRenderList(unref(menuItems), (item) => {
        _push(ssrRenderComponent(_component_SidebarMenuItem, {
          key: item.name,
          item,
          collapsed: unref(sidebarCollapsed),
          class: "transition-all duration-300"
        }, null, _parent));
      });
      _push(`<!--]--></nav><div class="p-4 border-t border-gray-200 dark:border-gray-700" data-v-68d18aac><button${ssrIncludeBooleanAttr(unref(isLoading)) ? " disabled" : ""} class="${ssrRenderClass([{ "justify-center": unref(sidebarCollapsed) }, "w-full flex items-center px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"])}" data-v-68d18aac>`);
      _push(ssrRenderComponent(unref(ArrowRightOnRectangleIcon), { class: "w-5 h-5 flex-shrink-0" }, null, _parent));
      if (!unref(sidebarCollapsed)) {
        _push(`<span class="ml-3 whitespace-nowrap" data-v-68d18aac>${ssrInterpolate(unref(isLoading) ? "登出中..." : "登出")}</span>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</button></div><button${ssrIncludeBooleanAttr(unref(sidebarTransitioning)) ? " disabled" : ""} class="${ssrRenderClass([{ "cursor-not-allowed opacity-50": unref(sidebarTransitioning) }, "absolute -right-3 top-6 w-6 h-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 hover:scale-110 transition-all duration-200 shadow-md"])}"${ssrRenderAttr("title", unref(sidebarCollapsed) ? "展開側邊欄" : "收起側邊欄")} data-v-68d18aac>`);
      _push(ssrRenderComponent(unref(ChevronLeftIcon), {
        class: ["w-4 h-4 text-gray-500 transition-transform duration-300", { "rotate-180": unref(sidebarCollapsed) }]
      }, null, _parent));
      _push(`</button></aside>`);
      if (unref(sidebarMobileOpen)) {
        _push(`<div class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" data-v-68d18aac></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<aside class="${ssrRenderClass([[
        unref(sidebarMobileOpen) ? "translate-x-0" : "-translate-x-full"
      ], "fixed top-0 left-0 h-full sidebar-expanded bg-white dark:bg-gray-800 shadow-xl transition-transform duration-300 z-50 lg:hidden flex flex-col sidebar-mobile"])}" data-v-68d18aac><div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-700" data-v-68d18aac><div class="flex items-center space-x-3" data-v-68d18aac>`);
      if (unref(showLogo) && unref(logoUrl)) {
        _push(`<div class="h-10 flex items-center" data-v-68d18aac><img${ssrRenderAttr("src", unref(logoUrl))}${ssrRenderAttr("alt", unref(websiteName))} class="h-8 object-contain" data-v-68d18aac></div>`);
      } else {
        _push(`<div data-v-68d18aac><div class="text-lg font-bold text-gray-800 dark:text-white" data-v-68d18aac>${ssrInterpolate(unref(displayName))}</div>`);
        if (unref(displaySecondaryName)) {
          _push(`<div class="text-xs text-gray-600 dark:text-gray-400" data-v-68d18aac>${ssrInterpolate(unref(displaySecondaryName))}</div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      }
      _push(`</div><button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" data-v-68d18aac>`);
      _push(ssrRenderComponent(unref(XMarkIcon), { class: "w-5 h-5" }, null, _parent));
      _push(`</button></div><nav class="flex-1 px-4 py-6 space-y-2" data-v-68d18aac><!--[-->`);
      ssrRenderList(unref(menuItems), (item) => {
        _push(ssrRenderComponent(_component_SidebarMenuItem, {
          key: item.name,
          item,
          collapsed: false,
          onClick: unref(closeMobileSidebar)
        }, null, _parent));
      });
      _push(`<!--]--></nav><div class="p-4 border-t border-gray-200 dark:border-gray-700" data-v-68d18aac><button${ssrIncludeBooleanAttr(unref(isLoading)) ? " disabled" : ""} class="w-full flex items-center px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed" data-v-68d18aac>`);
      _push(ssrRenderComponent(unref(ArrowRightOnRectangleIcon), { class: "w-5 h-5" }, null, _parent));
      _push(`<span class="ml-3" data-v-68d18aac>${ssrInterpolate(unref(isLoading) ? "登出中..." : "登出")}</span></button></div></aside></div>`);
    };
  }
};
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("components/AppSidebar.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const __nuxt_component_0 = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["__scopeId", "data-v-68d18aac"]]);
const useI18n = () => {
  const locale = useState("locale", () => "zh-TW");
  const translations = {
    "zh-TW": () => import('./zh-TW-D1vc__r3.mjs').then((m) => m.default),
    "en": () => import('./en-CQ9WJudz.mjs').then((m) => m.default),
    "ja": () => import('./ja-1i-umakd.mjs').then((m) => m.default)
  };
  const messages = useState("messages", () => ({}));
  const loadMessages = async (lang) => {
    if (!messages.value[lang]) {
      try {
        const msgs = await translations[lang]();
        messages.value[lang] = msgs;
      } catch (error) {
        console.warn(`Failed to load translations for ${lang}:`, error);
        messages.value[lang] = {};
      }
    }
  };
  const getValue = (obj, path) => {
    return path.split(".").reduce((current, key) => current == null ? void 0 : current[key], obj);
  };
  const t = (key, fallback = key) => {
    const currentMessages = messages.value[locale.value];
    if (!currentMessages) return fallback;
    const value = getValue(currentMessages, key);
    return value !== void 0 ? value : fallback;
  };
  const locales = ref([
    {
      code: "zh-TW",
      name: "繁體中文",
      flag: "🇹🇼"
    },
    {
      code: "en",
      name: "English",
      flag: "🇺🇸"
    },
    {
      code: "ja",
      name: "日本語",
      flag: "🇯🇵"
    }
  ]);
  watch(locale, async (newLocale) => {
    await loadMessages(newLocale);
  });
  return {
    locale,
    locales,
    t
  };
};
const intervalError = "[nuxt] `setInterval` should not be used on the server. Consider wrapping it with an `onNuxtReady`, `onBeforeMount` or `onMounted` lifecycle hook, or ensure you only call it in the browser by checking `false`.";
const setInterval = () => {
  console.error(intervalError);
};
const useNotificationsStore = /* @__PURE__ */ defineStore("notifications", () => {
  const notifications = ref([
    {
      id: 1,
      type: "system",
      title: "notifications.system_update",
      message: "A new system update is available. Please update to get the latest features.",
      time: new Date(Date.now() - 5 * 60 * 1e3),
      read: false,
      priority: "high",
      icon: "ExclamationCircleIcon"
    },
    {
      id: 2,
      type: "user",
      title: "notifications.user_registration",
      message: 'New user "john.doe@example.com" has registered.',
      time: new Date(Date.now() - 10 * 60 * 1e3),
      read: false,
      priority: "medium",
      icon: "UserPlusIcon"
    },
    {
      id: 3,
      type: "report",
      title: "notifications.daily_report",
      message: "Your daily analytics report is ready for review.",
      time: new Date(Date.now() - 60 * 60 * 1e3),
      read: true,
      priority: "low",
      icon: "DocumentTextIcon"
    },
    {
      id: 4,
      type: "security",
      title: "Security Alert",
      message: "Unusual login activity detected from a new device.",
      time: new Date(Date.now() - 2 * 60 * 60 * 1e3),
      read: false,
      priority: "high",
      icon: "ShieldExclamationIcon"
    }
  ]);
  const unreadCount = computed(
    () => notifications.value.filter((n) => !n.read).length
  );
  const priorityNotifications = computed(
    () => notifications.value.filter((n) => n.priority === "high" && !n.read).slice(0, 3)
  );
  const recentNotifications = computed(
    () => notifications.value.sort((a, b) => b.time - a.time).slice(0, 5)
  );
  const addNotification = (notification) => {
    const newNotification = {
      id: Date.now(),
      type: notification.type || "info",
      title: notification.title,
      message: notification.message,
      time: /* @__PURE__ */ new Date(),
      read: false,
      priority: notification.priority || "medium",
      icon: notification.icon || "InformationCircleIcon"
    };
    notifications.value.unshift(newNotification);
    if (notification.autoRemove !== false) {
      setTimeout(() => {
        removeNotification(newNotification.id);
      }, 3e4);
    }
  };
  const markAsRead = (id) => {
    const notification = notifications.value.find((n) => n.id === id);
    if (notification) {
      notification.read = true;
    }
  };
  const markAllAsRead = () => {
    notifications.value.forEach((n) => {
      n.read = true;
    });
  };
  const removeNotification = (id) => {
    const index = notifications.value.findIndex((n) => n.id === id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  };
  const clearAllNotifications = () => {
    notifications.value = [];
  };
  const clearReadNotifications = () => {
    notifications.value = notifications.value.filter((n) => !n.read);
  };
  const getTimeAgo = (time) => {
    const now = /* @__PURE__ */ new Date();
    const diff = now - time;
    const minutes = Math.floor(diff / (1e3 * 60));
    const hours = Math.floor(diff / (1e3 * 60 * 60));
    const days = Math.floor(diff / (1e3 * 60 * 60 * 24));
    if (minutes < 1) return "Just now";
    if (minutes < 60) return `${minutes} minutes ago`;
    if (hours < 24) return `${hours} hour${hours > 1 ? "s" : ""} ago`;
    return `${days} day${days > 1 ? "s" : ""} ago`;
  };
  const simulateRealTimeNotifications = () => {
    setInterval();
  };
  return {
    notifications: readonly(notifications),
    unreadCount,
    priorityNotifications,
    recentNotifications,
    addNotification,
    markAsRead,
    markAllAsRead,
    removeNotification,
    clearAllNotifications,
    clearReadNotifications,
    getTimeAgo,
    simulateRealTimeNotifications
  };
});
const useColorMode = () => {
  return useState("color-mode").value;
};
const useThemeStore = /* @__PURE__ */ defineStore("theme", () => {
  const websiteSettingsStore = useWebsiteSettingsStore();
  const { primaryColor } = storeToRefs(websiteSettingsStore);
  const setPrimaryColor = (color) => {
    websiteSettingsStore.primaryColor = color;
    websiteSettingsStore.saveSettings();
  };
  const initializePrimaryColor = () => {
  };
  return {
    primaryColor,
    setPrimaryColor,
    initializePrimaryColor
  };
});
const useTheme = () => {
  const colorMode = useColorMode();
  const themeStore = useThemeStore();
  const websiteSettingsStore = useWebsiteSettingsStore();
  const isDark = computed(() => colorMode.value === "dark");
  const isLight = computed(() => colorMode.value === "light");
  const isSystem = computed(() => colorMode.preference === "system");
  const toggleTheme = () => {
    const websiteSettingsStore2 = useWebsiteSettingsStore();
    if (!websiteSettingsStore2.enableDarkMode) {
      return;
    }
    const newMode = colorMode.value === "dark" ? "light" : "dark";
    setTheme(newMode);
  };
  const setTheme = (mode) => {
    if (!websiteSettingsStore.enableDarkMode && mode === "dark") {
      return;
    }
    colorMode.preference = mode;
    websiteSettingsStore.themeMode = mode;
    {
      websiteSettingsStore.saveSettings();
    }
  };
  const setPrimaryColor = (color) => {
    themeStore.setPrimaryColor(color);
  };
  const initializeTheme = () => {
  };
  return {
    // State
    isDark,
    isLight,
    isSystem,
    colorMode,
    primaryColor: computed(() => themeStore.primaryColor),
    // Methods
    toggleTheme,
    setTheme,
    setPrimaryColor,
    initializeTheme
  };
};
const _sfc_main$3 = {
  __name: "AppNavbar",
  __ssrInlineRender: true,
  setup(__props) {
    const { t, locales } = useI18n();
    const route = useRoute();
    const sidebarStore = useSidebarStore();
    const { toggleMobileSidebar } = sidebarStore;
    const notificationsStore = useNotificationsStore();
    const { recentNotifications, unreadCount, markAsRead, markAllAsRead, clearReadNotifications } = notificationsStore;
    const { isDark } = useTheme();
    const websiteSettingsStore = useWebsiteSettingsStore();
    const { enableSearch, enableMultilingual, enableNotifications, enableDarkMode } = storeToRefs(websiteSettingsStore);
    const pageTitle = computed(() => {
      const titles = {
        "/": t("nav.dashboard"),
        "/dashboard": t("nav.dashboard"),
        "/dashboard/crm": t("nav.crm"),
        "/dashboard/ecommerce": t("nav.ecommerce"),
        "/profile": t("common.profile"),
        "/settings": t("nav.settings"),
        "/settings/general": t("nav.general_settings"),
        "/settings/theme": t("nav.theme_settings"),
        "/settings/ui": t("nav.ui_settings"),
        "/settings/users": t("nav.user_management"),
        "/help": t("nav.help_center"),
        "/help/faq": t("nav.faq"),
        "/help/support": t("nav.support"),
        "/help/docs": t("nav.docs"),
        "/clients": "業主管理",
        "/clients/create": "新增業主",
        "/projects": "專案管理",
        "/projects/create": "新增專案"
      };
      return titles[route.path] || "Page";
    });
    const currentPageTitle = computed(() => pageTitle.value);
    const breadcrumbItems = computed(() => {
      const pathSegments = route.path.split("/").filter((segment) => segment);
      const items = [];
      let currentPath = "";
      for (let i = 0; i < pathSegments.length; i++) {
        currentPath += "/" + pathSegments[i];
        const segmentTitles = {
          "/dashboard": t("nav.dashboards"),
          "/dashboard/crm": t("nav.crm"),
          "/dashboard/ecommerce": t("nav.ecommerce"),
          "/profile": t("common.profile"),
          "/settings": t("nav.settings"),
          "/settings/general": t("nav.general_settings"),
          "/settings/theme": t("nav.theme_settings"),
          "/settings/ui": t("nav.ui_settings"),
          "/settings/users": t("nav.user_management"),
          "/help": t("nav.help_center"),
          "/help/faq": t("nav.faq"),
          "/help/support": t("nav.support"),
          "/help/docs": t("nav.docs"),
          "/clients": "業主管理",
          "/clients/create": "新增業主",
          "/projects": "專案管理",
          "/projects/create": "新增專案"
        };
        const title = segmentTitles[currentPath] || pathSegments[i];
        items.push({
          name: title,
          href: currentPath
        });
      }
      return items;
    });
    const showSearch = ref(false);
    const showLanguage = ref(false);
    const showNotifications = ref(false);
    const showUserMenu = ref(false);
    const searchQuery = ref("");
    ref(null);
    const languages = computed(() => locales.value);
    const closeUserMenu = () => {
      showUserMenu.value = false;
    };
    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);
    const getNotificationIcon = (iconName) => {
      const iconComponents = {
        ExclamationCircleIcon,
        UserPlusIcon,
        DocumentTextIcon,
        ShieldExclamationIcon,
        InformationCircleIcon,
        WrenchScrewdriverIcon
      };
      return iconComponents[iconName] || InformationCircleIcon;
    };
    return (_ctx, _push, _parent, _attrs) => {
      var _a2, _b, _c, _d, _e, _f, _g, _h;
      const _component_NuxtLink = __nuxt_component_0$2;
      _push(`<header${ssrRenderAttrs(mergeProps({ class: "h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 flex items-center justify-between navbar-container" }, _attrs))}><div class="flex items-center space-x-4"><button class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">`);
      _push(ssrRenderComponent(unref(Bars3Icon), { class: "w-6 h-6" }, null, _parent));
      _push(`</button><div class="hidden lg:flex items-center space-x-4"><h1 class="text-xl font-bold text-gray-900 dark:text-white">${ssrInterpolate(unref(currentPageTitle))}</h1><div class="h-6 w-px bg-gray-300 dark:bg-gray-600"></div><nav class="flex" aria-label="Breadcrumb"><ol class="flex items-center space-x-2 text-sm"><li>`);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/",
        class: "text-gray-500 dark:text-gray-400 hover:text-primary-500 transition-colors duration-200"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(unref(t)("common.home"))}`);
          } else {
            return [
              createTextVNode(toDisplayString(unref(t)("common.home")), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</li><!--[-->`);
      ssrRenderList(unref(breadcrumbItems), (item, index) => {
        _push(`<li class="flex items-center">`);
        _push(ssrRenderComponent(unref(ChevronRightIcon), { class: "w-4 h-4 mx-2 text-gray-400" }, null, _parent));
        if (item.href && index < unref(breadcrumbItems).length - 1) {
          _push(ssrRenderComponent(_component_NuxtLink, {
            to: item.href,
            class: "text-gray-500 dark:text-gray-400 hover:text-primary-500 transition-colors duration-200"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`${ssrInterpolate(item.name)}`);
              } else {
                return [
                  createTextVNode(toDisplayString(item.name), 1)
                ];
              }
            }),
            _: 2
          }, _parent));
        } else {
          _push(`<span class="text-gray-700 dark:text-gray-300 font-medium">${ssrInterpolate(item.name)}</span>`);
        }
        _push(`</li>`);
      });
      _push(`<!--]--></ol></nav></div></div><div class="flex items-center space-x-3">`);
      if (unref(enableSearch)) {
        _push(`<button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 relative">`);
        _push(ssrRenderComponent(unref(MagnifyingGlassIcon), { class: "w-5 h-5" }, null, _parent));
        _push(`</button>`);
      } else {
        _push(`<!---->`);
      }
      if (unref(enableMultilingual)) {
        _push(`<div class="relative"><button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">`);
        _push(ssrRenderComponent(unref(GlobeAltIcon), { class: "w-5 h-5" }, null, _parent));
        _push(`</button>`);
        if (unref(showLanguage)) {
          _push(`<div class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"><!--[-->`);
          ssrRenderList(unref(languages), (lang) => {
            _push(`<button class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"><span class="text-lg mr-2">${ssrInterpolate(lang.flag)}</span> ${ssrInterpolate(lang.name)}</button>`);
          });
          _push(`<!--]--></div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      if (unref(enableDarkMode)) {
        _push(`<button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">`);
        if (unref(isDark)) {
          _push(ssrRenderComponent(unref(SunIcon), { class: "w-5 h-5" }, null, _parent));
        } else {
          _push(ssrRenderComponent(unref(MoonIcon), { class: "w-5 h-5" }, null, _parent));
        }
        _push(`</button>`);
      } else {
        _push(`<!---->`);
      }
      if (unref(enableNotifications)) {
        _push(`<div class="relative"><button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 relative">`);
        _push(ssrRenderComponent(unref(BellIcon), { class: "w-5 h-5" }, null, _parent));
        if (unref(unreadCount) > 0) {
          _push(`<span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 rounded-full flex items-center justify-center text-xs text-white font-bold">${ssrInterpolate(unref(unreadCount) > 99 ? "99+" : unref(unreadCount))}</span>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</button>`);
        if (unref(showNotifications)) {
          _push(`<div class="absolute right-0 top-full mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"><div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between"><h3 class="font-semibold text-gray-900 dark:text-white">${ssrInterpolate(unref(t)("notifications.title"))}</h3><div class="flex items-center space-x-2"><button class="text-xs text-primary-500 hover:text-primary-600 transition-colors duration-200">${ssrInterpolate(unref(t)("notifications.mark_all_read"))}</button></div></div><div class="max-h-80 overflow-y-auto"><!--[-->`);
          ssrRenderList(unref(recentNotifications), (notification) => {
            _push(`<div class="${ssrRenderClass([{ "bg-blue-50 dark:bg-blue-900/20": !notification.read }, "p-4 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 cursor-pointer"])}"><div class="flex items-start space-x-3"><div class="${ssrRenderClass([{
              "bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400": notification.priority === "high",
              "bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400": notification.priority === "medium",
              "bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400": notification.priority === "low"
            }, "flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"])}">`);
            ssrRenderVNode(_push, createVNode(resolveDynamicComponent(getNotificationIcon(notification.icon)), { class: "w-4 h-4" }, null), _parent);
            _push(`</div><div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900 dark:text-white">${ssrInterpolate(typeof notification.title === "string" && notification.title.includes(".") ? unref(t)(notification.title) : notification.title)}</p><p class="text-sm text-gray-600 dark:text-gray-300 mt-1">${ssrInterpolate(notification.message)}</p><p class="text-xs text-gray-500 dark:text-gray-400 mt-1">${ssrInterpolate(unref(notificationsStore).getTimeAgo(notification.time))}</p></div><div class="flex-shrink-0">`);
            if (!notification.read) {
              _push(`<div class="w-2 h-2 bg-primary-500 rounded-full"></div>`);
            } else {
              _push(`<!---->`);
            }
            _push(`</div></div></div>`);
          });
          _push(`<!--]-->`);
          if (unref(recentNotifications).length === 0) {
            _push(`<div class="p-8 text-center text-gray-500 dark:text-gray-400">${ssrInterpolate(unref(t)("notifications.no_notifications"))}</div>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</div><div class="p-3 border-t border-gray-200 dark:border-gray-700"><button class="w-full text-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200">${ssrInterpolate(unref(t)("notifications.clear_all"))}</button></div></div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="relative"><button class="flex items-center space-x-2 p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"><img${ssrRenderAttr("src", ((_a2 = unref(user)) == null ? void 0 : _a2.avatar) || `https://ui-avatars.com/api/?name=${encodeURIComponent(((_b = unref(user)) == null ? void 0 : _b.name) || ((_c = unref(user)) == null ? void 0 : _c.username) || "User")}&background=6366f1&color=fff`)}${ssrRenderAttr("alt", ((_d = unref(user)) == null ? void 0 : _d.name) || ((_e = unref(user)) == null ? void 0 : _e.username) || "User Avatar")} class="w-8 h-8 rounded-full object-cover">`);
      _push(ssrRenderComponent(unref(ChevronDownIcon), { class: "w-4 h-4 hidden sm:block" }, null, _parent));
      _push(`</button>`);
      if (unref(showUserMenu)) {
        _push(`<div class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"><div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700"><p class="text-sm font-medium text-gray-900 dark:text-white">${ssrInterpolate(((_f = unref(user)) == null ? void 0 : _f.name) || ((_g = unref(user)) == null ? void 0 : _g.username) || "User")}</p><p class="text-sm text-gray-600 dark:text-gray-400">${ssrInterpolate((_h = unref(user)) == null ? void 0 : _h.email)}</p></div>`);
        _push(ssrRenderComponent(_component_NuxtLink, {
          to: "/profile",
          class: "block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200",
          onClick: closeUserMenu
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`${ssrInterpolate(unref(t)("common.profile"))}`);
            } else {
              return [
                createTextVNode(toDisplayString(unref(t)("common.profile")), 1)
              ];
            }
          }),
          _: 1
        }, _parent));
        _push(ssrRenderComponent(_component_NuxtLink, {
          to: "/settings",
          class: "block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200",
          onClick: closeUserMenu
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`${ssrInterpolate(unref(t)("common.settings"))}`);
            } else {
              return [
                createTextVNode(toDisplayString(unref(t)("common.settings")), 1)
              ];
            }
          }),
          _: 1
        }, _parent));
        _push(`<hr class="my-1 border-gray-200 dark:border-gray-700"><button class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">${ssrInterpolate(unref(t)("common.logout"))}</button></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div>`);
      if (unref(showSearch)) {
        _push(`<div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-start justify-center pt-20"><div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg mx-4"><div class="p-4"><input${ssrRenderAttr("value", unref(searchQuery))} type="text"${ssrRenderAttr("placeholder", unref(t)("common.search") + "...")} class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></div></div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</header>`);
    };
  }
};
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("components/AppNavbar.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = {
  __name: "AppFootbar",
  __ssrInlineRender: true,
  emits: ["privacy", "terms"],
  setup(__props) {
    const { t } = useI18n();
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<footer${ssrRenderAttrs(mergeProps({ class: "bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-3" }, _attrs))}><div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400"><div class="flex items-center space-x-4"><span>${ssrInterpolate(unref(t)("common.version"))} 1.0.0</span></div><div class="flex items-center"><span>${ssrInterpolate(unref(t)("footer.copyright"))}</span></div><div class="flex items-center space-x-4"><a href="#" class="hover:text-primary-500 transition-colors duration-200">${ssrInterpolate(unref(t)("footer.privacy_policy"))}</a><span class="w-px h-4 bg-gray-300 dark:bg-gray-600"></span><a href="#" class="hover:text-primary-500 transition-colors duration-200">${ssrInterpolate(unref(t)("footer.terms_of_service"))}</a></div></div></footer>`);
    };
  }
};
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("components/AppFootbar.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = {
  __name: "error",
  __ssrInlineRender: true,
  setup(__props) {
    const { t } = useI18n();
    const sidebarStore = useSidebarStore();
    const { sidebarCollapsed } = storeToRefs(sidebarStore);
    const websiteSettingsStore = useWebsiteSettingsStore();
    const { showFooter } = storeToRefs(websiteSettingsStore);
    const showFootbar = computed(() => showFooter.value);
    const searchQuery = ref("");
    const error = useError();
    const errorType = computed(() => {
      var _a2, _b, _c;
      if (((_a2 = error.value) == null ? void 0 : _a2.statusCode) === 500) return "server_error";
      if (((_b = error.value) == null ? void 0 : _b.statusCode) === 403) return "access_denied";
      if (((_c = error.value) == null ? void 0 : _c.statusCode) === 401) return "unauthorized";
      return "not_found";
    });
    const errorCode = computed(() => {
      var _a2;
      return ((_a2 = error.value) == null ? void 0 : _a2.statusCode) || 404;
    });
    const errorTitle = computed(() => {
      switch (errorType.value) {
        case "server_error":
          return "500 - Server Error";
        case "access_denied":
          return "403 - Access Denied";
        case "unauthorized":
          return "401 - Unauthorized";
        default:
          return `${errorCode.value} - ${t("error.page_not_found")}`;
      }
    });
    useHead({
      title: `${errorTitle.value} | Project Management`,
      meta: [
        { name: "description", content: t("error.page_description") }
      ]
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_AppSidebar = __nuxt_component_0;
      const _component_AppNavbar = _sfc_main$3;
      const _component_NuxtLink = __nuxt_component_0$2;
      const _component_AppFootbar = _sfc_main$2;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-gray-50 dark:bg-gray-900 flex" }, _attrs))} data-v-d7ab52d6>`);
      _push(ssrRenderComponent(_component_AppSidebar, null, null, _parent));
      _push(`<div class="${ssrRenderClass([{
        "sidebar-collapsed": unref(sidebarCollapsed)
      }, "min-h-screen flex flex-col flex-1 main-content-area"])}" data-v-d7ab52d6>`);
      _push(ssrRenderComponent(_component_AppNavbar, null, null, _parent));
      _push(`<main class="flex-1 flex items-center justify-center p-6" data-v-d7ab52d6><div class="text-center max-w-2xl mx-auto" data-v-d7ab52d6><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-12" data-v-d7ab52d6><div class="mb-8" data-v-d7ab52d6><h1 class="text-9xl font-bold text-primary-500 leading-none select-none" data-v-d7ab52d6>${ssrInterpolate(unref(errorCode))}</h1></div><div class="mb-8" data-v-d7ab52d6><div class="relative inline-flex items-center justify-center" data-v-d7ab52d6>`);
      _push(ssrRenderComponent(unref(FolderIcon), { class: "w-24 h-24 text-gray-300 dark:text-gray-600" }, null, _parent));
      _push(`<div class="absolute -top-2 -right-2 bg-primary-500 rounded-full w-10 h-10 flex items-center justify-center" data-v-d7ab52d6>`);
      _push(ssrRenderComponent(unref(QuestionMarkCircleIcon), { class: "w-6 h-6 text-white" }, null, _parent));
      _push(`</div></div></div><div class="mb-8 space-y-2" data-v-d7ab52d6><h2 class="text-2xl font-bold text-gray-900 dark:text-white" data-v-d7ab52d6>${ssrInterpolate(unref(t)("error.page_not_found"))}</h2><p class="text-lg text-gray-600 dark:text-gray-300" data-v-d7ab52d6>${ssrInterpolate(unref(t)("error.page_not_found_en"))}</p><p class="text-gray-500 dark:text-gray-400" data-v-d7ab52d6>${ssrInterpolate(unref(t)("error.page_description"))}</p></div><div class="mb-8" data-v-d7ab52d6><div class="relative max-w-md mx-auto" data-v-d7ab52d6><input${ssrRenderAttr("value", unref(searchQuery))} type="text"${ssrRenderAttr("placeholder", unref(t)("error.search_placeholder"))} class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200" data-v-d7ab52d6><div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none" data-v-d7ab52d6>`);
      _push(ssrRenderComponent(unref(MagnifyingGlassIcon), { class: "h-5 w-5 text-gray-400" }, null, _parent));
      _push(`</div><button class="absolute inset-y-0 right-0 pr-3 flex items-center" data-v-d7ab52d6>`);
      _push(ssrRenderComponent(unref(ArrowRightIcon), { class: "h-5 w-5 text-primary-500 hover:text-primary-600 transition-colors duration-200" }, null, _parent));
      _push(`</button></div></div><div class="space-y-6" data-v-d7ab52d6><div data-v-d7ab52d6>`);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/",
        class: "inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transform hover:scale-[1.02] transition-all duration-200"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(HomeIcon), { class: "w-5 h-5 mr-2" }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(unref(t)("error.back_to_dashboard"))}`);
          } else {
            return [
              createVNode(unref(HomeIcon), { class: "w-5 h-5 mr-2" }),
              createTextVNode(" " + toDisplayString(unref(t)("error.back_to_dashboard")), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" data-v-d7ab52d6>`);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/projects?search=true",
        class: "inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(MagnifyingGlassIcon), { class: "w-4 h-4 mr-2" }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(unref(t)("error.search_projects"))}`);
          } else {
            return [
              createVNode(unref(MagnifyingGlassIcon), { class: "w-4 h-4 mr-2" }),
              createTextVNode(" " + toDisplayString(unref(t)("error.search_projects")), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/projects",
        class: "inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(FolderIcon), { class: "w-4 h-4 mr-2" }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(unref(t)("error.view_all_projects"))}`);
          } else {
            return [
              createVNode(unref(FolderIcon), { class: "w-4 h-4 mr-2" }),
              createTextVNode(" " + toDisplayString(unref(t)("error.view_all_projects")), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/clients",
        class: "inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(UsersIcon), { class: "w-4 h-4 mr-2" }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(unref(t)("error.client_management"))}`);
          } else {
            return [
              createVNode(unref(UsersIcon), { class: "w-4 h-4 mr-2" }),
              createTextVNode(" " + toDisplayString(unref(t)("error.client_management")), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/help",
        class: "inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(QuestionMarkCircleIcon), { class: "w-4 h-4 mr-2" }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(unref(t)("error.contact_support"))}`);
          } else {
            return [
              createVNode(unref(QuestionMarkCircleIcon), { class: "w-4 h-4 mr-2" }),
              createTextVNode(" " + toDisplayString(unref(t)("error.contact_support")), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="pt-6 border-t border-gray-200 dark:border-gray-700" data-v-d7ab52d6><p class="text-sm text-gray-500 dark:text-gray-400" data-v-d7ab52d6>${ssrInterpolate(unref(t)("error.helpful_tips"))}</p><div class="mt-3 space-y-2" data-v-d7ab52d6><div class="flex items-center text-xs text-gray-400 dark:text-gray-500" data-v-d7ab52d6>`);
      _push(ssrRenderComponent(unref(CheckIcon), { class: "w-4 h-4 mr-2 text-primary-500" }, null, _parent));
      _push(` ${ssrInterpolate(unref(t)("error.tip_1"))}</div><div class="flex items-center text-xs text-gray-400 dark:text-gray-500" data-v-d7ab52d6>`);
      _push(ssrRenderComponent(unref(CheckIcon), { class: "w-4 h-4 mr-2 text-primary-500" }, null, _parent));
      _push(` ${ssrInterpolate(unref(t)("error.tip_2"))}</div><div class="flex items-center text-xs text-gray-400 dark:text-gray-500" data-v-d7ab52d6>`);
      _push(ssrRenderComponent(unref(CheckIcon), { class: "w-4 h-4 mr-2 text-primary-500" }, null, _parent));
      _push(` ${ssrInterpolate(unref(t)("error.tip_3"))}</div></div></div></div></div></div></main>`);
      if (unref(showFootbar)) {
        _push(ssrRenderComponent(_component_AppFootbar, null, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div>`);
    };
  }
};
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("error.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const ErrorComponent = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["__scopeId", "data-v-d7ab52d6"]]);
const _sfc_main = {
  __name: "nuxt-root",
  __ssrInlineRender: true,
  setup(__props) {
    const IslandRenderer = () => null;
    const nuxtApp = useNuxtApp();
    nuxtApp.deferHydration();
    nuxtApp.ssrContext.url;
    const SingleRenderer = false;
    provide(PageRouteSymbol, useRoute());
    nuxtApp.hooks.callHookWith((hooks) => hooks.map((hook) => hook()), "vue:setup");
    const error = useError();
    const abortRender = error.value && !nuxtApp.ssrContext.error;
    onErrorCaptured((err, target, info) => {
      nuxtApp.hooks.callHook("vue:error", err, target, info).catch((hookError) => console.error("[nuxt] Error in `vue:error` hook", hookError));
      {
        const p = nuxtApp.runWithContext(() => showError(err));
        onServerPrefetch(() => p);
        return false;
      }
    });
    const islandContext = nuxtApp.ssrContext.islandContext;
    return (_ctx, _push, _parent, _attrs) => {
      ssrRenderSuspense(_push, {
        default: () => {
          if (unref(abortRender)) {
            _push(`<div></div>`);
          } else if (unref(error)) {
            _push(ssrRenderComponent(unref(ErrorComponent), { error: unref(error) }, null, _parent));
          } else if (unref(islandContext)) {
            _push(ssrRenderComponent(unref(IslandRenderer), { context: unref(islandContext) }, null, _parent));
          } else if (unref(SingleRenderer)) {
            ssrRenderVNode(_push, createVNode(resolveDynamicComponent(unref(SingleRenderer)), null, null), _parent);
          } else {
            _push(ssrRenderComponent(unref(_sfc_main$6), null, null, _parent));
          }
        },
        _: 1
      });
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/nuxt/dist/app/components/nuxt-root.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
let entry;
{
  entry = async function createNuxtAppServer(ssrContext) {
    var _a2;
    const vueApp = createApp(_sfc_main);
    const nuxt = createNuxtApp({ vueApp, ssrContext });
    try {
      await applyPlugins(nuxt, plugins);
      await nuxt.hooks.callHook("app:created", vueApp);
    } catch (error) {
      await nuxt.hooks.callHook("app:error", error);
      (_a2 = nuxt.payload).error || (_a2.error = createError(error));
    }
    if (ssrContext == null ? void 0 : ssrContext._renderResponse) {
      throw new Error("skipping render");
    }
    return vueApp;
  };
}
const entry$1 = (ssrContext) => entry(ssrContext);

export { LayoutMetaSymbol as L, PageRouteSymbol as P, _export_sfc as _, useRoute as a, appLayoutTransition as b, _wrapInTransition as c, useAppConfig as d, entry$1 as default, appConfig as e, __nuxt_component_0$2 as f, get as g, defineNuxtRouteMiddleware as h, useAuthStore as i, asyncDataDefaults as j, createError as k, looseToNumber as l, mergeConfig as m, navigateTo as n, omit as o, useRuntimeConfig as p, useHead as q, twMerge as t, useNuxtApp as u };
//# sourceMappingURL=server.mjs.map
