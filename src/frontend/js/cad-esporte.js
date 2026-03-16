/* cad-esporte.js — SOEE */

document.addEventListener('DOMContentLoaded', function () {

  /* ── TEMA ── */
  var btnTema = document.getElementById('toggle-theme');
  if (btnTema) {
    var icoTema = btnTema.querySelector('i');
    function setTheme(t) {
      document.documentElement.setAttribute('data-theme', t);
      localStorage.setItem('theme', t);
      icoTema.className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
    setTheme(localStorage.getItem('theme') || 'light');
    btnTema.addEventListener('click', function () {
      setTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
  }

  /* ── FOTO: abas ── */
  var origenInput   = document.getElementById('origem_foto');
  var painelUpload  = document.getElementById('painel-upload');
  var painelUrl     = document.getElementById('painel-url');
  var painelNenhuma = document.getElementById('painel-nenhuma');

  function mostrarFoto(alvo) {
    if (painelUpload)  painelUpload.style.display  = alvo === 'upload'  ? 'block' : 'none';
    if (painelUrl)     painelUrl.style.display      = alvo === 'url'     ? 'block' : 'none';
    if (painelNenhuma) painelNenhuma.style.display  = alvo === 'nenhuma' ? 'block' : 'none';
    if (origenInput)   origenInput.value = alvo;
    if (alvo !== 'upload') limparUpload();
    if (alvo !== 'url') {
      var fu = document.getElementById('foto_url');
      if (fu) fu.value = '';
    }
  }

  document.querySelectorAll('.foto-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      document.querySelectorAll('.foto-tab').forEach(function (t) { t.classList.remove('ativo'); });
      tab.classList.add('ativo');
      mostrarFoto(tab.dataset.tab);
    });
  });

  mostrarFoto('upload');

  /* ── FOTO: upload preview ── */
  var inputArquivo  = document.getElementById('foto_arquivo');
  var uploadArea    = document.getElementById('uploadArea');
  var uploadPreview = document.getElementById('uploadPreview');

  function limparUpload() {
    if (!inputArquivo || !uploadPreview) return;
    inputArquivo.value = '';
    uploadPreview.innerHTML =
      '<i class="fa-solid fa-cloud-arrow-up upload-icone"></i>' +
      '<p class="upload-txt">Clique ou arraste uma imagem aqui</p>' +
      '<p class="upload-sub">JPG, PNG, GIF ou WEBP \u2014 m\u00e1x. 5 MB</p>';
  }

  if (inputArquivo) {
    inputArquivo.addEventListener('change', function () {
      var file = inputArquivo.files[0];
      if (!file || !file.type.startsWith('image/')) return;
      var r = new FileReader();
      r.onload = function (e) {
        uploadPreview.innerHTML =
          '<img src="' + e.target.result + '" style="max-height:140px;border-radius:8px;" />' +
          '<p class="upload-nome">' + file.name + '</p>';
      };
      r.readAsDataURL(file);
    });
  }

  if (uploadArea) {
    uploadArea.addEventListener('dragover',  function (e) { e.preventDefault(); uploadArea.classList.add('drag-over'); });
    uploadArea.addEventListener('dragleave', function ()  { uploadArea.classList.remove('drag-over'); });
    uploadArea.addEventListener('drop', function (e) {
      e.preventDefault();
      uploadArea.classList.remove('drag-over');
      var file = e.dataTransfer.files[0];
      if (file && inputArquivo) {
        var dt = new DataTransfer();
        dt.items.add(file);
        inputArquivo.files = dt.files;
        inputArquivo.dispatchEvent(new Event('change'));
      }
    });
  }

  /* ── FOTO: URL preview ── */
  var inputUrl   = document.getElementById('foto_url');
  var urlPreview = document.getElementById('urlPreview');
  var urlTimer   = null;

  if (inputUrl && urlPreview) {
    inputUrl.addEventListener('input', function () {
      clearTimeout(urlTimer);
      var val = inputUrl.value.trim();
      if (!val) { urlPreview.innerHTML = '<i class="fa-solid fa-image"></i>'; return; }
      urlTimer = setTimeout(function () {
        var img = new Image();
        img.onload  = function () { urlPreview.innerHTML = '<img src="' + val + '" style="width:100%;height:100%;object-fit:cover;" />'; };
        img.onerror = function () { urlPreview.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>'; };
        img.src = val;
      }, 600);
    });
  }

  /* ── DURAÇÃO: mostra painel certo ao escolher tipo ── */
  var selectTipo    = document.getElementById('tipo_duracao');
  var painelMinutos = document.getElementById('painel-minutos');
  var painelPontos  = document.getElementById('painel-pontos');

  function mostrarDuracao(tipo) {
    if (painelMinutos) painelMinutos.style.display = tipo === 'minutos' ? 'block' : 'none';
    if (painelPontos)  painelPontos.style.display  = tipo === 'pontos'  ? 'block' : 'none';

    var sMin = document.getElementById('duracao_minutos');
    var sPts = document.getElementById('duracao_pontos');
    if (sMin) sMin.required = tipo === 'minutos';
    if (sPts) sPts.required = tipo === 'pontos';
  }

  if (selectTipo) {
    selectTipo.addEventListener('change', function () {
      mostrarDuracao(selectTipo.value);
    });
    /* Restaura estado se voltou com erro POST */
    if (selectTipo.value) mostrarDuracao(selectTipo.value);
  }

  /* ── DURAÇÃO: campo "outro" para minutagem ── */
  var selMin      = document.getElementById('duracao_minutos');
  var outroMinDiv = document.getElementById('outro_minutos');
  if (selMin && outroMinDiv) {
    selMin.addEventListener('change', function () {
      outroMinDiv.style.display = selMin.value === 'outro' ? 'block' : 'none';
      outroMinDiv.required = selMin.value === 'outro';
    });
    if (selMin.value === 'outro') outroMinDiv.style.display = 'block';
  }

  /* ── DURAÇÃO: campo "outro" para pontuação ── */
  var selPts      = document.getElementById('duracao_pontos');
  var outroPtsDiv = document.getElementById('outro_pontos');
  if (selPts && outroPtsDiv) {
    selPts.addEventListener('change', function () {
      outroPtsDiv.style.display = selPts.value === 'outro' ? 'block' : 'none';
      outroPtsDiv.required = selPts.value === 'outro';
    });
    if (selPts.value === 'outro') outroPtsDiv.style.display = 'block';
  }

  /* ── VALIDAÇÃO: qtd_max >= qtd_min ── */
  var form     = document.getElementById('formModalidade');
  var inputMin = document.getElementById('qtd_min_jogadores');
  var inputMax = document.getElementById('qtd_max_jogadores');

  if (inputMin && inputMax) {
    inputMax.addEventListener('input', function () { inputMax.setCustomValidity(''); });
    inputMin.addEventListener('input', function () {
      var mn = parseInt(inputMin.value, 10);
      var mx = parseInt(inputMax.value, 10);
      inputMax.setCustomValidity((!isNaN(mn) && !isNaN(mx) && mx < mn) ? 'O m\u00e1ximo deve ser \u2265 ao m\u00ednimo.' : '');
    });
  }

  if (form && inputMin && inputMax) {
    form.addEventListener('submit', function (e) {
      var mn = parseInt(inputMin.value, 10);
      var mx = parseInt(inputMax.value, 10);
      if (!isNaN(mn) && !isNaN(mx) && mx < mn) {
        e.preventDefault();
        inputMax.setCustomValidity('O m\u00e1ximo deve ser maior ou igual ao m\u00ednimo.');
        inputMax.reportValidity();
      }
    });
  }

});