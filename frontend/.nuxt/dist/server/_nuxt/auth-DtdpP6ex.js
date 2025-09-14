import { ref, computed, readonly } from "vue";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/hookable/dist/index.mjs";
import { k as useRuntimeConfig, w as defineStore, n as navigateTo } from "../server.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/klona/dist/index.mjs";
import "D:/Bonus/0814_web_45000/urban_renewal/frontend/node_modules/defu/dist/defu.mjs";
import "#internal/nuxt/paths";
const useApi = () => {
  const config = useRuntimeConfig();
  const getBaseURL = () => {
    const isDev = process.env.NODE_ENV === "development";
    if (isDev) {
      console.log("[API] Using development proxy: /api");
      return "/api";
    }
    const apiBaseUrl = config.public.apiBaseUrl || config.public.backendUrl;
    if (apiBaseUrl && apiBaseUrl !== "/api") {
      console.log("[API] Using production API URL:", apiBaseUrl);
      return apiBaseUrl;
    }
    const productionUrl = "https://project.mercylife.cc/api";
    console.log("[API] Using fallback production URL:", productionUrl);
    return productionUrl;
  };
  const baseURL = getBaseURL();
  console.log("[API] Base URL resolved to:", baseURL);
  const getAuthToken = () => {
    return null;
  };
  const getAuthHeaders = () => {
    const token = getAuthToken();
    if (token) {
      return {
        "Authorization": `Bearer ${token}`
      };
    }
    return {};
  };
  const apiRequest = async (endpoint, options = {}) => {
    var _a, _b, _c, _d, _e, _f;
    const authHeaders = getAuthHeaders();
    const defaultOptions = {
      baseURL,
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        ...authHeaders,
        ...options.headers
      },
      ...options
    };
    try {
      console.log(`[API] ${defaultOptions.method || "GET"} ${baseURL}${endpoint}`);
      const response = await $fetch(endpoint, defaultOptions);
      console.log(`[API] Success: ${baseURL}${endpoint}`);
      return {
        success: true,
        data: response,
        error: null
      };
    } catch (error) {
      console.error(`[API] Error ${defaultOptions.method || "GET"} ${baseURL}${endpoint}:`, error);
      console.error("[API] Full URL:", `${baseURL}${endpoint}`);
      console.error("[API] Options:", defaultOptions);
      if (error.status === 401 || error.statusCode === 401) {
        console.warn("[API] Authentication error - clearing auth state");
      }
      const errorDetails = {
        message: ((_a = error.data) == null ? void 0 : _a.message) || error.message || "請求失敗",
        status: error.status || error.statusCode || 500,
        statusText: error.statusText || error.statusMessage || "Internal Server Error",
        url: `${baseURL}${endpoint}`,
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
  const get = async (endpoint, params = {}) => {
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
  const del = async (endpoint) => {
    return await apiRequest(endpoint, {
      method: "DELETE"
    });
  };
  const setAuthToken = (token) => {
  };
  const clearAuthToken = () => {
  };
  return {
    get,
    post,
    put,
    patch,
    delete: del,
    apiRequest,
    getAuthToken,
    getAuthHeaders,
    setAuthToken,
    clearAuthToken
  };
};
const useAuthStore = defineStore("auth", () => {
  const user = ref(null);
  const token = ref(null);
  const isLoggedIn = computed(() => !!user.value && !!token.value);
  const isAdmin = computed(() => {
    var _a;
    return ((_a = user.value) == null ? void 0 : _a.role) === "admin";
  });
  const isLoading = ref(false);
  const TOKEN_KEY = "auth_token";
  const USER_KEY = "auth_user";
  const login = async (credentials) => {
    var _a;
    try {
      isLoading.value = true;
      const loginData = {
        login: credentials.username,
        password: credentials.password
      };
      const { post } = useApi();
      const response = await post("/auth/login", loginData);
      if (!response.success) {
        throw new Error(((_a = response.error) == null ? void 0 : _a.message) || "登入失敗");
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
      const { get } = useApi();
      const response = await get("/auth/me");
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
    var _a;
    try {
      const { put } = useApi();
      const response = await put("/profile", profileData);
      if (!response.success) {
        throw new Error(((_a = response.error) == null ? void 0 : _a.message) || "更新失敗");
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
    var _a;
    try {
      const { put } = useApi();
      const response = await put("/auth/change-password", passwordData);
      if (!response.success) {
        throw new Error(((_a = response.error) == null ? void 0 : _a.message) || "密碼更改失敗");
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
export {
  useAuthStore as u
};
//# sourceMappingURL=auth-DtdpP6ex.js.map
