(function($) {

  /**
   * Adds a CKEditor plugin to insert <pre> and <code> tags.
   *
   * Based on blog posts by:
   *
   * Nikolay Ulyanitsky
   * http://blog.lystor.org.ua/2010/11/ckeditor-plugin-and-toolbar-button-for.html
   *
   * and
   *
   * Peter Petrik
   * http://peterpetrik.com/blog/ckeditor-and-geshi-filter
   */
  CKEDITOR.plugins.add('code-button', {
    init: function (editor) {
      var buttons = {
        'code-button-pre': ['pre', 'PRE'],
        'code-button-code': ['code', 'CODE']
      };
      for (var buttonName in buttons) {
        var format = {'element': buttons[buttonName][0]};
        var style = new CKEDITOR.style(format);

        // Allow the button's state to be toggled.
        // @see http://drupal.org/node/1025626 for a standardized solution to
        //   the closure context late binding problem.
        (function(buttonName, style) {
          editor.attachStyleStateChange(style, function (state) {
            editor.getCommand(buttonName).setState(state);
          });
        })(buttonName, style);

        // Add the command and button to the editor.
        editor.addCommand(buttonName, new CKEDITOR.styleCommand(style));
        editor.ui.addButton(buttonName, {command: buttonName, label: buttons[buttonName][1]});
      }
    }
  });

})(jQuery);
