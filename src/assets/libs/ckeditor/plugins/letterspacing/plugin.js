CKEDITOR.plugins.add( 'letterspacing', {
  requires: ['richcombo'],
  init: function( editor ) {
    var config = editor.config,
      lang = editor.lang.format;
    var trackings = [];

    config.allowedContent = 'span'; //There may be a better way to do this.

	trackings.push(1 + 'px');
	trackings.push(1.1 + 'px');
	trackings.push(1.2 + 'px');
	trackings.push(1.3 + 'px');
	trackings.push(1.4 + 'px');
	trackings.push(1.5 + 'px');
	trackings.push(1.6 + 'px');
	trackings.push(1.7 + 'px');
	trackings.push(1.8 + 'px');
	trackings.push(1.9 + 'px');
	trackings.push(2 + 'px');

    editor.ui.addRichCombo('letterspacing', {
      label: 'Betűköz',
      title: 'Betűköz megváltoztatás',
      voiceLabel: 'Change letter-spacing',
      className: 'cke_format',
      multiSelect: false,

      panel: {
      css : [ config.contentsCss, CKEDITOR.getUrl( CKEDITOR.skin.getPath('editor') + 'editor.css' ) ]
      },

      init: function() {
      this.startGroup('letterspacing');
      for (var this_letting in trackings) {
        this.add(trackings[this_letting], trackings[this_letting], trackings[this_letting]);
      }
      },

      onClick: function(value) {
      editor.focus();
      editor.fire('saveSnapshot');
      var ep = editor.elementPath();
      var style = new CKEDITOR.style({styles: {'letter-spacing': value}});
      editor[style.checkActive(ep) ? 'removeStyle' : 'applyStyle' ](style);

      editor.fire('saveSnapshot');
      }
    });
  }
});
