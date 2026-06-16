<?php
/*
 * ═══════════════════════════════════════════════════════════
 *  PARTIAL: formulario_inscricao.php  —  SOEE
 *
 *  Substitui o bloco <form> dentro do loop foreach ($modalidades)
 *  em aluno.php. Detecta automaticamente se é solo/time (form
 *  simples) ou dupla/trio (form com autocomplete de parceiro).
 *
 *  Variáveis esperadas no escopo:
 *    $md              — linha da modalidade (array)
 *    $nomeCamisaSalvo — string do nome de camisa salvo na sessão
 * ═══════════════════════════════════════════════════════════
 *
 *  COMO USAR: dentro do foreach de modalidades em aluno.php,
 *  onde estava o <form onsubmit="enviarInscricao(...)">
 *  substitua pelo include deste arquivo.
 */

$tipoParticipacao = $md['tipo_participacao'] ?? 'time';
$emIdForm         = $md['id_edicao_modalidade'];

if ($tipoParticipacao === 'dupla' || $tipoParticipacao === 'trio'):
    $labelParceiro = $tipoParticipacao === 'dupla' ? 'Parceiro(a)' : 'Parceiro(a) 1';
?>

<!-- ══ Formulário de Dupla / Trio ══════════════════════ -->
<form onsubmit="enviarInscricaoDupla(event, <?= $emIdForm ?>, '<?= $tipoParticipacao ?>')">

    <!-- Dados do próprio aluno -->
    <div class="dupla-secao">
        <div class="dupla-secao-titulo">
            <i class="fa-solid fa-user"></i> Seus dados
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <div>
                <label class="insc-label">
                    <i class="fa-solid fa-shirt" style="font-size:.7rem"></i>
                    Nome da Camisa <span class="insc-opt">(opcional)</span>
                </label>
                <input type="text" name="nome_camisa"
                       class="insc-input"
                       placeholder="Ex: Cafu, Neymar…"
                       value="<?= htmlspecialchars($nomeCamisaSalvo) ?>"
                       maxlength="20">
            </div>
            <div>
                <label class="insc-label">
                    Nº Camisa <span class="insc-opt">(opcional)</span>
                </label>
                <input type="number" name="camisa" min="1" max="99"
                       class="insc-input" placeholder="Ex: 10">
            </div>
        </div>
    </div>

    <!-- Parceiro 1 -->
    <div class="dupla-secao">
        <div class="dupla-secao-titulo">
            <i class="fa-solid fa-user-plus"></i> <?= $labelParceiro ?>
        </div>

        <!-- Busca com autocomplete -->
        <div>
            <label class="insc-label">Buscar pelo nome</label>
            <div class="parceiro-busca-wrap">
                <input type="text"
                       class="insc-input parceiro-busca-input"
                       data-emid="<?= $emIdForm ?>"
                       data-target="parceiro1_id"
                       placeholder="Digite o nome do(a) parceiro(a)…"
                       autocomplete="off">
                <!-- hidden com o id real -->
                <input type="hidden" name="parceiro1_id" value="">
            </div>
        </div>

        <!-- Dados do parceiro 1 -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
            <div>
                <label class="insc-label">
                    Nome da Camisa <span class="insc-opt">(opcional)</span>
                </label>
                <input type="text" name="nome_camisa_p1"
                       class="insc-input" placeholder="Nome camisa do(a) parceiro(a)"
                       maxlength="20">
            </div>
            <div>
                <label class="insc-label">
                    Nº Camisa <span class="insc-opt">(opcional)</span>
                </label>
                <input type="number" name="camisa_p1" min="1" max="99"
                       class="insc-input" placeholder="Ex: 7">
            </div>
        </div>
    </div>

    <?php if ($tipoParticipacao === 'trio'): ?>
    <!-- Parceiro 2 (só para trio) -->
    <div class="dupla-secao">
        <div class="dupla-secao-titulo">
            <i class="fa-solid fa-user-plus"></i> Parceiro(a) 2
        </div>
        <div>
            <label class="insc-label">Buscar pelo nome</label>
            <div class="parceiro-busca-wrap">
                <input type="text"
                       class="insc-input parceiro-busca-input"
                       data-emid="<?= $emIdForm ?>"
                       data-target="parceiro2_id"
                       placeholder="Digite o nome do(a) segundo(a) parceiro(a)…"
                       autocomplete="off">
                <input type="hidden" name="parceiro2_id" value="">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
            <div>
                <label class="insc-label">
                    Nome da Camisa <span class="insc-opt">(opcional)</span>
                </label>
                <input type="text" name="nome_camisa_p2"
                       class="insc-input" placeholder="Nome camisa do(a) parceiro(a)"
                       maxlength="20">
            </div>
            <div>
                <label class="insc-label">
                    Nº Camisa <span class="insc-opt">(opcional)</span>
                </label>
                <input type="number" name="camisa_p2" min="1" max="99"
                       class="insc-input" placeholder="Ex: 9">
            </div>
        </div>
    </div>
    <?php endif; ?>

    <button type="submit" class="btn-inscrever" style="width:100%;margin-top:8px;">
        <i class="fa-solid fa-people-group"></i>
        Inscrever <?= $tipoParticipacao === 'dupla' ? 'dupla' : 'trio' ?>
    </button>

</form>

<?php else: ?>

<!-- ══ Formulário simples (solo / time) ════════════════ -->
<form onsubmit="enviarInscricao(event, <?= $emIdForm ?>)">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:end;">
        <div>
            <label class="insc-label">
                <i class="fa-solid fa-shirt" style="font-size:.7rem"></i>
                Nome da Camisa <span class="insc-opt">(opcional)</span>
            </label>
            <input type="text" name="nome_camisa"
                   class="insc-input"
                   placeholder="Ex: Cafu, Neymar…"
                   value="<?= htmlspecialchars($nomeCamisaSalvo) ?>"
                   maxlength="20">
        </div>
        <div>
            <label class="insc-label">Nº Camisa <span class="insc-opt">(opcional)</span></label>
            <input type="number" name="camisa" min="1" max="99"
                   class="insc-input" placeholder="Ex: 10">
        </div>
        <button type="submit" class="btn-inscrever">
            <i class="fa-solid fa-plus"></i> Inscrever
        </button>
    </div>
</form>

<?php endif; ?>
