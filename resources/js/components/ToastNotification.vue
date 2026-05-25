<template>
  <teleport to="body">
    <transition-group
      name="toast"
      tag="div"
      class="toast-container"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast"
        :class="[`toast-${toast.type}`]"
        @click="removeToast(toast.id)"
      >
        <div class="toast-icon">
          <span v-if="toast.type === 'success'">✓</span>
          <span v-else-if="toast.type === 'error'">✕</span>
          <span v-else-if="toast.type === 'warning'">⚠</span>
          <span v-else-if="toast.type === 'info'">ℹ</span>
          <span v-else>📍</span>
        </div>
        <div class="toast-content">
          <div class="toast-title">{{ toast.title }}</div>
          <div v-if="toast.message" class="toast-message">{{ toast.message }}</div>
        </div>
        <button class="toast-close" @click.stop="removeToast(toast.id)">×</button>
      </div>
    </transition-group>
  </teleport>
</template>

<script setup>
import { ref } from 'vue'

const toasts = ref([])
let nextId = 1

function addToast({ title, message, type = 'info', duration = 3000 }) {
  const id = nextId++
  toasts.value.push({ id, title, message, type })

  if (duration > 0) {
    setTimeout(() => {
      removeToast(id)
    }, duration)
  }

  return id
}

function removeToast(id) {
  const index = toasts.value.findIndex(t => t.id === id)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
}

defineExpose({
  addToast,
  removeToast
})
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 400px;
}

.toast {
  display: flex;
  align-items: start;
  gap: 12px;
  padding: 16px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border-left: 4px solid;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 300px;
}

.toast:hover {
  transform: translateX(-4px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.toast-icon {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 14px;
  color: white;
}

.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-title {
  font-weight: 600;
  font-size: 14px;
  color: #1f2937;
  margin-bottom: 4px;
}

.toast-message {
  font-size: 13px;
  color: #6b7280;
  line-height: 1.4;
}

.toast-close {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: none;
  background: rgba(0, 0, 0, 0.1);
  color: #6b7280;
  font-size: 18px;
  line-height: 1;
  cursor: pointer;
  transition: all 0.2s;
}

.toast-close:hover {
  background: rgba(0, 0, 0, 0.2);
  color: #1f2937;
}

.toast-success {
  border-left-color: #10b981;
}

.toast-success .toast-icon {
  background: #10b981;
}

.toast-error {
  border-left-color: #ef4444;
}

.toast-error .toast-icon {
  background: #ef4444;
}

.toast-warning {
  border-left-color: #f59e0b;
}

.toast-warning .toast-icon {
  background: #f59e0b;
}

.toast-info {
  border-left-color: #3b82f6;
}

.toast-info .toast-icon {
  background: #3b82f6;
}

.toast-location {
  border-left-color: #8b5cf6;
}

.toast-location .toast-icon {
  background: #8b5cf6;
}

/* Transitions */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.8);
}
</style>
