<template>
  <div class="app-shell flex min-h-screen items-center justify-center p-4">
    <div class="surface-card w-full max-w-2xl overflow-hidden animate-pulse-slow">
      <!-- Header -->
      <div class="bg-gradient-to-r from-brand-700 via-brand-600 to-cyan-500 px-6 py-8 text-white">
        <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-blue-100/90">Live Trigger</p>
        <h1 class="mt-2 text-center text-4xl font-bold">Reminder Alert</h1>
      </div>

      <!-- Reminder details -->
      <div class="p-8 space-y-6">
        <div class="text-center space-y-4">
          <h2 class="text-3xl font-bold text-slate-900">{{ reminder?.title || 'Location Reminder' }}</h2>
          <p class="text-lg text-slate-600">{{ reminder?.description || 'You have reached your destination!' }}</p>
        </div>

        <!-- Location info -->
        <div class="surface-card-soft p-6 space-y-3">
          <div class="flex items-center gap-3">
            <img src="/icon/marker.png" alt="Location marker" class="h-8 w-8 object-contain" />
            <div>
              <p class="text-lg font-semibold text-slate-800">{{ location?.name }}</p>
              <p class="text-slate-600">{{ location?.address }}</p>
            </div>
          </div>
          
          <div class="flex items-center gap-3 text-slate-700">
            <img src="/icon/bullseye-arrow.png" alt="Trigger icon" class="h-7 w-7 object-contain" />
            <p class="text-lg">
              <span class="font-semibold">Trigger:</span> 
              {{ reminder?.trigger_type === 'entry' ? 'Entering' : 'Leaving' }} the area
            </p>
          </div>
        </div>

        <!-- Alarm status -->
        <div class="surface-card-soft border-amber-300 bg-amber-50 p-6 text-center">
          <div class="mb-3 flex justify-center">
            <img :src="alarmLogoUrl" alt="Alarm clock" class="h-16 w-16 object-contain" />
          </div>
          <p class="text-xl font-semibold text-amber-800">Alarm is ringing...</p>
          <p class="mt-2 text-slate-600">Click the button below to stop</p>
        </div>

        <!-- Stop button -->
        <button
          @click="stopAlarm"
          class="btn-brand w-full rounded-2xl py-5 text-xl font-bold shadow-lg transition-all duration-200 hover:scale-[1.01] active:scale-[0.99]"
        >
          STOP ALARM
        </button>

        <!-- Time display -->
        <div class="text-center text-slate-500">
          <p class="text-sm">Triggered at {{ formatTime(triggeredAt) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const reminder = ref(null);
const location = ref(null);
const triggeredAt = ref(new Date());
const DEFAULT_ALARM_LOGO = '/icon/alarm-clock.png';
const DEFAULT_ALARM_SOUND = '/icon/alarm.mp3';
const alarmLogoUrl = ref(DEFAULT_ALARM_LOGO);

// Audio context for continuous alarm
let audioContext = null;
let oscillator = null;
let gainNode = null;
let isPlaying = false;
let alarmAudio = null;

onMounted(() => {
  // Get data from URL parameters
  try {
    if (route.query.reminder) {
      reminder.value = JSON.parse(decodeURIComponent(route.query.reminder));
    }
    if (route.query.location) {
      location.value = JSON.parse(decodeURIComponent(route.query.location));
    }
    if (route.query.triggeredAt) {
      triggeredAt.value = new Date(route.query.triggeredAt);
    }
  } catch (error) {
    console.error('Error parsing alarm data:', error);
  }

  // Start playing alarm sound immediately
  startAlarmSound();

  // Change tab title to grab attention
  document.title = 'Reminder Alert';
  
  // Flash the title for attention
  let flashCount = 0;
  const flashInterval = setInterval(() => {
    document.title = flashCount % 2 === 0 ? 'Reminder Alert' : 'Check Reminder';
    flashCount++;
    if (flashCount > 20) clearInterval(flashInterval);
  }, 500);
});

onUnmounted(() => {
  stopAlarmSound();
  document.title = 'Smart Reminder App';
});

function startAlarmSound() {
  if (isPlaying) return;

  // Respect user sound preference and use default alarm sound.
  try {
    const saved = localStorage.getItem('appSettings');
    if (saved) {
      const settings = JSON.parse(saved);
      if (settings?.soundAlerts === false) {
        return;
      }
    }
  } catch (error) {
    console.error('Error loading reminder sound settings:', error);
  }

  isPlaying = true;
  alarmAudio = new Audio(DEFAULT_ALARM_SOUND);
  alarmAudio.loop = true;
  alarmAudio.volume = 0.8;
  alarmAudio.play().catch((error) => {
    console.error('Error playing reminder sound:', error);
    alarmAudio = null;
    isPlaying = false;
    startToneAlarmFallback();
  });

}

function startToneAlarmFallback() {
  if (isPlaying) return;
  
  try {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    
    // Resume audio context if suspended (Chrome autoplay policy)
    if (audioContext.state === 'suspended') {
      audioContext.resume();
    }
    
    isPlaying = true;
    playAlarmPattern();
  } catch (error) {
    console.error('Error starting alarm sound:', error);
  }
}

function playAlarmPattern() {
  if (!audioContext || !isPlaying) return;

  // Create gain node for volume control
  gainNode = audioContext.createGain();
  gainNode.connect(audioContext.destination);
  gainNode.gain.value = 0.3; // 30% volume

  // Create three-tone alarm pattern: high-low-high (880Hz, 440Hz, 880Hz)
  const frequencies = [880, 440, 880]; // A5, A4, A5
  let currentTone = 0;

  function playNextTone() {
    if (!isPlaying || !audioContext) return;

    oscillator = audioContext.createOscillator();
    oscillator.type = 'sine';
    oscillator.frequency.value = frequencies[currentTone % frequencies.length];
    oscillator.connect(gainNode);
    
    const now = audioContext.currentTime;
    oscillator.start(now);
    oscillator.stop(now + 0.3);
    
    oscillator.onended = () => {
      currentTone++;
      setTimeout(() => {
        if (isPlaying) playNextTone();
      }, 100); // Short pause between tones
    };
  }

  playNextTone();
}

function stopAlarmSound() {
  isPlaying = false;

  if (alarmAudio) {
    alarmAudio.pause();
    alarmAudio.currentTime = 0;
    alarmAudio = null;
  }
  
  if (oscillator) {
    try {
      oscillator.stop();
      oscillator.disconnect();
    } catch (error) {
      // Oscillator might already be stopped
    }
    oscillator = null;
  }
  
  if (gainNode) {
    gainNode.disconnect();
    gainNode = null;
  }
  
  if (audioContext) {
    audioContext.close();
    audioContext = null;
  }
}

function stopAlarm() {
  stopAlarmSound();
  // Navigate back to dashboard
  window.location.href = '/dashboard';
}

function formatTime(date) {
  return new Date(date).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
}
</script>

<style scoped>
@keyframes pulse-slow {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.02);
  }
}

.animate-pulse-slow {
  animation: pulse-slow 2s ease-in-out infinite;
}
</style>
