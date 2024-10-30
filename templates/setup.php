<?php 
global $error_cp_msg;
$post_id = $post->ID;

$meta = get_post_meta($post_id, 'custom_page');
//check background existed
$background = false;

if(!empty($meta)) {
	foreach( $meta as $val ) {
		if(is_array($val) && array_key_exists("background", $val) ) {
			$background = true;
			break;
		}	
	}
}

//create a default background canvas if id no exist
if( $background == false ){
	CustomPage::set_default_page($post_id);
	$meta = get_post_meta($post_id, 'custom_page');
}
?>
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php echo __('Custom Page - Edit Page', 'cp');?></title>
<script type="text/javascript">
var page_id = <?php echo $post_id;?>
</script>
<?php wp_head(); ?>
</head>
<body>
<div class="setup-custom-page">
<?php render_custom_page(); ?>
</div>
<div class="add-element" style="display:none;">
	<form action="<?php echo wp_nonce_url(add_query_arg( array('setup_custom' => $post_id, 'custom_page_action' => 'add_element'), get_permalink( $post_id )));?>" method="post">
    <table>
    <thead>
    <tr class="element">
    	<th colspan="2"><h3><?php echo __('Add New Element', 'cp'); ?><span class="id_error"><?php echo $error_cp_msg['id'];?></span></h3></th>
    </tr>
    </thead>
    <tbody id="add-new-element">
    <input type="hidden" name="custom_page_action" value="add_element" />
    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
    <tr class="element">
        <td class="id"><label><?php echo __('ID', 'cp'); ?></label><input class="new" type="text" name="element_id" value="<?php echo $_POST['element_id']; ?>" /></td>
        <td class="layer"><label><?php echo __('Layer', 'cp'); ?></label><input class="new" type="text" name="element_layer" value="<?php echo $_POST['element_layer']; ?>" /></td>
    </tr>
    <tr class="element">
        <td class="width"><label><?php echo __('Width', 'cp'); ?></label><input class="new" type="text" name="element_width" value="<?php echo $_POST['element_width']; ?>" /></td>
        <td class="height"><label><?php echo __('Height', 'cp'); ?></label><input class="new" type="text" name="element_height" value="<?php echo $_POST['element_height']; ?>" /></td>
    </tr>
    <tr class="element">
        <td class="left"><label><?php echo __('Left', 'cp'); ?></label><input class="new" type="text" name="element_left" value="<?php echo $_POST['element_left']; ?>" /></td>
        <td class="top"><label><?php echo __('Top', 'cp'); ?></label><input class="new" type="text" name="element_top" value="<?php echo $_POST['element_top']; ?>" /></td>
    </tr>
    <tr class="element">
        <td class="link"><label><?php echo __('Link', 'cp'); ?></label><input class="new" type="text" name="element_link" value="<?php echo $_POST['element_link']; ?>" /></td>
        <td class="link-target"><label><?php echo __('Link Target', 'cp'); ?></label><select class="new" name="element_link_target">
                <option></option>
                <option value="_blank" <?php echo ($_POST['element_target'] == '_blank')? 'selected="selected"': ''; ?> >_blank</option>
                <option value="_self" <?php echo ($_POST['element_target'] == '_self')? 'selected="selected"': ''; ?> >_self</option>
                <option value="_parent" <?php echo ($_POST['element_target'] == '_parent')? 'selected="selected"': ''; ?>>_parent</option>
                <option value="_top" <?php echo ($_POST['element_target'] == '_top')? 'selected="selected"': ''; ?>>_top</option>
        	</select>
        </td>
    </tr>
    <tr class="element">
        <td class="image"><label><?php echo __('Image', 'cp'); ?></label><input class="new" type="text" name="element_image" value="<?php echo $_POST['element_image']; ?>" /></td>
        <td class="image_size"><a class="new" href="javascript:void(0)"><?php echo __('Set Width and Height as Image Size', 'cp');?></a></td>
    </tr>
    <tr class="element-editor">
        <td colspan="2" class="text">
        <?php wp_editor( (isset($_POST['element_text']))? stripslashes($_POST['element_text']): '', 'new-element-text', array('wpautop'=> false, 'textarea_name' => 'element_text', 'textarea_rows' => 10, 'editor_class' => 'element-text')); ?>
        </td>
    </tr>
    <tr class="element">
        <td colspan="2" class="text-padding"><label><?php echo __('Text Padding', 'cp'); ?></label>
		<span class="text-padding text-padding-top"><?php echo __('Top', 'cp'); ?>&nbsp;<input class="new" type="text" size="1" name="element_text_padding[top]" value="<?php echo $_POST['element_text_padding']['top']; ?>" /></span>
        <span class="text-padding text-padding-right"><?php echo __('Right', 'cp'); ?>&nbsp;<input class="new" type="text" size="1" name="element_text_padding[right]" value="<?php echo $_POST['element_text_padding']['right']; ?>" /></span>
        <span class="text-padding text-padding-bottom"><?php echo __('Bottom', 'cp'); ?>&nbsp;<input class="new" type="text" size="1" name="element_text_padding[bottom]" value="<?php echo $_POST['element_text_padding']['bottom']; ?>" /></span>
        <span class="text-padding text-padding-left"><?php echo __('Left', 'cp'); ?>&nbsp;<input class="new" type="text" size="1" name="element_text_padding[left]" value="<?php echo $_POST['element_text_padding']['left']; ?>" /></span>
        </td>
    </tr>
    <tr class="element">
        <td colspan="2" class="display">
        <label><?php echo __('Display', 'cp'); ?></label>
        <span class="element-display element-display-text"><input class="new" type="radio" name="element_display" value="text" <?php echo($_POST['element_display'] == 'text')? 'checked="checked"': ''; ?> />&nbsp;<?php echo __('Text/HTML', 'cp'); ?></span>
        <span class="element-display element-display-image"><input class="new" type="radio" name="element_display" value="image" <?php echo($_POST['element_display'] == 'image')? 'checked="checked"': ''; ?> />&nbsp;<?php echo __('Image', 'cp'); ?></span>
        <span class="element-display element-display-both"><input class="new" type="radio" name="element_display" value="both" <?php echo($_POST['element_display'] == 'both')? 'checked="checked"': ''; ?> />&nbsp;<?php echo __('Both', 'cp'); ?></span>
        <span class="element-display element-display-link"><input class="new" type="radio" name="element_display" value="link" <?php echo($_POST['element_display'] == 'link')? 'checked="checked"': ''; ?> />&nbsp;<?php echo __('Link Block', 'cp'); ?></span>
        <span class="element-display element-display-none"><input class="<?php echo $id; ?>" type="checkbox" name="element_display_none" />&nbsp;<?php echo __('Display None', 'cp'); ?></span>
        </td>
    </tr>
    </tbody>
    <tfoot>
    <tr class="element">
        <td colspan="2" class="submit"><input type="submit" value="<?php echo __('Create New Element', 'cp');?>" /></td>
    </tr>
    </tfoot>
    </table>
    </form>
</div><!-- div class add_element!-->

<div class="update-elements" style="display:none;">
    <form action="<?php echo wp_nonce_url(add_query_arg( array('setup_custom' => $post_id, 'custom_page_action' => 'update_elements'), get_permalink( $post_id )));?>" method="post">
    <table>
    <input type="hidden" name="custom_page_action" value="update_elements" />
    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
        <thead>
        <tr class="element">
            <th colspan="2"><h3><?php echo __('Edit Elements', 'cp'); ?></h3></th>
        </tr>
        </thead>
    <?php foreach($meta[0] as $id => $val):?>
        <tbody id="element-<?php echo $id;?>" class="edit-element">
        <tr class="element">
            <td class="id"><label><?php echo __('ID', 'cp'); ?></label><input class="<?php echo $id; ?>" type="text" name="update[<?php echo $id;?>][element_id]" value="<?php echo $id; ?>" readonly="readonly"/></td>
            <td class="layer"><label><?php echo __('Layer', 'cp'); ?></label><input class="<?php echo $id; ?>" type="text" name="update[<?php echo $id;?>][element_layer]" value="<?php echo $val['element_layer']; ?>" /></td>
        </tr>
        <tr class="element">
            <td class="width"><label><?php echo __('Width', 'cp'); ?></label><input class="<?php echo $id; ?>" type="text" name="update[<?php echo $id;?>][element_width]" value="<?php echo $val['element_width']; ?>" /></td>
            <td class="height"><label><?php echo __('Height', 'cp'); ?></label><input class="<?php echo $id; ?>" type="text" name="update[<?php echo $id;?>][element_height]" value="<?php echo $val['element_height']; ?>" /></td>
        </tr>
        <tr class="element">
            <td class="left"><label><?php echo __('Left', 'cp'); ?></label><input class="<?php echo $id; ?>" type="text" name="update[<?php echo $id;?>][element_left]" <?php echo ($id == 'background')? 'disabled="disabled"': '';?> value="<?php echo $val['element_left']; ?>" /></td>
            <td class="top"><label><?php echo __('Top', 'cp'); ?></label><input class="<?php echo $id; ?>" type="text" name="update[<?php echo $id;?>][element_top]" <?php echo ($id == 'background')? 'disabled="disabled"': '';?> value="<?php echo $val['element_top']; ?>" /></td>
        </tr>
        <tr class="element">
            <td class="link"><label><?php echo __('Link', 'cp'); ?></label><input class="<?php echo $id; ?>" type="text" name="update[<?php echo $id;?>][element_link]" <?php echo ($id == 'background')? 'disabled="disabled"': '';?> value="<?php echo $val['element_link']; ?>" /></td>
            <td class="link-target"><label><?php echo __('Link Target', 'cp'); ?></label><select class="<?php echo $id; ?>" name="update[<?php echo $id;?>][element_link_target]" <?php echo ($id == 'background')? 'disabled="disabled"': '';?>>
                    <option></option>
                    <option value="_blank" <?php echo($val['element_link_target'] == '_blank')? 'selected="selected"': ''; ?> >_blank</option>
                    <option value="_self" <?php echo($val['element_link_target'] == '_self')? 'selected="selected"': ''; ?> >_self</option>
                    <option value="_parent" <?php echo($val['element_link_target'] == '_parent')? 'selected="selected"': ''; ?>>_parent</option>
                    <option value="_top" <?php echo($val['element_link_target'] == '_top')? 'selected="selected"': ''; ?>>_top</option>
                </select>
            </td>
        </tr>
        <tr class="element">
            <td class="image"><label><?php echo __('Image', 'cp'); ?></label><input class="<?php echo $id; ?>" type="text" name="update[<?php echo $id;?>][element_image]" value="<?php echo $val['element_image']; ?>" /></td>
            <td class="image_size"><a class="<?php echo $id; ?>" href="javascript:void(0)"><?php echo __('Set Width and Height as Image Size', 'cp');?></a></td>
        </tr>
        <tr class="element">
            <td colspan="2" class="text">
            <?php wp_editor( $val['element_text'], 'textarea-'.$id, array('wpautop'=> false, 'textarea_name' => 'update['.$id.'][element_text]', 'textarea_rows' => 10, 'editor_class' => 'textarea-'.$id, 'tinymce' => array('onchange_callback' => 'refresh_textarea'))); ?>
            </td>
        </tr>
        <tr class="element">
            <td colspan="2" class="text-padding"><label><?php echo __('Text Padding', 'cp'); ?></label>
            <span class="text-padding text-padding-top"><?php echo __('Top', 'cp'); ?>&nbsp;<input class="<?php echo $id; ?>" type="text" size="1" name="update[<?php echo $id;?>][element_text_padding][top]" value="<?php echo $val['element_text_padding']['top']; ?>" /></span>
            <span class="text-padding text-padding-right"><?php echo __('Right', 'cp'); ?>&nbsp;<input class="<?php echo $id; ?>" type="text" size="1" name="update[<?php echo $id;?>][element_text_padding][right]" value="<?php echo $val['element_text_padding']['right']; ?>" /></span>
            <span class="text-padding text-padding-bottom"><?php echo __('Bottom', 'cp'); ?>&nbsp;<input class="<?php echo $id; ?>" type="text" size="1" name="update[<?php echo $id;?>][element_text_padding][bottom]" value="<?php echo $val['element_text_padding']['bottom']; ?>" /></span>
            <span class="text-padding text-padding-left"><?php echo __('Left', 'cp'); ?>&nbsp;<input class="<?php echo $id; ?>" type="text" size="1" name="update[<?php echo $id;?>][element_text_padding][left]" value="<?php echo $val['element_text_padding']['left']; ?>" /></span>
            </td>
        </tr>
        <tr class="element">
            <td colspan="2" class="display">
            <label><?php echo __('Display', 'cp'); ?></label>
            <span class="element-display element-display-text"><input class="<?php echo $id; ?>" type="radio" name="update[<?php echo $id;?>][element_display]" value="text" <?php echo($val['element_display'] == 'text')? 'checked="checked"': ''; ?> />&nbsp;<?php echo __('Text/HTML', 'cp'); ?></span>
            <span class="element-display element-display-image"><input class="<?php echo $id; ?>" type="radio" name="update[<?php echo $id;?>][element_display]" value="image" <?php echo($val['element_display'] == 'image')? 'checked="checked"': ''; ?> />&nbsp;<?php echo __('Image', 'cp'); ?></span>
            <span class="element-display element-display-both"><input class="<?php echo $id; ?>" type="radio" name="update[<?php echo $id;?>][element_display]" value="both" <?php echo($val['element_display'] == 'both')? 'checked="checked"': ''; ?> />&nbsp;<?php echo __('Both', 'cp'); ?></span>
            <span class="element-display element-display-link"><input class="<?php echo $id; ?>" type="radio" name="update[<?php echo $id;?>][element_display]" value="link" <?php echo($val['element_display'] == 'link')? 'checked="checked"': ''; ?> <?php echo ($id == 'background')? 'disabled="disabled"': '';?> />&nbsp;<?php echo __('Link Block', 'cp'); ?></span>
            <span class="element-display element-display-none"><input class="<?php echo $id; ?>" type="checkbox" name="update[<?php echo $id;?>][element_display_none]" <?php echo($val['element_display_none'] == true)? 'checked="checked"': ''; ?> />&nbsp;<?php echo __('Display None', 'cp'); ?></span>
            </td>
        </tr>
        <tr class="element">
            <?php if($id != 'background'):?>
            <td class="remove"><input class="<?php echo $id; ?>" type="checkbox" name="update[<?php echo $id;?>][element_remove]" /><?php echo __('Remove Element', 'cp'); ?></td>
            <?php else: ?>
            <td class="remove"><?php echo __('*Background Element is Required', 'cp'); ?></td>
            <?php endif;?>
            <td class="refresh"><a class="<?php echo $id; ?>" href="javascript:void(0)"><?php echo __('Refresh', 'cp');?></a></td>
        </tr>
        </tbody>
    <?php endforeach; ?>
        <tfoot>
        <tr class="element">
            <td colspan="2" class="submit"><input type="submit" value="<?php echo __('Update All Elements', 'cp');?>" /></td>
        </tr>
        </tfoot>
    </table>
    </form>
</div>

<div class="select-elements" style="display:none;">
<a href="javascript:void(0)" class="element-action close-select-elements"><?php echo __('Close','cp');?></a>
<?php foreach($meta[0] as $id => $val): ?>
<?php echo '<a href="javascript:void(0)" class="element-action select-element '.$id.'">'.$id.'</a>'; ?>
<?php endforeach; ?>
</div>
<div class="select-actions">
<a href="javascript:void(0)" class="element-action edit-elements"><?php echo __('Edit Elements', 'cp');?></a><a href="javascript:void(0)" class="element-action add-element"><?php echo __('Create New Element', 'cp');?></a><a href="<?php echo wp_nonce_url(add_query_arg( array('setup_custom' => $post_id, 'custom_page_action' => 'reset_custom_page'), get_permalink( $post_id ))); ?>" class="reset-custom-page"><?php echo __('Reset Custom Page', 'cp');?></a><a href="javascript:void(0)" class="element-action close-element-form"><?php echo __('Close', 'cp');?></a><a href="javascript:void(0)" class="element-action open-element-form"><?php echo __('Open', 'cp');?></a>
</div>
<?php wp_footer(); ?>
</body>
</html>
