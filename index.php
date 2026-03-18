<?php get_header(); ?>

<main class="min-h-screen bg-gray-100">
  <div class="max-w-5xl mx-auto px-4 py-10">

    <!-- Cabeçalho da galeria -->
    <div class="mb-8">
      <h1 class="text-2xl font-semibold text-gray-800">Modelos 3D</h1>
    </div>

    <!-- Grid de itens -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
      <?php
      $args = [
        'post_type'      => 'objetora',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
      ];
      $query = new WP_Query($args);
      if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>

        <a href="<?php the_permalink() ?>"
           class="group bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
          <?php if (has_post_thumbnail()) : ?>
            <div class="aspect-square overflow-hidden bg-gray-50">
              <?php the_post_thumbnail('medium', ['class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300']); ?>
            </div>
          <?php else : ?>
            <div class="aspect-square bg-gray-100 flex items-center justify-center">
              <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M21 7.5l-9-5.25L3 7.5m18 0v9l-9 5.25M3 7.5v9l9 5.25M3 7.5l9 5.25 9-5.25" />
              </svg>
            </div>
          <?php endif; ?>
          <div class="px-3 py-2.5">
            <h2 class="text-sm font-medium text-gray-700 truncate"><?php the_title(); ?></h2>
            <?php
              $glb_id = carbon_get_post_meta(get_the_ID(), 'glb_file');
              $glb_name = $glb_id ? basename(get_attached_file($glb_id)) : null;
            ?>
            <?php if ($glb_name) : ?>
              <p class="text-xs text-gray-400 truncate mt-0.5"><?php echo esc_html($glb_name); ?></p>
            <?php endif; ?>
          </div>
        </a>

      <?php endwhile; else : ?>
        <p class="col-span-full text-center text-gray-400 text-sm py-20">Nenhum modelo encontrado.</p>
      <?php endif; wp_reset_postdata(); ?>
    </div>

  </div>
</main>

<?php get_footer(); ?>
