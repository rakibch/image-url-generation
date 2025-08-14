import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import { createApp } from 'vue';

import BulkUploader from './components/BulkUploader.vue';

const app = createApp({});

// Register global components if needed
app.component('bulk-uploader', BulkUploader);

app.mount('#app');
