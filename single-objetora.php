<?php get_header() ?>

<main class="min-h-screen bg-gray-100 flex flex-col">
  <?php
  $glb_file_id = carbon_get_post_meta(get_the_ID(), 'glb_file');

  if ($glb_file_id) :
    $glb_file_url    = wp_get_attachment_url($glb_file_id);
    $glb_attachment  = get_post($glb_file_id);
    $glb_filesize_bytes = filesize(get_attached_file($glb_file_id));
    $glb_filesize_mb = $glb_filesize_bytes ? number_format($glb_filesize_bytes / 1048576, 2) . ' MB' : 'N/A';
    $glb_upload_date = $glb_attachment ? date_i18n('d/m/Y H:i', strtotime($glb_attachment->post_date)) : 'N/A';
    $glb_filename    = $glb_attachment ? basename(get_attached_file($glb_file_id)) : 'N/A';
  ?>

  <!-- Viewer 3D -->
  <section class="w-full bg-white" style="height: 70vh;">
    <model-viewer
      id="model-viewer"
      class="w-full h-full"
      alt="<?php echo esc_attr(get_the_title()) ?>"
      src="<?php echo esc_url($glb_file_url) ?>"
      ar shadow-intensity="1" camera-controls touch-action="pan-y"
      style="width:100%; height:100%; --progress-bar-color: transparent; --progress-bar-height: 0px;">
      <!-- Barra de progresso customizada na parte inferior -->
      <div slot="progress-bar"
           style="position:absolute; bottom:0; left:0; right:0; height:4px; background:rgba(0,0,0,0.08);">
        <div id="mv-progress-fill"
             style="height:100%; width:0%; background:#3b82f6; transition: width 0.2s ease;"></div>
      </div>
    </model-viewer>
  </section>

  <script>
    (function () {
      var mv   = document.getElementById('model-viewer');
      var fill = document.getElementById('mv-progress-fill');

      if (!mv) return;

      // Barra de progresso
      if (fill) {
        mv.addEventListener('progress', function (e) {
          fill.style.width = (e.detail.totalProgress * 100).toFixed(1) + '%';
          if (e.detail.totalProgress < 1) {
            fill.style.opacity = '1';
          } else {
            fill.style.opacity = '0';
          }
        });
      }

      // Dados técnicos após carregamento
      mv.addEventListener('load', function () {

        // --- Dimensões (API oficial) com alternância de unidade ---
        var dim       = mv.getDimensions(); // valores em metros
        var units     = ['cm', 'm', 'km'];
        var unitIndex = 0;

        var conversions = {
          cm: function (v) { return (v * 100).toFixed(2) + ' cm'; },
          m:  function (v) { return v.toFixed(4) + ' m'; },
          km: function (v) { return (v / 1000).toFixed(7) + ' km'; },
        };

        var dimCells = [
          { id: 'mv-dim-x', val: dim.x },
          { id: 'mv-dim-y', val: dim.y },
          { id: 'mv-dim-z', val: dim.z },
        ];

        function renderDimensions() {
          var fmt = conversions[units[unitIndex]];
          dimCells.forEach(function (cell) {
            var el = document.getElementById(cell.id);
            if (el) el.textContent = fmt(cell.val);
          });
        }

        renderDimensions();

        dimCells.forEach(function (cell) {
          var el = document.getElementById(cell.id);
          if (!el) return;
          el.style.cursor = 'pointer';
          el.title = 'Clique para alternar unidade';
          el.addEventListener('click', function () {
            unitIndex = (unitIndex + 1) % units.length;
            renderDimensions();
          });
        });

        // --- Contagem de polígonos (traversal interno Three.js) ---
        var polyCount  = 0;
        var meshCount  = 0;
        try {
          var symbols    = Object.getOwnPropertySymbols(mv);
          var sceneSym   = symbols.find(function (s) {
            return s.toString().toLowerCase().includes('scene');
          });
          if (sceneSym && mv[sceneSym] && mv[sceneSym].traverse) {
            mv[sceneSym].traverse(function (node) {
              if (node.isMesh && node.geometry) {
                meshCount++;
                var geo = node.geometry;
                if (geo.index) {
                  polyCount += geo.index.count / 3;
                } else if (geo.attributes && geo.attributes.position) {
                  polyCount += geo.attributes.position.count / 3;
                }
              }
            });
          }
        } catch (e) { /* API interna indisponível */ }

        setCell('mv-polygons', polyCount > 0 ? polyCount.toLocaleString('pt-BR') : 'N/A');
        setCell('mv-meshes',   meshCount > 0 ? meshCount  : 'N/A');

        // --- Validação glTF via bundle local ---
        var validationBody  = document.getElementById('gltf-validation-body');
        var validationBadge = document.getElementById('gltf-badge');
        var glbSrc          = mv.getAttribute('src');

        if (validationBody && glbSrc && typeof window.gltfValidate === 'function') {
          window.gltfValidate(glbSrc)
            .then(function (report) {
              var issues   = report.issues;
              var hasError = issues.numErrors > 0;
              var hasWarn  = issues.numWarnings > 0;

              if (hasError) {
                validationBadge.textContent = issues.numErrors + ' erro(s)';
                validationBadge.className = 'text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-600';
              } else if (hasWarn) {
                validationBadge.textContent = issues.numWarnings + ' aviso(s)';
                validationBadge.className = 'text-xs font-medium px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-600';
              } else {
                validationBadge.textContent = 'Válido';
                validationBadge.className = 'text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-600';
              }

              var html = '<div class="flex flex-wrap gap-2 mb-4">'
                + pill(issues.numErrors,   'Erros',  'red')
                + pill(issues.numWarnings, 'Avisos', 'yellow')
                + pill(issues.numInfos,    'Infos',  'blue')
                + pill(issues.numHints,    'Hints',  'gray')
                + '</div>';

              if (issues.messages && issues.messages.length > 0) {
                html += '<ul class="space-y-1.5">';
                issues.messages.forEach(function (msg) {
                  var colors = { ERROR: 'text-red-600', WARNING: 'text-yellow-600', INFORMATION: 'text-blue-600', HINT: 'text-gray-400' };
                  var color  = colors[msg.severity] || 'text-gray-500';
                  html += '<li class="flex gap-2 text-xs leading-relaxed">'
                    + '<span class="font-semibold shrink-0 ' + color + '">[' + msg.severity + ']</span>'
                    + '<span class="text-gray-600">' + escHtml(msg.message)
                    + (msg.pointer ? ' <span class="text-gray-400">(' + escHtml(msg.pointer) + ')</span>' : '')
                    + '</span></li>';
                });
                html += '</ul>';
              } else {
                html += '<p class="text-xs text-green-600">Nenhum problema encontrado.</p>';
              }

              validationBody.innerHTML = html;

              // Botão e modal do relatório JSON
              var reportBtn   = document.getElementById('gltf-report-btn');
              var modal       = document.getElementById('gltf-modal');
              var modalBody   = document.getElementById('gltf-modal-body');
              var modalClose  = document.getElementById('gltf-modal-close');

              if (reportBtn && modal && modalBody && typeof window.JSONFormatter === 'function') {
                reportBtn.classList.remove('hidden');

                reportBtn.addEventListener('click', function () {
                  modalBody.innerHTML = '';
                  var formatter = new window.JSONFormatter(report, 2, { hoverPreviewEnabled: true });
                  modalBody.appendChild(formatter.render());
                  modal.classList.remove('hidden');
                  modal.classList.add('flex');
                });

                modalClose.addEventListener('click', function () {
                  modal.classList.add('hidden');
                  modal.classList.remove('flex');
                });

                modal.addEventListener('click', function (e) {
                  if (e.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                  }
                });
              }
            })
            .catch(function (err) {
              validationBadge.textContent = 'Falha';
              validationBadge.className = 'text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-600';
              validationBody.textContent = 'Erro ao validar: ' + err.message;
            });
        } else if (validationBody) {
          validationBody.textContent = 'Validador não disponível.';
        }
      });

      function setCell(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value;
      }

      function pill(count, label, color) {
        var map = { red: 'bg-red-50 text-red-600', yellow: 'bg-yellow-50 text-yellow-600', blue: 'bg-blue-50 text-blue-600', gray: 'bg-gray-100 text-gray-500' };
        return '<span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full ' + (map[color] || map.gray) + '"><strong>' + count + '</strong> ' + label + '</span>';
      }

      function escHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      }
    })();
  </script>

  <!-- Conteúdo abaixo do viewer -->
  <section class="max-w-3xl mx-auto w-full px-4 py-8 flex flex-col gap-6">

    <!-- Título -->
    <h1 class="text-2xl font-semibold text-gray-800"><?php echo esc_html(get_the_title()) ?></h1>

    <!-- Tabela técnica -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Dados técnicos do modelo</span>
      </div>
      <table class="w-full text-sm text-gray-700">
        <tbody>
          <tr class="border-b border-gray-100">
            <td class="px-5 py-3 font-medium text-gray-500 w-40">Polígonos</td>
            <td class="px-5 py-3 tabular-nums" id="mv-polygons"><span class="text-gray-300">Carregando...</span></td>
          </tr>
          <tr class="border-b border-gray-100">
            <td class="px-5 py-3 font-medium text-gray-500">Meshes</td>
            <td class="px-5 py-3 tabular-nums" id="mv-meshes"><span class="text-gray-300">Carregando...</span></td>
          </tr>
          <tr class="border-b border-gray-100">
            <td class="px-5 py-3 font-medium text-gray-500">Largura</td>
            <td class="px-5 py-3 tabular-nums" id="mv-dim-x"><span class="text-gray-300">Carregando...</span></td>
          </tr>
          <tr class="border-b border-gray-100">
            <td class="px-5 py-3 font-medium text-gray-500">Altura</td>
            <td class="px-5 py-3 tabular-nums" id="mv-dim-y"><span class="text-gray-300">Carregando...</span></td>
          </tr>
          <tr>
            <td class="px-5 py-3 font-medium text-gray-500">Profundidade</td>
            <td class="px-5 py-3 tabular-nums" id="mv-dim-z"><span class="text-gray-300">Carregando...</span></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Validação glTF -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Validação glTF</span>
        <div class="flex items-center gap-2">
          <span id="gltf-badge" class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-400">Aguardando...</span>
          <button id="gltf-report-btn"
            class="hidden text-xs font-medium px-2 py-0.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors"
            type="button">
            Ver relatório JSON
          </button>
        </div>
      </div>
      <div id="gltf-validation-body" class="px-5 py-4 text-sm text-gray-400">
        Carregando validação...
      </div>
    </div>

    <!-- Modal relatório JSON -->
    <div id="gltf-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-screen flex flex-col" style="max-height:85vh;">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 shrink-0">
          <span class="text-sm font-semibold text-gray-700">Relatório completo — glTF Validator</span>
          <button id="gltf-modal-close" type="button" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
        </div>
        <div id="gltf-modal-body" class="overflow-auto px-5 py-4 text-xs font-mono"></div>
      </div>
    </div>

    <!-- Tabela de informações -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Informações do arquivo</span>
      </div>
      <table class="w-full text-sm text-gray-700">
        <tbody>
          <tr class="border-b border-gray-100">
            <td class="px-5 py-3 font-medium text-gray-500 w-40">Nome</td>
            <td class="px-5 py-3 break-all"><?php echo esc_html($glb_filename) ?></td>
          </tr>
          <tr class="border-b border-gray-100">
            <td class="px-5 py-3 font-medium text-gray-500">Peso</td>
            <td class="px-5 py-3"><?php echo esc_html($glb_filesize_mb) ?></td>
          </tr>
          <tr class="border-b border-gray-100">
            <td class="px-5 py-3 font-medium text-gray-500">Upload em</td>
            <td class="px-5 py-3"><?php echo esc_html($glb_upload_date) ?></td>
          </tr>
          <tr>
            <td class="px-5 py-3 font-medium text-gray-500">URL</td>
            <td class="px-5 py-3 break-all">
              <a href="<?php echo esc_url($glb_file_url) ?>" class="text-blue-600 hover:underline">
                <?php echo esc_html($glb_file_url) ?>
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Ações -->
    <div class="flex flex-wrap gap-3">
      <a href="<?php echo esc_url($glb_file_url) ?>"
         class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
        Download GLB
      </a>
      <a href="<?php bloginfo('url') ?>"
         class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">
        Voltar para os modelos
      </a>
    </div>

  </section>

  <?php else: ?>
  <div class="flex items-center justify-center flex-1 py-20">
    <p class="text-gray-400 text-sm">Arquivo GLB não definido.</p>
  </div>
  <?php endif; ?>
</main>

<?php get_footer() ?>