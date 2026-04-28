/* ─────────────────────────────────────────
   dash-prof.js — SOEE · Dashboard Professor
   Depende de: adm.js (toast, abrirModal, fecharModal,
               trocarPainel, trocarPainelById,
               excluirRegistro, validarSumula)
───────────────────────────────────────── */

(function () {
    'use strict';

    /* ── helper: faz fetch e lida com JSON ou HTML de erro ── */
    function fetchJSON(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(function (r) {
            return r.text().then(function (txt) {
                console.log('[SOEE fetch]', url, '→ HTTP', r.status);
                console.log('[SOEE fetch] body:', txt);
                try {
                    return JSON.parse(txt);
                } catch (e) {
                    return {
                        ok: false,
                        erro: 'Resposta do servidor: ' + txt.replace(/<[^>]+>/g, ' ').trim().substring(0, 200)
                    };
                }
            });
        });
    }

    /* ════════════════════════════════════════
       ELEGER / REMOVER ADM DE SALA
    ════════════════════════════════════════ */
    window.elegerAdmSala = function (idAluno, nomeAluno) {
        if (!confirm('Alterar cargo de "' + nomeAluno + '"?')) return;
        fetchJSON('/soee/src/backend/actions/eleger-adm-sala.php', 'id_usuario=' + idAluno)
        .then(function (d) {
            if (d.ok) {
                toast('Cargo de ' + nomeAluno + ' atualizado!', 'sucesso');
                setTimeout(function () { location.reload(); }, 1400);
            } else {
                toast('Erro: ' + (d.erro || 'desconhecido'), 'erro');
            }
        })
        .catch(function (e) { console.error('[elegerAdmSala]', e); toast('Erro de conexão.', 'erro'); });
    };

    /* ════════════════════════════════════════
       ALTERAR STATUS DE EDIÇÃO
    ════════════════════════════════════════ */
    window.alterarStatusEdicao = function (id, status, selectEl) {
        fetchJSON(
            '/soee/src/backend/actions/atualizar-status-edicao.php',
            'id_edicao=' + id + '&status=' + encodeURIComponent(status)
        )
        .then(function (d) {
            if (d.ok) {
                toast('Status atualizado com sucesso!', 'sucesso');
                var badgeMap = {
                    planejamento: '<span class="badge-status pendente">Planejamento</span>',
                    inscricoes:   '<span class="badge-status pendente">Inscrições</span>',
                    em_andamento: '<span class="badge-status ativo">Em Andamento</span>',
                    encerrado:    '<span class="badge-status encerrado">Encerrado</span>'
                };
                var row = selectEl.closest('tr');
                if (row) {
                    var badgeTd = row.querySelectorAll('td')[5];
                    if (badgeTd && badgeMap[status]) badgeTd.innerHTML = badgeMap[status];
                }
            } else {
                toast('Erro: ' + (d.erro || 'desconhecido'), 'erro');
            }
        })
        .catch(function (e) { console.error('[alterarStatusEdicao]', e); toast('Erro de conexão.', 'erro'); });
    };

    /* ════════════════════════════════════════
       SORTEIO DE PARTIDAS
    ════════════════════════════════════════ */
    window.gerarSorteio = function (emId, nomeModalidade, btnEl) {
        if (!confirm('Gerar sorteio para "' + nomeModalidade + '"?\n\nAs partidas serão criadas aleatoriamente. Essa ação não pode ser desfeita.')) return;

        if (btnEl) { btnEl.disabled = true; btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sorteando…'; }

        var modal = document.getElementById('modal-sorteio');
        document.getElementById('sorteio-loading').style.display  = 'block';
        document.getElementById('sorteio-resultado').style.display = 'none';
        document.getElementById('sorteio-footer').style.display   = 'none';
        modal.classList.add('open');

        fetchJSON('/soee/src/backend/actions/gerar-sorteio.php', 'edicao_modalidade_id=' + emId)
        .then(function (d) {
            document.getElementById('sorteio-loading').style.display = 'none';
            document.getElementById('sorteio-footer').style.display  = 'block';
            var res = document.getElementById('sorteio-resultado');
            res.style.display = 'block';

            if (!d.ok) {
                res.innerHTML = '<div class="sorteio-alerta erro"><i class="fas fa-circle-xmark"></i> ' + (d.erro || 'Erro desconhecido') + '</div>';
                if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = '<i class="fas fa-shuffle"></i> Sortear'; }
                return;
            }

            var html = '<div class="sorteio-alerta ok" style="margin-bottom:18px;"><i class="fas fa-check-circle"></i> ' + d.msg + '</div>';
            var grupos = {};
            (d.partidas || []).forEach(function (p) {
                var key = p.grupo ? 'Grupo ' + p.grupo : p.fase;
                if (!grupos[key]) grupos[key] = [];
                grupos[key].push(p);
            });
            Object.keys(grupos).forEach(function (key) {
                html += '<div class="sorteio-grupo-label"><i class="fas fa-layer-group"></i> ' + key + '</div>';
                grupos[key].forEach(function (p) {
                    html += '<div class="sorteio-partida-item"><span>' + p.time_a + '</span><span class="sorteio-vs">VS</span><span>' + p.time_b + '</span></div>';
                });
            });
            res.innerHTML = html;
            if (btnEl && btnEl.parentElement) {
                btnEl.outerHTML = '<span class="badge-sorteado"><i class="fas fa-check"></i> Sorteado</span>';
            }
        })
        .catch(function (e) {
            console.error('[gerarSorteio]', e);
            document.getElementById('sorteio-loading').style.display = 'none';
            document.getElementById('sorteio-resultado').innerHTML = '<div class="sorteio-alerta erro"><i class="fas fa-circle-xmark"></i> Erro de conexão.</div>';
            document.getElementById('sorteio-resultado').style.display = 'block';
            document.getElementById('sorteio-footer').style.display = 'block';
            if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = '<i class="fas fa-shuffle"></i> Sortear'; }
        });
    };

    window.fecharModalSorteio = function () {
        document.getElementById('modal-sorteio').classList.remove('open');
    };

    /* ════════════════════════════════════════
       EDITAR PARTIDA (data / hora / local)
    ════════════════════════════════════════ */
    window.abrirEditarPartida = function (id, data, hora, local) {
        document.getElementById('edit-partida-id').value    = id;
        document.getElementById('edit-partida-data').value  = data;
        document.getElementById('edit-partida-hora').value  = hora;
        document.getElementById('edit-partida-local').value = local || '';
        abrirModal('modal-editar-partida');
    };

    function salvarEdicaoPartida() {
        var id    = document.getElementById('edit-partida-id').value;
        var data  = document.getElementById('edit-partida-data').value;
        var hora  = document.getElementById('edit-partida-hora').value;
        var local = document.getElementById('edit-partida-local').value;

        if (!data || !hora) { toast('Preencha data e hora.', 'aviso'); return; }

        fetchJSON(
            '/soee/src/backend/actions/editar-partida.php',
            'id_partida=' + encodeURIComponent(id) +
            '&data_partida=' + encodeURIComponent(data) +
            '&hora_partida=' + encodeURIComponent(hora) +
            '&local_partida=' + encodeURIComponent(local)
        )
        .then(function (d) {
            if (d.ok) {
                toast('Partida atualizada!', 'sucesso');
                fecharModal('modal-editar-partida');
                setTimeout(function () { location.reload(); }, 1200);
            } else {
                toast(d.erro || 'Erro desconhecido', 'erro');
            }
        })
        .catch(function (e) { console.error('[salvarEdicaoPartida]', e); toast('Erro de conexão.', 'erro'); });
    }

    /* ════════════════════════════════════════
       REGISTRAR RESULTADO INLINE
    ════════════════════════════════════════ */
    window.salvarResultadoInline = function (idPartida, nomeTimeA, nomeTimeB, btnEl) {
        var row  = btnEl.closest('tr');
        var inpA = row.querySelector('.placar-a');
        var inpB = row.querySelector('.placar-b');

        if (!inpA || !inpB) return;

        var pA = parseInt(inpA.value, 10);
        var pB = parseInt(inpB.value, 10);

        if (isNaN(pA) || isNaN(pB) || pA < 0 || pB < 0) {
            toast('Informe placares válidos (≥ 0).', 'aviso');
            return;
        }

        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetchJSON(
            '/soee/src/backend/actions/salvar-resultado-inline.php',
            'partida_id=' + encodeURIComponent(idPartida) +
            '&placar_time_a=' + encodeURIComponent(pA) +
            '&placar_time_b=' + encodeURIComponent(pB)
        )
        .then(function (d) {
            if (d.ok) {
                toast('Resultado salvo!', 'sucesso');
                var vencedor = pA > pB ? nomeTimeA : (pB > pA ? nomeTimeB : 'Empate');
                var cell = row.querySelector('.td-resultado');
                if (cell) {
                    cell.innerHTML =
                        '<span class="resultado-vencedor-badge">' +
                        '<i class="fas fa-trophy"></i> ' + vencedor +
                        ' (' + pA + '\u2013' + pB + ')' +
                        '</span>';
                }
                setTimeout(function () { location.reload(); }, 1400);
            } else {
                toast(d.erro || 'Erro desconhecido', 'erro');
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="fas fa-check"></i> Salvar';
            }
        })
        .catch(function (e) {
            console.error('[salvarResultadoInline]', e);
            toast('Erro de conexão.', 'erro');
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="fas fa-check"></i> Salvar';
        });
    };

    /* ════════════════════════════════════════
       INICIALIZAÇÃO
    ════════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', function () {

        var ms = document.getElementById('modal-sorteio');
        if (ms) ms.addEventListener('click', function (e) { if (e.target === ms) fecharModalSorteio(); });

        var btnSalvarEd = document.getElementById('btn-salvar-edicao-partida');
        if (btnSalvarEd) btnSalvarEd.addEventListener('click', salvarEdicaoPartida);

        (function () {
            var p = new URLSearchParams(window.location.search);
            if (p.get('ok') === '1') history.replaceState({}, '', window.location.pathname);
        })();
    });

})();
