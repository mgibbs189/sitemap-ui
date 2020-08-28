<?php

$has_saved = false;
$post_types = SMUI()->get_post_types();
$taxonomies = SMUI()->get_taxonomies();

if ( SMUI()->is_valid_nonce() ) {
    SMUI()->save_settings();
    $has_saved = true;
}

?>

<div class="wrap">
    <h3>Sitemap UI <?php echo SMUI_VERSION; ?></h3>

    <?php if ( $has_saved ) : ?>
    <div class="notice notice-success is-dismissible">
        <p>Settings saved.</p>
    </div>
    <?php endif; ?>

    <form method="post" action="">
        <div><input type="checkbox" class="global-opt opt-all" name="data[objects][all]" /> Turn off sitemaps</div>
        <div><input type="checkbox" class="global-opt opt-post-types" name="data[objects][post_types]" /> Exclude all post types</div>
        <div><input type="checkbox" class="global-opt opt-taxonomies" name="data[objects][taxonomies]" /> Exclude all taxonomies</div>
        <div><input type="checkbox" class="global-opt opt-users" name="data[objects][users]" /> Exclude all users</div>

        <div class="wrap-post-types">
            <h3>Exclude post types</h3>
            <select class="exclude-multi exclude-post-types" name="data[post_types][]" multiple="multiple">
                <?php foreach ( $post_types as $pt ) : ?>
                <option value="<?php echo esc_attr( $pt ); ?>"><?php echo esc_html( $pt ); ?></option>
                <?php endforeach; ?>
            </select>

            <p>Exclude specific post IDs:</p>
            <input type="text" name="data[post_ids]" class="exclude-ids exclude-post-ids" placeholder="Enter comma-separated post IDs" />
        </div>

        <div class="wrap-taxonomies">
            <h3>Exclude taxonomies</h3>
            <select class="exclude-multi exclude-taxonomies" name="data[taxonomies][]" multiple="multiple">
                <?php foreach ( $taxonomies as $tax ) : ?>
                <option value="<?php echo esc_attr( $tax ); ?>"><?php echo esc_html( $tax ); ?></option>
                <?php endforeach; ?>
            </select>

            <p>Exclude specific term IDs:</p>
            <input type="text" name="data[term_ids]" class="exclude-ids exclude-term-ids" placeholder="Enter comma-separated term IDs" />
        </div>

        <p>
            <input type="submit" class="button-primary" value="Save changes" />
            <input type="hidden" name="smui_nonce" value="<?php echo wp_create_nonce( 'smui_nonce' ); ?>" />
        </p>
</div>
