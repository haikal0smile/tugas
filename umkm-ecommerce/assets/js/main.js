// Qty stepper buttons (product detail & cart forms)
document.addEventListener('click', function (e) {
  const btn = e.target.closest('[data-step]');
  if (!btn) return;
  const wrap = btn.closest('.qty-stepper');
  const input = wrap.querySelector('input[type="number"]');
  const step = parseInt(btn.dataset.step, 10);
  const min = parseInt(input.min || '1', 10);
  const max = input.max ? parseInt(input.max, 10) : Infinity;
  let val = parseInt(input.value || '1', 10) + step;
  if (val < min) val = min;
  if (val > max) val = max;
  input.value = val;
});

// Auto-submit cart quantity updates after a short pause typing
document.querySelectorAll('.qty-form input[type="number"]').forEach(function (input) {
  let timer;
  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () {
      input.form.requestSubmit();
    }, 500);
  });
});
