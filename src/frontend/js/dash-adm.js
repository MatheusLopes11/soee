// ── Validar súmula via fetch (sem reload) ─────────────────
function validarSumula(id, status) {
    var msg = status === 'validada' ? 'Súmula validada!' : 'Súmula rejeitada.';
    var tipo = status === 'validada' ? 'sucesso' : 'erro';
    fetch('/soee/src/backend/php/actions/validar-sumula.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_sumula=' + id + '&status_sumula=' + status
    }).then(function(r){ return r.json(); }).then(function(d){
        if(d.ok){ toast(msg, tipo); setTimeout(function(){ location.reload(); }, 1200); }
        else { toast('Erro: ' + (d.erro || 'desconhecido'), 'erro'); }
    }).catch(function(){ toast('Erro de conexão.','erro'); });
}

// ── Excluir registro genérico via fetch ───────────────────
function excluirRegistro(entidade, id) {
    if(!confirm('Excluir este(a) ' + entidade + '? Esta ação não pode ser desfeita.')) return;
    fetch('/soee/src/backend/php/actions/excluir-registro.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'entidade=' + entidade + '&id=' + id
    }).then(function(r){ return r.json(); }).then(function(d){
        if(d.ok){ toast('Registro excluído.','sucesso'); setTimeout(function(){ location.reload(); }, 1200); }
        else { toast('Erro: ' + (d.erro || 'desconhecido'), 'erro'); }
    }).catch(function(){ toast('Erro de conexão.','erro'); });
}

// ── Editar usuário (pré-preencher modal) ──────────────────
function editarUsuario(id) {
    var row = document.querySelector('#tabela-usuarios tr[data-id="' + id + '"]');
    if(!row) return;
    var cells = row.querySelectorAll('td');
    document.getElementById('u-id').value    = id;
    document.getElementById('u-nome').value  = cells[1].textContent.trim();
    document.getElementById('u-email').value = cells[2].textContent.trim();
    document.getElementById('modal-usuario-titulo').textContent = 'Editar Usuário';
    abrirModal('modal-usuario');
}