<?php

$has_saved = false;
$post_types = SMUI()->get_post_types();
$taxonomies = SMUI()->get_taxonomies();

if ( isset( $_POST['smui_nonce'] ) && wp_verify_nonce( $_POST['smui_nonce'], 'smui_nonce' ) ) {
    $settings = json_encode( $_POST['data'] );
    update_option( 'smui_settings', $settings );
    $has_saved = true;
}

$settings = get_option( 'smui_settings', [] );

?>

<link href="<?php echo SMUI_URL; ?>/assets/vendor/fSelect/fSelect.css" rel="stylesheet">
<link href="<?php echo SMUI_URL; ?>/assets/css/admin.css" rel="stylesheet">
<script src="<?php echo SMUI_URL; ?>/assets/vendor/fSelect/fSelect.js"></script>
<script src="<?php echo SMUI_URL; ?>/assets/js/admin.js"></script>
<script>
var SMUI = <?php echo $settings; ?>;
</script>


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
                <option value="<?php echo $pt; ?>"><?php echo $pt; ?></option>
                <?php endforeach; ?>
            </select>

            <p>Exclude specific posts:</p>
            <input type="text" name="data[post_ids]" class="exclude-ids exclude-post-ids" placeholder="Enter comma-separated post IDs" />
        </div>

        <div class="wrap-taxonomies">
            <h3>Exclude taxonomies</h3>
            <select class="exclude-multi exclude-taxonomies" name="data[taxonomies][]" multiple="multiple">
                <?php foreach ( $taxonomies as $tax ) : ?>
                <option value="<?php echo $tax; ?>"><?php echo $tax; ?></option>
                <?php endforeach; ?>
            </select>

            <p>Exclude specific terms:</p>
            <input type="text" name="data[term_ids]" class="exclude-ids exclude-term-ids" placeholder="Enter comma-separated term IDs" />
        </div>

        <p>
            <input type="submit" class="button-primary" value="Save changes" />
            <input type="hidden" name="smui_nonce" value="<?php echo wp_create_nonce( 'smui_nonce' ); ?>" />
        </p>
</div>
