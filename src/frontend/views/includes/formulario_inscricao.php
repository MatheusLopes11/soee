<?php
// ─────────────────────────────────────────────────────────
//  formulario_inscricao.php
//  Incluído dentro do foreach de $modalidades no aluno.php.
//  Variáveis disponíveis no escopo:
//    $md              — dados da modalidade atual
//    $nomeCamisaSalvo — último nome de camisa do aluno
//  Só é incluído quando !$jaInscrito && !$bloqueado
// ─────────────────────────────────────────────────────────

$tipo = $md['tipo_participacao']; // 'solo', 'dupla', 'trio', 'time'
$emId = (int) $md['id_edicao_modalidade'];
?>

<?php if ($tipo === 'dupla' || $tipo === 'trio'): ?>
<!-- ══════ FORMULÁRIO DUPLA / TRIO ══════ -->
<form onsubmit="enviarInscricaoDupla(event, <?= $emId ?>, '<?= $tipo ?>')">

    <div style="background:rgba(249,115,22,.06);border:1px solid rgba(249,115,22,.2);
                border-radius:10px;padding:12px 14px;margin-bottom:14px;font-size:.8rem;
                color:var(--texto-2,#64748b);">
        <i class="fa-solid fa-people-group" style="color:#f97316"></i>
        <?= $tipo === 'dupla'
            ? 'Modalidade em <strong>dupla</strong> — preencha seus dados e selecione seu(sua) parceiro(a).'
            : 'Modalidade em <strong>trio</strong> — preencha seus dados e selecione dois(duas) parceiros(as).' ?>
    </div>

    <!-- ── Meus dados ── -->
    <div style="margin-bottom:10px;">
        <div style="font-size:.75rem;font-weight:700;color:var(--texto-2,#64748b);
                    text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
            <i class="fa-solid fa-user" style="color:#f97316"></i> Seus dados
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <div>
                <label class="insc-label">
                    <i class="fa-solid fa-shirt" style="font-size:.7rem"></i>
                    Nome da Camisa <span style="font-weight:400;opacity:.6">(opcional)</span>
                </label>
                <input type="text" name="nome_camisa" class="insc-input"
                       placeholder="Ex: Cafu, Neymar…"
                       value="<?= htmlspecialchars($nomeCamisaSalvo) ?>"
                       maxlength="20">
            </div>
            <div>
                <label class="insc-label">
                    Nº Camisa <span style="font-weight:400;opacity:.6">(opcional)</span>
                </label>
                <input type="number" name="camisa" min="1" max="99"
                       class="insc-input" placeholder="Ex: 10">
            </div>
        </div>
    </div>

    <!-- ── Parceiro 1 ── -->
    <div style="margin-bottom:10px;">
        <div style="font-size:.75rem;font-weight:700;color:var(--texto-2,#64748b);
                    text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
            <i class="fa-solid fa-user-plus" style="color:#f97316"></i>
            <?= $tipo === 'dupla' ? 'Parceiro(a)' : 'Parceiro(a) 1' ?>
            <span style="color:#ef4444">*</span>
        </div>
        <div style="position:relative;margin-bottom:8px;">
            <input type="text"
                   class="insc-input parceiro-busca-input"
                   placeholder="Digite o nome do(a) colega para buscar…"
                   data-emid="<?= $emId ?>"
                   data-target="parceiro1_id"
                   autocomplete="off">
            <input type="hidden" name="parceiro1_id">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <div>
                <label class="insc-label">
                    <i class="fa-solid fa-shirt" style="font-size:.7rem"></i>
                    Nome da Camisa do(a) parceiro(a) <span style="font-weight:400;opacity:.6">(opcional)</span>
                </label>
                <input type="text" name="nome_camisa_p1" class="insc-input"
                       placeholder="Ex: Marta, Pelé…" maxlength="20">
            </div>
            <div>
                <label class="insc-label">
                    Nº Camisa <span style="font-weight:400;opacity:.6">(opcional)</span>
                </label>
                <input type="number" name="camisa_p1" min="1" max="99"
                       class="insc-input" placeholder="Ex: 7">
            </div>
        </div>
    </div>

    <?php if ($tipo === 'trio'): ?>
    <!-- ── Parceiro 2 (só para trio) ── -->
    <div style="margin-bottom:10px;">
        <div style="font-size:.75rem;font-weight:700;color:var(--texto-2,#64748b);
                    text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
            <i class="fa-solid fa-user-plus" style="color:#f97316"></i>
            Parceiro(a) 2 <span style="color:#ef4444">*</span>
        </div>
        <div style="position:relative;margin-bottom:8px;">
            <input type="text"
                   class="insc-input parceiro-busca-input"
                   placeholder="Digite o nome do(a) colega para buscar…"
                   data-emid="<?= $emId ?>"
                   data-target="parceiro2_id"
                   autocomplete="off">
            <input type="hidden" name="parceiro2_id">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <div>
                <label class="insc-label">
                    <i class="fa-solid fa-shirt" style="font-size:.7rem"></i>
                    Nome da Camisa do(a) parceiro(a) <span style="font-weight:400;opacity:.6">(opcional)</span>
                </label>
                <input type="text" name="nome_camisa_p2" class="insc-input"
                       placeholder="Ex: Marta, Pelé…" maxlength="20">
            </div>
            <div>
                <label class="insc-label">
                    Nº Camisa <span style="font-weight:400;opacity:.6">(opcional)</span>
                </label>
                <input type="number" name="camisa_p2" min="1" max="99"
                       class="insc-input" placeholder="Ex: 9">
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div style="margin-top:12px;text-align:right;">
        <button type="submit" class="btn-inscrever">
            <i class="fa-solid fa-people-group"></i>
            Inscrever <?= $tipo === 'dupla' ? 'Dupla' : 'Trio' ?>
        </button>
    </div>

</form>

<?php else: ?>
<!-- ══════ FORMULÁRIO SOLO / TIME ══════ -->
<form onsubmit="enviarInscricao(event, <?= $emId ?>)">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:end;">
        <div>
            <label class="insc-label">
                <i class="fa-solid fa-shirt" style="font-size:.7rem"></i>
                Nome da Camisa <span style="font-weight:400;opacity:.6">(opcional)</span>
            </label>
            <input type="text" name="nome_camisa" class="insc-input"
                   placeholder="Ex: Cafu, Neymar…"
                   value="<?= htmlspecialchars($nomeCamisaSalvo) ?>"
                   maxlength="20">
        </div>
        <div>
            <label class="insc-label">
                Nº Camisa <span style="font-weight:400;opacity:.6">(opcional)</span>
            </label>
            <input type="number" name="camisa" min="1" max="99"
                   class="insc-input" placeholder="Ex: 10">
        </div>
        <button type="submit" class="btn-inscrever">
            <i class="fa-solid fa-plus"></i> Inscrever
        </button>
    </div>
</form>
<?php endif; ?>