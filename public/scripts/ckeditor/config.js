CKEDITOR.editorConfig = function( config ) {
	config.language 		= 'vi';					
	config.removePlugins 	= 'iframe';
	config.extraPlugins 	= 'youtube,widget,lineutils,codesnippet,tableresize';
	 
	config.codeSnippet_theme = 'school_book';
	 
	 config.toolbar_Full	= [
	    { name: 'document'		, items : [ 'Source','-','Save','NewPage','Preview','Print','-','Templates' ] },
		{ name: 'clipboard'		, items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'editing'		, items : [ 'Find','Replace','-','SelectAll','-','Scayt' ] },
		{ name: 'forms'			, items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
		{ name: 'basicstyles'	, items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
		{ name: 'paragraph'		, items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv', '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
		{ name: 'links'			, items : [ 'Link','Unlink','Anchor' ] },
		{ name: 'insert'		, items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
		{ name: 'styles'		, items : [ 'Styles','Format','Font','FontSize' ] },
		{ name: 'colors'		, items : [ 'TextColor','BGColor' ] },
		{ name: 'tools'			, items : [ 'Maximize', 'ShowBlocks' ] }
	];
	 
	config.toolbar	= 'Full';
	 
	config.filebrowserBrowseUrl = '/public/scripts/kcfinder/browse.php?opener=ckeditor&type=files';
	config.filebrowserImageBrowseUrl = '/public/scripts/kcfinder/browse.php?opener=ckeditor&type=images';
	config.filebrowserFlashBrowseUrl = '/public/scripts/kcfinder/browse.php?opener=ckeditor&type=flash';
	config.filebrowserUploadUrl = '/public/scripts/kcfinder/upload.php?opener=ckeditor&type=files';
	config.filebrowserImageUploadUrl = '/public/scripts/kcfinder/upload.php?opener=ckeditor&type=images';
	config.filebrowserFlashUploadUrl = '/public/scripts/kcfinder/upload.php?opener=ckeditor&type=flash';
};