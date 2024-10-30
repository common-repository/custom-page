// JavaScript Document
jQuery(document).ready(function ($){
	//Hide Body of Update Element Form on Init
	var action = get_url_parameter('action');

	$('tbody.edit-element').hide();
	
	if(action == 'add_element') {
		$('div.add-element').dialog({ autoOpen: true, width: 550, height: 600, position: ['right','bottom']});
	} else {
		$('div.add-element').dialog({ autoOpen: false, width: 550, height: 600, position: ['right','bottom']});
	}
	
	$('div.update-elements').dialog({ autoOpen: false, width: 550, height: 600, position: ['right','bottom']});
		
	$('a.select-element').click(function(){
		$('div.form-selected').removeClass('form-selected');
		$('div.update-elements').addClass('form-selected');
		
		$('tbody.current-action').removeClass('current-action');
		
		$('tbody.edit-element').hide();
		
		var selected = $(this).text();
		var element = '#element-' +  selected;

		$(element).fadeIn();
		$(element).addClass('current-action');
		
		$('div.add-element').dialog("close");
		$('div.update-elements').dialog("open");
		
		select_element(selected);
		refresh_element(selected);
		return false;
	});
	
	$('a.add-element').click(function(){
		$('div.form-selected').removeClass('form-selected');
		$('div.add-element').addClass('form-selected');
		
		$('tbody.current-action').removeClass('current-action');
		
		$('div.update-elements').dialog("close");
		$('div.add-element').dialog("open");
		
		$('div.select-elements').hide();
		
		return false;
	});
	
	$('a.edit-elements, div.select-elements').click(function(){
		$('div.select-elements').fadeIn();
	});
	
	$('a.edit-elements, a.element-action').click(function(){
		return false;
	});
	
	$('a.close-select-elements').click(function(){
		$('div.select-elements').hide();
		return false;
	});
	
	$('a.close-element-form').click(function(){
		$('div.form-selected').dialog("close");
		return false;
	});
	
	$('a.open-element-form').click(function(){
		$('div.form-selected').dialog("open");
		return false;
	});

	$('td.refresh a').click(function(){
		var selected = $(this).attr('class');
		refresh_element(selected);
		return false;
	});
	
	$('td.image_size a').click(function(){
		var selected = $(this).attr('class');
		var image = '';
		
		if(selected == 'new') {
			image = $('#add-new-element td.image input').val();
		} else {
			image = $('#element-' + selected + ' td.image input').val();
		}
		
		if(image){
			var size = img_size(image);
			
			if(size['width'] == 0 || size['height'] == 0){
				return false;
			}
			
			if(selected == 'new') {
				$('#add-new-element td.width input').val(size['width']);
				$('#add-new-element td.height input').val(size['height']);
			} else {
				$('#element-' + selected + ' td.width input').val(size['width']);
				$('#element-' + selected + ' td.height input').val(size['height']);
				refresh_element(selected);
			}
		}
		
		return false;
	});
	
	/* Set Input from Form */	
	$('div.update-elements input, div.update-elements select').change(function(){
		var selected = $(this).attr('class');
		refresh_element(selected);
	});
	
	$('div.update-elements textarea').change(function(){
		var class_name = $(this).attr('class');
		var selected = textarea_selected(class_name);
		$('#element-' + selected + ' td.text textarea').val($(this).val());
		refresh_element(selected);
	});
	
	$('a.reset-custom-page').click(function(){
		return confirm("Confirm Reset All Elements?");
	});

	function link_click(){
		return false;
	}	
	
	function get_url_parameter(sParam) {
		var sPageURL = window.location.search.substring(1);
		var sURLVariables = sPageURL.split('&');
		for (var i = 0; i < sURLVariables.length; i++) {
			var sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] == sParam) {
				return sParameterName[1];
			}
		}
	};
	
	function refresh_textarea(inst){
		var class_name = inst.getBody().className;
		
		selected = textarea_selected(class_name);
		
		$('#element-' + selected + ' td.text textarea').val(inst.getBody().innerHTML);
		refresh_element(selected);
	}
	
	function textarea_selected(class_selected){
		var class_selected = class_selected.split(' ');
		for (var i = 0; i < class_selected.length; i++) {
			selected = class_selected[i].split('-');
			if(selected[0] == 'textarea') {
				return selected[1];
			}
		}
	}
	
	function select_element(selected){
		$('div.setup-custom-page div.ui-resizable').resizable('destroy');
		$('div.setup-custom-page div.ui-draggable').draggable('destroy');
		
		$('div.selected-element a.element-link').removeAttr("onclick");
		$('div.selected-element').removeClass('selected-element');
		
		if(selected == 'background'){
			$('div.page-' + page_id + '-background').addClass('selected-element');
			
			$('div.page-' + page_id + '-background').resizable({
				stop: function(){set_size(selected);}
			});
		
			$('div.page-' + page_id + '-background').draggable({
				stop: function(){set_position(selected);}
			});
		} else {
			$('div.page-' + page_id + '-' + selected + '-element').addClass('selected-element');
			
			$('div.page-' + page_id + '-' + selected + '-element').resizable({
				stop: function(){set_size(selected);}
			});
		
			$('div.page-' + page_id + '-' + selected + '-element').draggable({
				stop: function(){set_position(selected);}
			});
		}
	};
	
	function img_size(image) {
		var load_image = new Image();
		var size = [];
		
		load_image.src = image; 
		
		size['height'] = load_image.height;
		size['width'] = load_image.width;
		
		return size;
	}
	
	function set_size(selected){
		var height = null;
		var width = null;
		
		if(selected == 'background') {
			height = $('div.page-' + page_id + '-background').height();
			width = $('div.page-' + page_id + '-background').width();	
		} else {
			height = $('div.page-' + page_id + '-' + selected + '-element').height();
			width = $('div.page-' + page_id + '-' + selected + '-element').width();
		}
		
		$('#element-' + selected + ' td.width input').val(parseInt(width));
		$('#element-' + selected + ' td.height input').val(parseInt(height));
		
		refresh_element(selected);
	}
	
	function set_position(selected){
		var position = $('div.page-' + page_id + '-' + selected + '-element').position();
		$('#element-' + selected + ' td.left input').val(parseInt(position.left));
		$('#element-' + selected + ' td.top input').val(parseInt(position.top));
		refresh_element(selected);
	}
	
	function refresh_element(selected){
		var get = selected.split(' ');
		var selected = get[0];
		
		var layer = $('#element-' + selected + ' td.layer input').val();
		var width = $('#element-' + selected + ' td.width input').val();
		var height = $('#element-' + selected + ' td.height input').val();
		var left = $('#element-' + selected + ' td.left input').val();
		var top = $('#element-' + selected + ' td.top input').val();
		var text = $('#element-' + selected + ' td.text textarea').val();
		var link = $('#element-' + selected + ' td.link input').val();
		var link_target = $('#element-' + selected + ' td.link-target select').val();
		var image = $('#element-' + selected + ' td.image input').val();
		var display = $('#element-' + selected + ' td.display input:checked').val();
		var display_none = $('#element-' + selected + ' td.display span.element-display-none input:checked').val();
		var padding_top = $('#element-' + selected + ' span.text-padding-top input').val();
		var padding_right = $('#element-' + selected + ' span.text-padding-right input').val();
		var padding_bottom = $('#element-' + selected + ' span.text-padding-bottom input').val();
		var padding_left = $('#element-' + selected + ' span.text-padding-left input').val();
		var remove = $('#element-' + selected + ' td.remove input:checked').val();
		
		if(isNaN(layer)){
			$('#element-' + selected + ' td.layer input').val(0);
			layer = 0;
		}
		
		if(width < 10 || isNaN(width)) {
			$('#element-' + selected + ' td.width input').val(10);
			width = 10;
		}
		
		if(height < 10 || isNaN(height)){
			$('#element-' + selected + ' td.height input').val(10);
			height = 10;
		}
		
		if(isNaN(left)){
			$('#element-' + selected + ' td.left input').val(0);
			left = 0;
		}
		
		if(isNaN(top)) {
			$('#element-' + selected + ' td.top input').val(0);
			top = 0;
		}
		
		if(!padding_right) {
			padding_right = 0;
		}
		if(!padding_left) {
			padding_left = 0;
	
		}
		if(!padding_top) {
			padding_top = 0;
		}
		if(!padding_bottom) {
			padding_bottom = 0;
		}
	
		if(!image){
			if(display == 'image' || display == 'both'){
				$('#element-' + selected + ' td.display span.element-display-text input').prop('checked', true);
				display = $('#element-' + selected + ' td.display input:checked').val();
			}
			$('#element-' + selected + ' td.display span.element-display-image input, #element-' + selected + ' td.display span.element-display-both input').prop('disabled', true);
		} else {
			$('#element-' + selected + ' td.display span.element-display-image input, #element-' + selected + ' td.display span.element-display-both input').prop('disabled', false);
		}
		
		if(!link){
			$('#element-' + selected + ' td.display span.element-display-link input').prop('disabled', true);
		} else {
			$('#element-' + selected + ' td.display span.element-display-link input').prop('disabled', false);
		}
		
		if(selected != 'background') {
			$('div.page-' + page_id + '-' + selected + '-element div.element-text, div.page-' + page_id + '-' + selected + '-element' + ' img.element-image , div.page-' + page_id + '-' + selected + '-element a.element-link').remove();
			
			var content = '';
			
			switch(display) {
				case 'text':
					content += '<div class="element-text page-' + page_id + '-' + selected + '-text">' + text + '</div>';
					break;
				case 'image':
					if(link){
						content += '<a onclick="return false;" class="element-link page-' + page_id + '-' + selected + '-link" href="' + link + '" target="' + link_target + '">';
					}
						content += '<img class="element-image page-' + page_id + '-' + selected + '-image" src="' + image + '" alt="' + selected + '-image" />';
					if(link){
						content += '</a>';
					}	
					break;
				case 'both':
					content += '<img class="element-image page-' + page_id + '-' + selected + '-image" src="' + image + '" alt="' + selected + '-image" />';
					content += '<div class="element-text page-' + page_id + '-' + selected + '-text">' + text + '</div>';
					break;
				case 'link':
					if(link){
						content += '<a onclick="return false;" class="element-link page-' + page_id + '-' + selected + '-link" href="' + link + '" target="' + link_target + '"></a>';
					}
					break;
			}
			
			$('div.page-' + page_id + '-' + selected + '-element').prepend(content);
			
			$('div.page-' + page_id + '-' + selected + '-element, div.page-' + page_id + '-' + selected + '-element img.element-image, div.page-' + page_id + '-' + selected + '-element a.element-link').width(width);
			$('div.page-' + page_id + '-' + selected + '-element, div.page-' + page_id + '-' + selected + '-element img.element-image, div.page-' + page_id + '-' + selected + '-element a.element-link').height(height);
			$('div.page-' + page_id + '-' + selected + '-element div.element-text').width(width - padding_right - padding_left);
			$('div.page-' + page_id + '-' + selected + '-element div.element-text').height(height - padding_top - padding_bottom);
			$('div.page-' + page_id + '-' + selected + '-element div.element-text').css('padding-top', padding_top);
			$('div.page-' + page_id + '-' + selected + '-element div.element-text').css('padding-bottom', padding_bottom);
			$('div.page-' + page_id + '-' + selected + '-element div.element-text').css('padding-left', padding_left);
			$('div.page-' + page_id + '-' + selected + '-element div.element-text').css('padding-right', padding_right);
			$('div.page-' + page_id + '-' + selected + '-element').css('left', left);
			$('div.page-' + page_id + '-' + selected + '-element').css('top', top);
			$('div.page-' + page_id + '-' + selected + '-element').css('z-index', layer);
			
			if(display_none || remove){
				$('div.page-' + page_id + '-' + selected + '-element').hide();
			} else {
				$('div.page-' + page_id + '-' + selected + '-element').show();
			}
		} else {
			// For Background
			$('div.page-' + page_id + '-background').draggable('disable');
			
			$('img.page-' + page_id + '-background-image, div.page-' + page_id + '-background-text' ).remove();
			
			var content = '';
			
			switch(display) {
				case 'text':
					content += '<div class="page-' + page_id + '-background-text">' + text + '</div>';
					break;
				case 'image':
					content += '<img class="page-' + page_id + '-background-image" src="' + image + '" alt="custom-background" />';
					break;
				case 'both':
					content += '<img class="page-' + page_id + '-background-image" src="' + image + '" alt="custom-background" />';
					content += '<div class="page-' + page_id + '-background-text">' + text + '</div>';
					break;
				case 'none':
					break;
			}
			
			$('div.page-' + page_id + '-background').prepend(content);
			
			$('div.page-' + page_id + '-background, img.page-' + page_id + '-background-image').width(width);
			$('div.page-' + page_id + '-background, img.page-' + page_id + '-background-image').height(height);
			$('div.page-' + page_id + '-background-text').width(width - padding_right - padding_left);
			$('div.page-' + page_id + '-background-text').height(height - padding_top - padding_bottom);
			$('div.page-' + page_id + '-background-text').css('padding-top', padding_top);
			$('div.page-' + page_id + '-background-text').css('padding-bottom', padding_bottom);
			$('div.page-' + page_id + '-background-text').css('padding-left', padding_left);
			$('div.page-' + page_id + '-background-text').css('padding-right', padding_right);
			$('div.page-' + page_id + '-background').css('z-index', layer);
			
			$('div.page-' + page_id + '-background').position({
				of: 'div.setup-custom-page',
				my: 'center top',
				at: 'center top'
			});
					
			if(display_none == 'on'){
				$('div.page-' + page_id + '-background-text').hide();
				$('img.page-' + page_id + '-background-image').hide();
			} else {
				$('div.page-' + page_id + '-background-text').show();
				$('img.page-' + page_id + '-background-image').show();
			}
		}
	}
});
