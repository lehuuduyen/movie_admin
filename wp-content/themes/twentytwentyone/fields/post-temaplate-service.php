<?php
add_action('cmb2_init', 'postTemplateService');
function postTemplateService() 
{
    $metaBox = new_cmb2_box([
        'id' => KEY_TEMPLATE_SERVICE . '_box',
        'title' => 'Video',
        'object_types' => POST_TYPE,
        'context' => 'normal',
        'priority' => 'default',
        'show_names' => true,
        
    ]);
    $metaBox->add_field( array(
        'name' => 'Link video',
        'id' => KEY_TEMPLATE_SERVICE . '_link',
        'type' => 'text',
        'attributes' => array(
            'data-cmb2-qtranslate' => true,
            'class' => 'form-control'
        )
    ));
    $metaBox->add_field( array(
        'name'    => 'File',
        // 'desc'    => 'Upload an image or enter an URL.',
        'id'      => KEY_TEMPLATE_SERVICE . '_file',
        'type'    => 'file',
        // Optional:
        'options' => array(
            'url' => false, // Hide the text input for the url
        ),
        'text'    => array(
            'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
        ),
        // query_args are passed to wp.media's library query.
        'query_args' => array(
            // 'type' => 'application/pdf', // Make library only display PDFs.
            // Or only allow gif, jpg, or png images
            'type' => array(
                'image/gif',
                'image/jpeg',
                'image/png',
                'playlist.m3u8'
            ),
        ),
        'preview_size' => 'large', // Image size to use when previewing in the admin.
    ) );
    
}

