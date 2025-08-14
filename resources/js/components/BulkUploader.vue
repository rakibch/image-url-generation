<template>
  <div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Thumbnail Bulk Uploader</h3>
      <div v-if="user">
        <span class="me-2">Logged in as <strong>{{ user.name }}</strong> ({{ user.plan }})</span>
        <button class="btn btn-outline-secondary btn-sm" @click="logout">Logout</button>
      </div>
    </div>

    <div v-if="!user" class="card p-3 mb-4">
      <h5 class="mb-3">Login / Register</h5>
      <div class="row g-2">
        <div class="col-md-3">
          <input v-model="auth.name" class="form-control" placeholder="Name (for register)" />
        </div>
        <div class="col-md-3">
          <input v-model="auth.email" class="form-control" placeholder="Email" />
        </div>
        <div class="col-md-3">
          <input v-model="auth.password" type="password" class="form-control" placeholder="Password" />
        </div>
        <div class="col-md-3">
          <select v-model="auth.plan" class="form-select">
            <option value="free">free</option>
            <option value="pro">pro</option>
            <option value="enterprise">enterprise</option>
          </select>
        </div>
      </div>
      <div class="mt-2">
        <button class="btn btn-primary me-2" @click="register">Register</button>
        <button class="btn btn-success" @click="login">Login</button>
      </div>
    </div>

    <div v-if="user" class="card p-3 mb-4">
      <label class="form-label">Paste image URLs (one per line)</label>
      <textarea v-model="urls" rows="6" class="form-control" placeholder="https://example.com/a.jpg&#10;https://example.com/b.png"></textarea>
      <button class="btn btn-primary mt-3" :disabled="submitting" @click="submit">
        {{ submitting ? 'Submitting…' : 'Submit' }}
      </button>
      <div v-if="quotaMsg" class="text-muted mt-2">{{ quotaMsg }}</div>
    </div>

    <div v-if="bulkId" class="mb-2">
      <label>Status Filter:</label>
      <select class="form-select w-auto d-inline-block ms-2" v-model="filter" @change="fetchTasks">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="processed">Processed</option>
        <option value="failed">Failed</option>
      </select>
      <span class="ms-3">Bulk Request #{{ bulkId }}</span>
    </div>

    <table v-if="tasks.length" class="table table-striped">
      <thead>
        <tr>
          <th>Image URL</th>
          <th>Status</th>
          <th>Timestamp</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in tasks" :key="t.id">
          <td class="text-truncate" style="max-width:580px">{{ t.image_url }}</td>
          <td>
            <span :class="badgeClass(t.status)">{{ t.status }}</span>
          </td>
          <td>{{ t.processed_at || t.created_at }}</td>
        </tr>
      </tbody>
    </table>

    <p v-else-if="bulkId">No tasks yet…</p>
  </div>
</template>

<script>
export default {
  data(){
    return {
      auth: { name:'', email:'', password:'', plan:'free' },
      user: null,
      urls: '',
      submitting: false,
      bulkId: null,
      filter: '',
      tasks: [],
      poller: null,
      quotaMsg: ''
    };
  },
  mounted(){
    const token = localStorage.getItem('token');
    const user  = localStorage.getItem('user');
    if (token && user) this.user = JSON.parse(user);
    if (this.user) this.setQuotaMsg();
  },
  methods: {
    apiHeaders(){
      const token = localStorage.getItem('token');
      return {
        'Content-Type':'application/json',
        'Accept':'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {})
      };
    },
    async register(){
      const res = await fetch('/api/register', {
        method:'POST',
        headers: this.apiHeaders(),
        body: JSON.stringify(this.auth)
      });
      const data = await res.json();
      if(!res.ok) return alert(data.message || 'Register failed');
      localStorage.setItem('token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      this.user = data.user;
      this.setQuotaMsg();
    },
    async login(){
      const res = await fetch('/api/login', {
        method:'POST',
        headers: this.apiHeaders(),
        body: JSON.stringify({ email:this.auth.email, password:this.auth.password })
      });
      const data = await res.json();
      if(!res.ok) return alert(data.message || 'Login failed');
      localStorage.setItem('token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      this.user = data.user;
      this.setQuotaMsg();
    },
    async logout(){
      await fetch('/api/logout', { method:'POST', headers: this.apiHeaders() });
      localStorage.removeItem('token'); localStorage.removeItem('user');
      this.user = null; this.bulkId = null; this.tasks = []; this.urls = '';
      this.stopPolling();
    },
    async submit(){
      this.submitting = true;
      try {
        const res = await fetch('/api/bulk-requests', {
          method:'POST',
          headers: this.apiHeaders(),
          body: JSON.stringify({ urls: this.urls })
        });
        const data = await res.json();
        if(!res.ok) throw new Error(data.message || 'Submit failed');
        this.bulkId = data.bulk_request_id;
        this.startPolling();
      } catch(e){ alert(e.message); }
      finally { this.submitting = false; }
    },
    async fetchTasks(){
      if(!this.bulkId) return;
      const qs = this.filter ? `?status=${this.filter}` : '';
      const res = await fetch(`/api/bulk-requests/${this.bulkId}/tasks${qs}`, {
        headers: this.apiHeaders()
      });
      const data = await res.json();
      this.tasks = data.data || [];
    },
    startPolling(){
      this.stopPolling();
      this.fetchTasks();
      this.poller = setInterval(this.fetchTasks, 2000);
    },
    stopPolling(){ if(this.poller){ clearInterval(this.poller); this.poller=null; } },
    badgeClass(s){
      return {
        'badge bg-secondary': s==='pending',
        'badge bg-success':   s==='processed',
        'badge bg-danger':    s==='failed'
      };
    },
    setQuotaMsg(){
      if(!this.user) return this.quotaMsg = '';
      const plan = this.user.plan;
      const map = { free:50, pro:100, enterprise:200 };
      this.quotaMsg = `Plan "${plan}": up to ${map[plan]} URLs per request.`;
    }
  },
  beforeUnmount(){ this.stopPolling(); }
}
</script>
