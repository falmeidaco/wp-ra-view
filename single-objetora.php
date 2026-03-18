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
      var mv = document.getElementById('model-viewer');
      var fill = document.getElementById('mv-progress-fill');
      if (!mv || !fill) return;
      mv.addEventListener('progress', function (e) {
        var pct = (e.detail.totalProgress * 100).toFixed(1) + '%';
        fill.style.width = pct;
        if (e.detail.totalProgress >= 1) {
          setTimeout(function () { fill.style.opacity = '0'; }, 400);
        }
      });
    })();
  </script>

  <!-- Conteúdo abaixo do viewer -->
  <section class="max-w-3xl mx-auto w-full px-4 py-8 flex flex-col gap-6">

    <!-- Título -->
    <h1 class="text-2xl font-semibold text-gray-800"><?php echo esc_html(get_the_title()) ?></h1>

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