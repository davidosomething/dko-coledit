/**
 * admin-edit.js
 * Loaded on edit.php in WordPress admin
 */
var DKOColEdit = (function ($) {

  /**
   * isEmpty
   * @param string $value
   * @return bool
   */
  function isEmpty(value) {
    return !$.trim(value).length;
  }


  /**
   * notifyElement
   * @param object $el jQuery object
   * @param string initialColor hex color with #
   * @param string finalColor
   * @return void
   */
  function notifyElement($el, initialColor, finalColor) {
    $el
      .animate( { backgroundColor: initialColor },
                250)
      .delay(1000)
      .animate( { backgroundColor: finalColor },
                500,
                function () {
                  $(this).css('backgroundColor', 'transparent');
                });
  }


  var $theList       = $('#the-list');
  var $tds           = $theList.find('.DKOColEdit_custom_fields');
  var $selects       = $tds.find('.DKOColEdit-select2');
  var $addKeySection = $tds.find('section.DKOColEdit-add-key-section');
  var activeKey      = '';
  var activePost     = '';

  var emptyValueSection = function($valueSection) {
    $valueSection.children().not('h6').remove();
  };

  var hideValueSection = function ($td) {
    $valueSection = $td.find('section.DKOColEdit-add-value-section').hide();
    emptyValueSection($valueSection);
  };

  var showValueSection = function ($td) {
    $valueSection = $td.find('section.DKOColEdit-add-value-section').show();
    emptyValueSection($valueSection);
  };

  var disableEnter = function (e) {
    if (e.keyCode == 13) { e.preventDefault(); }
  };
  $tds.on('keypress', 'input', disableEnter);


  $selects.select2({
    placeholder:         "key name or *",
    allowClear:          true,
    initSelection:       function (element, callback) {
                           callback([]);
                         },
    minimumInputLength:  1,
    query:               function (query) {
      var $input = $(this.element);
      var post_id = $input.closest('div.DKOColEdit-wrapper').data('dkocoledit-post-id');
      var data = {results: []};
      $.ajax({
        url:        ajaxurl,
        type:       'POST',
        dataType:   'json',
        async:      false,
        data:       {
                      action:   'DKOColEdit_get_post_custom_keys_starting_with',
                      q:        query.term,
                      post_id:  post_id
                    },
        success:    function (response) {
                      $.each(response, function (index, value) {
                        var field = {
                          id:   value,
                          text: value
                        };
                        data.results.push(field);
                      });
                    }
      });
      query.callback(data);
    }
  });

  $selects.on('change', function (e) {
    if (e.val) {
      getValueForEditing(this, e.val);
    }
    else {
      doneEditing(this);
    }
  });

  function getValueForEditing(input, key) {
    var $input        = $(input);
    var post_id       = $input.closest('div.DKOColEdit-wrapper').data('dkocoledit-post-id');
    var $td           = $input.closest('td.column-DKOColEdit_custom_fields');

    // toggle sections
    $td.find('section.DKOColEdit-add-key-section').hide();
    showValueSection($td);

    activeKey = key;
    activePost = post_id;

    $.ajax({
      url:        ajaxurl,
      type:       'POST',
      dataType:   'json',
      async:      false,
      data:       {
        action:   'DKOColEdit_get_post_custom_values',
        post_id:  post_id,
        key:      key
      },
      success:    function (data) {
        var valueFieldHtml = ich.DKOColEditFieldTemplate(data);
        valueFieldHtml.appendTo($td.find('section.DKOColEdit-add-value-section'));
      }
    });
  } // getValueForEditing ()

  function doneEditing(input) {
    var $input        = $(input);
    var $td           = $input.closest('td.column-DKOColEdit_custom_fields');
    hideValueSection($td);
    $td.find('a.DKOColEdit-add-key').show();
  }

  var deleteValue = function (e) {
    var $icon    = $(this);
    var $p       = $icon.closest('p');
    var $input   = $p.find('input.DKOColEdit-value-field-input');
    var value    = $input.val() || '';
    var really   = confirm("Really delete the field with\n\tkey: " + activeKey + "\n\tvalue: " + value + "\n\ton post: " + activePost + ' ?');
    if (really) {
      $.ajax({
        url:        ajaxurl,
        type:       'POST',
        dataType:   'json',
        async:      false,
        data:       {
          action:   'DKOColEdit_delete_custom_value',
          post_id:  activePost,
          key:      activeKey,
          value:    value
        },
        success:    function (data) {
          $p.fadeOut('fast', function () { $p.remove(); });
        }
      });
    }
  };
  $tds.on('click', 'a.DKOColEdit-delete-field', deleteValue);


  /**
   * Upsert a value
   */
  var updateValue = function (e) {
    var $input   = $(this);
    var $p       = $input.parent('p');
    var isNew    = $input.data('new') || '';
    var original = $input.data('original') || '';
    var value    = $input.val() || '';

    var ajaxData = {
      post_id:  activePost,
      key:      activeKey,
      value:    value,
      original: original
    };

    if (isNew) {
      ajaxData.action = 'DKOColEdit_insert_custom_value';
    }
    else {
      ajaxData.action = 'DKOColEdit_update_custom_value';
    }

    $.ajax({
      url:        ajaxurl,
      type:       'POST',
      dataType:   'json',
      async:      false,
      data:       ajaxData,
      success:    function (data) {
        notifyElement($p, '#aaffaa', '#ffffff');

        // not new anymore
        if (isNew) {
          $input.data('new', '');
          $input.data('original', value);
        }
      }
    });
  };
  $tds.on('blur', 'input.DKOColEdit-value-field-input', updateValue);


  /**
   * Add HTML for a new value
   */
  var addValueField = function (e) {
    var $link = $(this);
    var $fieldContainer = $link.parent().prev('div.DKOColEdit-value-fields');
    var valueFieldHtml = ich.DKOColAddFieldTemplate();
    valueFieldHtml.appendTo($fieldContainer);
  };
  $tds.on('click', 'a.DKOColEdit-add-field', addValueField);


  /**
   * Add HTML for a new key
   */
  var addKeyField = function (e) {
    var $link   = $(this);
    var $td     = $link.closest('td.DKOColEdit_custom_fields');
    $td.find('section.DKOColEdit-add-key-section').show();
    hideValueSection($td);
    $link.hide();
    $td.find('.DKOColEdit-select2').select2('val', '');
  };
  $tds.on('click', 'a.DKOColEdit-add-key', addKeyField);


  /**
   * Hide the add key section
   */
  var hideAddKeySection = function (e) {
    var $link   = $(this);
    var $td     = $link.closest('td.DKOColEdit_custom_fields');
    $td.find('section.DKOColEdit-add-key-section').hide();
    $td.find('a.DKOColEdit-add-key').show();
  };
  $addKeySection.on('click', 'a.DKOColEdit-add-key-cancel', hideAddKeySection);


  /**
   * Validate add key section and save new key/value pair
   */
  var addNewKey = function (e) {
    e.preventDefault();

    var $link   = $(this);
    var $td     = $link.closest('td.DKOColEdit_custom_fields');
    var $div    = $td.find('div.DKOColEdit-wrapper');
    var post_id = $div.data('dkocoledit-post-id');
    var $key    = $td.find('input.DKOColEdit-add-key--key-input');
    var $value  = $td.find('input.DKOColEdit-add-key--value-input');
    var keyValue   = $.trim($key.val());
    var valueValue = $.trim($value.val());
    var $keyContainer   = $key.closest('p');
    var $valueContainer = $value.closest('p');
    var isError = false;

    if (isEmpty(keyValue)) {
      notifyElement($keyContainer, '#ffaaaa', '#ffffff');
      isError = true;
    }
    if (isEmpty(valueValue)) {
      notifyElement($valueContainer, '#ffaaaa', '#ffffff');
      isError = true;
    }

    if (isError) {
      return;
    }
    else {
      var ajaxData = {
        post_id:  post_id,
        key:      keyValue,
        value:    valueValue,
        action:  'DKOColEdit_insert_custom_value'
      };

      $.ajax({
        url:        ajaxurl,
        type:       'POST',
        dataType:   'json',
        async:      false,
        data:       ajaxData,
        success:    function (data) {
          $key.val('');
          $value.val('');
          $td.find('section.DKOColEdit-add-key-section').hide();
          $td.find('a.DKOColEdit-add-key').show();
        }
      });
    }
  };
  $addKeySection.on('click', 'a.DKOColEdit-add-key-save', addNewKey);


})(jQuery);
