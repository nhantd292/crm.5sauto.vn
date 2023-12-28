CKEDITOR.editorConfig = function( config ) {
	config.language 		= 'vi';					
	config.removePlugins 	= 'iframe';
	 
	config.toolbar_Basic	= [
	      { name: 'document'	, items : [ 'Preview'] },             	   
	      { name: 'basicstyles'	, items : [ 'Bold','Italic','Underline','Strike','-','RemoveFormat' ] },
	      { name: 'styles'		, items : [ 'FontSize' ] },
	      { name: 'colors'		, items : [ 'TextColor','BGColor' ] },
	      { name: 'tools'		, items : [ 'Maximize' ] }
	];
	 
	config.toolbar	= 'Basic';
};