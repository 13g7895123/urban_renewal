import { inject, ref, computed } from "vue";
import { useDebounceFn } from "@vueuse/core";
const useFormGroup = (inputProps, config, bind = true) => {
  const formBus = inject("form-events", void 0);
  const formGroup = inject("form-group", void 0);
  const formInputs = inject("form-inputs", void 0);
  if (formGroup) {
    if (!bind || (inputProps == null ? void 0 : inputProps.legend)) {
      formGroup.inputId.value = void 0;
    } else if (inputProps == null ? void 0 : inputProps.id) {
      formGroup.inputId.value = inputProps == null ? void 0 : inputProps.id;
    }
    if (formInputs) {
      formInputs.value[formGroup.name.value] = formGroup.inputId.value;
    }
  }
  const blurred = ref(false);
  function emitFormEvent(type, path) {
    if (formBus) {
      formBus.emit({ type, path });
    }
  }
  function emitFormBlur() {
    emitFormEvent("blur", formGroup == null ? void 0 : formGroup.name.value);
    blurred.value = true;
  }
  function emitFormChange() {
    emitFormEvent("change", formGroup == null ? void 0 : formGroup.name.value);
  }
  const emitFormInput = useDebounceFn(() => {
    if (blurred.value || (formGroup == null ? void 0 : formGroup.eagerValidation.value)) {
      emitFormEvent("input", formGroup == null ? void 0 : formGroup.name.value);
    }
  }, 300);
  return {
    inputId: computed(() => (inputProps == null ? void 0 : inputProps.id) ?? (formGroup == null ? void 0 : formGroup.inputId.value)),
    name: computed(() => (inputProps == null ? void 0 : inputProps.name) ?? (formGroup == null ? void 0 : formGroup.name.value)),
    size: computed(() => {
      var _a;
      const formGroupSize = config.size[formGroup == null ? void 0 : formGroup.size.value] ? formGroup == null ? void 0 : formGroup.size.value : null;
      return (inputProps == null ? void 0 : inputProps.size) ?? formGroupSize ?? ((_a = config.default) == null ? void 0 : _a.size);
    }),
    color: computed(() => {
      var _a;
      return ((_a = formGroup == null ? void 0 : formGroup.error) == null ? void 0 : _a.value) ? "red" : inputProps == null ? void 0 : inputProps.color;
    }),
    emitFormBlur,
    emitFormInput,
    emitFormChange
  };
};
export {
  useFormGroup as u
};
//# sourceMappingURL=useFormGroup-B3564yef.js.map
