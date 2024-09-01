import { createApp } from 'vue';
import NeoStats from './components/NeoStats.vue';
import './assets/styles.css'; // Import the global CSS file

createApp({
  components: {
    NeoStats
  }
}).mount('#app');
