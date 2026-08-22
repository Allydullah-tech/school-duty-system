const BASE = '/school-duty-system/backend';

async function apiCall(path, method = 'GET', body = null) {
  const token = localStorage.getItem('sds_token');
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json' }
  };
  if (token) opts.headers['Authorization'] = 'Bearer ' + token;
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(BASE + path, opts);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Request failed');
  return data;
}

function saveSession(token, user) {
  localStorage.setItem('sds_token', token);
  localStorage.setItem('sds_user', JSON.stringify(user));
}

function getUser() {
  try { return JSON.parse(localStorage.getItem('sds_user')); } catch { return null; }
}

function requireAuth(role) {
  const u = getUser();
  const token = localStorage.getItem('sds_token');
  if (!u || !token) { window.location.href = 'login.html'; return null; }
  if (role && u.role !== role) { window.location.href = 'login.html'; return null; }
  return u;
}

function redirectIfLoggedIn() {
  const u = getUser();
  const token = localStorage.getItem('sds_token');
  if (!u || !token) return;
  const routes = { admin: 'admin.html', academician: 'academician.html', teacher: 'teacher.html', student: 'student.html' };
  if (routes[u.role]) window.location.href = routes[u.role];
}

function logout() {
  localStorage.removeItem('sds_token');
  localStorage.removeItem('sds_user');
  window.location.href = 'login.html';
}

function showAlert(containerId, msg, type = 'error') {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.innerHTML = `<div class="alert alert-${type}">${escapeHtml(msg)}</div>`;
  setTimeout(() => { if (el.firstChild) el.innerHTML = ''; }, 5000);
}

function badge(val) {
  if (!val) return '';
  const map = {
    pending: 'badge-pending', ongoing: 'badge-ongoing', completed: 'badge-completed',
    submitted: 'badge-submitted', graded: 'badge-graded',
    low: 'badge-low', medium: 'badge-medium', high: 'badge-high',
    in_progress: 'badge-in_progress', active: 'badge-active', closed: 'badge-closed',
    admin: 'badge-high', academician: 'badge-purple', teacher: 'badge-blue', student: 'badge-green',
    approved: 'badge-approved', rejected: 'badge-rejected',
    disabled: 'badge-closed'
  };
  const cls = map[val] || 'badge-pending';
  return `<span class="badge ${cls}">${val}</span>`;
}

function fmtDate(d) {
  if (!d) return '—';
  const dt = new Date(d);
  return dt.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
}

function fmtDateTime(d) {
  if (!d) return '—';
  return new Date(d).toLocaleString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
}

function escapeHtml(s) {
  if (!s) return '';
  return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

// shared timetable renderer
function renderTimetable(timetable, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  if (!timetable.length) {
    container.innerHTML = '<div class="empty-timetable">📅 No timetable entries found.</div>';
    return;
  }
  const timeSlots = [...new Set(timetable.map(t => t.time_slot))].sort();
  const daysOrder = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  let html = `<div class="timetable-legend">
    <div class="legend-item"><div class="legend-color subject"></div><span>Subject</span></div>
    <div class="legend-item"><div class="legend-color teacher"></div><span>Teacher</span></div>
    <div class="legend-item"><div class="legend-color class"></div><span>Class</span></div>
    <div class="legend-item"><div class="legend-color room"></div><span>Room</span></div>
  </div>
  <div class="timetable-wrapper"><div class="timetable-grid">
    <div class="timetable-row header">
      <div class="timetable-cell timetable-time">Time</div>
      ${daysOrder.map(d=>`<div class="timetable-cell">${d}</div>`).join('')}
    </div>`;
  for (let ts of timeSlots) {
    html += `<div class="timetable-row"><div class="timetable-cell timetable-time">${escapeHtml(ts)}</div>`;
    for (let day of daysOrder) {
      const e = timetable.find(t=>t.time_slot===ts && t.day_of_week===day);
      if (e) {
        html += `<div class="timetable-cell">
          <div class="timetable-subject">${escapeHtml(e.subject)}</div>
          <div class="timetable-field timetable-teacher"><span class="timetable-label">Teacher:</span><span class="timetable-value">${escapeHtml(e.teacher_name)}</span></div>
          <div class="timetable-field timetable-class"><span class="timetable-label">Class:</span><span class="timetable-value">${escapeHtml(e.class_name)}</span></div>
          ${e.room?`<div class="timetable-field timetable-room"><span class="timetable-label">Room:</span><span class="timetable-value">${escapeHtml(e.room)}</span></div>`:''}
        </div>`;
      } else {
        html += `<div class="timetable-cell"><div class="empty-cell">—</div></div>`;
      }
    }
    html += `</div>`;
  }
  html += `</div></div>`;
  container.innerHTML = html;
}
