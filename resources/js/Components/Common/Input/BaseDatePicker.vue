<script setup>
import { defineProps, defineEmits, ref, watch, onMounted } from "vue";
import DatePicker from "vue-datepicker-next";
import "vue-datepicker-next/index.css";

const props = defineProps({
  modelValue: [String, Date],
  type: String, // "time" | "date"
  showTime: Boolean,
  minuteStep: { type: Number, default: 30 },
  autoSelectNow: { type: Boolean, default: true },
  label: String,
  error: String,
  placeholder: String,
  required: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const internalDate = ref(null);
const internalTime = ref(null);

/* ---------------- HELPERS ---------------- */
const pad = (n) => String(n).padStart(2, "0");

const parseTimeValue = (value) => {
  if (!value) return null;
  if (value instanceof Date && !Number.isNaN(value.getTime())) return new Date(value);
  if (typeof value !== "string") return null;

  const raw = value.trim();
  const date = new Date();
  date.setMilliseconds(0);

  // 12-hour format: h:mm A or hh:mm:ss A
  const twelveHour = raw.match(
    /^(\d{1,2}):([0-5]\d)(?::([0-5]\d))?\s*([AaPp][Mm])$/
  );
  if (twelveHour) {
    let hours = Number(twelveHour[1]) % 12;
    const minutes = Number(twelveHour[2]);
    const seconds = Number(twelveHour[3] || 0);
    const isPM = twelveHour[4].toUpperCase() === "PM";
    if (isPM) hours += 12;
    date.setHours(hours, minutes, seconds, 0);
    return date;
  }

  // 24-hour format: HH:mm or HH:mm:ss
  const twentyFourHour = raw.match(
    /^([01]?\d|2[0-3]):([0-5]\d)(?::([0-5]\d))?$/
  );
  if (twentyFourHour) {
    const hours = Number(twentyFourHour[1]);
    const minutes = Number(twentyFourHour[2]);
    const seconds = Number(twentyFourHour[3] || 0);
    date.setHours(hours, minutes, seconds, 0);
    return date;
  }

  const parsed = new Date(raw);
  return Number.isNaN(parsed.getTime()) ? null : parsed;
};

const formatTime12 = (d) => {
  let h = d.getHours();
  const m = pad(d.getMinutes());
  const ampm = h >= 12 ? "PM" : "AM";
  h = h % 12 || 12;
  return `${pad(h)}:${m} ${ampm}`;
};

/* ---------------- AUTO FILL TIME ---------------- */
onMounted(() => {
  if (
    props.type === "time" &&
    props.autoSelectNow &&
    !props.modelValue
  ) {
    const now = new Date();

    // round minutes
    const step = props.minuteStep;
    const mins = Math.round(now.getMinutes() / step) * step;
    now.setMinutes(mins >= 60 ? 0 : mins, 0, 0);
    if (mins >= 60) now.setHours(now.getHours() + 1);

    internalTime.value = now;
    emit("update:modelValue", formatTime12(now));
  }
});

/* ---------------- SYNC FROM PARENT ---------------- */
watch(
  () => props.modelValue,
  (val) => {
    if (!val) {
      internalDate.value = null;
      internalTime.value = null;
      return;
    }

    if (props.type === "time") {
      internalTime.value = parseTimeValue(val);
    } else {
      const parsedDate = val instanceof Date ? new Date(val) : new Date(String(val));
      internalDate.value = Number.isNaN(parsedDate.getTime()) ? null : parsedDate;
    }
  },
  { immediate: true }
);

/* ---------------- TIME CHANGE ---------------- */
watch(internalTime, (val) => {
  if (props.type !== "time") return;
  if (!val) {
    if (props.modelValue) emit("update:modelValue", null);
    return;
  }

  const formatted = formatTime12(val);
  if (formatted !== props.modelValue) {
    emit("update:modelValue", formatted);
  }
});

/* ---------------- DATE CHANGE ---------------- */
watch(internalDate, (val) => {
  if (props.type === "time") return;
  if (!val) {
    if (props.modelValue) emit("update:modelValue", null);
    return;
  }

  const y = val.getFullYear();
  const m = pad(val.getMonth() + 1);
  const d = pad(val.getDate());

  let formatted = `${y}-${m}-${d}`;
  if (props.showTime) {
    formatted += ` ${pad(val.getHours())}:${pad(val.getMinutes())}:${pad(val.getSeconds())}`;
  }

  if (formatted !== props.modelValue) {
    emit("update:modelValue", formatted);
  }
});
</script>


<template>
  <div class="mb-2">
    <label v-if="label" class="form-label">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>

    <DatePicker
    v-if="type === 'time'"
    v-model:value="internalTime"
    type="time"
    format="hh:mm A"
    value-type="date"
    :minute-step="minuteStep"
    :use12h="true"
    :show-second="false"
    :required="required"
    :placeholder="placeholder"
    class="w-100"
  />

    <DatePicker
      v-else
      v-model:value="internalDate"
      :format="showTime ? 'MM-DD-YYYY HH:mm:ss' : 'MM-DD-YYYY'"
      :show-time-panel="showTime"
      :use12h="showTime"
      :required="required"
      class="w-100"
      :placeholder="placeholder"
    />

    <div v-if="error" class="text-danger mt-1">{{ error }}</div>
  </div>
</template>

<style>
.mx-datepicker-popup {
  z-index: 9999 !important;
}
.mx-input{
    height: 40px;
    line-height: 45px;
    background: transparent;
    border: 1px solid var(--iq-dark-border);
    font-size: 14px;
    color: var(--iq-body-text);
    border-radius: 8px;
}

</style>
