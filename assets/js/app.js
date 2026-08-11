(() => {
  'use strict';
  const body = document.body;
  const navToggle = document.querySelector('.nav-toggle');
  const mainNav = document.querySelector('.main-nav');
  navToggle?.addEventListener('click', () => {
    const open = mainNav.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', String(open));
    body.classList.toggle('no-scroll', open);
  });
  document.querySelectorAll('.dropdown-trigger').forEach(trigger => {
    trigger.addEventListener('click', event => {
      const parent = trigger.closest('.nav-dropdown');
      const open = !parent.classList.contains('open');
      document.querySelectorAll('.nav-dropdown.open').forEach(item => {
        if (item !== parent) item.classList.remove('open');
      });
      parent.classList.toggle('open', open);
      trigger.setAttribute('aria-expanded', String(open));
      if (window.innerWidth <= 800) event.preventDefault();
    });
  });
  document.addEventListener('click', event => {
    if (!event.target.closest('.nav-dropdown')) {
      document.querySelectorAll('.nav-dropdown.open').forEach(item => item.classList.remove('open'));
    }
  });
  const overlay = document.querySelector('.search-overlay');
  const openSearch = () => { overlay?.classList.add('open'); overlay?.setAttribute('aria-hidden','false'); body.classList.add('no-scroll'); setTimeout(() => overlay?.querySelector('input')?.focus(), 100); };
  const closeSearch = () => { overlay?.classList.remove('open'); overlay?.setAttribute('aria-hidden','true'); body.classList.remove('no-scroll'); };
  document.querySelector('.search-open')?.addEventListener('click', openSearch);
  document.querySelector('.search-close')?.addEventListener('click', closeSearch);
  overlay?.addEventListener('click', event => { if (event.target === overlay) closeSearch(); });
  document.addEventListener('keydown', event => { if (event.key === 'Escape') closeSearch(); if (event.key === '/' && !['INPUT','TEXTAREA'].includes(document.activeElement.tagName)) { event.preventDefault(); openSearch(); } });
  document.querySelectorAll('.toast').forEach((toast,index) => {
    toast.querySelector('button')?.addEventListener('click', () => toast.remove());
    setTimeout(() => toast.remove(), 5000 + index * 600);
  });
  const topButton = document.querySelector('.back-to-top');
  window.addEventListener('scroll', () => topButton?.classList.toggle('visible', window.scrollY > 500), {passive:true});
  topButton?.addEventListener('click', () => window.scrollTo({top:0,behavior:'smooth'}));
  document.querySelectorAll('.copy-link').forEach(button => button.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(window.location.href); const original=button.textContent; button.textContent='✓ Đã chép'; setTimeout(()=>button.textContent=original,1500); } catch(e) { window.prompt('Sao chép liên kết:',window.location.href); }
  }));
  document.querySelectorAll('[data-fill-email]').forEach(button => button.addEventListener('click', () => {
    const form=button.closest('.auth-form-wrap'); form.querySelector('[name=email]').value=button.dataset.fillEmail; form.querySelector('[name=password]').value=button.dataset.fillPassword;
  }));
  const fileInput=document.querySelector('.upload-zone input[type=file]');
  fileInput?.addEventListener('change', () => {
    const file=fileInput.files[0], zone=fileInput.closest('.upload-zone'), preview=zone.querySelector('.upload-preview');
    if (!file) return; if (file.size>5*1024*1024) { alert('Ảnh lớn hơn 5MB. Vui lòng chọn ảnh khác.'); fileInput.value=''; return; }
    const reader=new FileReader(); reader.onload=event=>{preview.src=event.target.result;zone.classList.add('has-preview');};reader.readAsDataURL(file);
  });
  document.querySelectorAll('.moderation-form').forEach(form => form.addEventListener('submit', event => {
    const submitter=event.submitter; if (!submitter || submitter.value==='approved') return;
    const note=form.querySelector('[name=admin_note]'); if (note.value.trim().length<5) { event.preventDefault(); note.focus(); alert('Vui lòng nhập ghi chú ít nhất 5 ký tự.'); }
  }));
})();
