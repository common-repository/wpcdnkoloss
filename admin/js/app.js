/* Setup variables */
var wpcdnkoloss = new Object();
wpcdnkoloss.active_library = null;
wpcdnkoloss.filetypes = new Array('js','css');
wpcdnkoloss.included_files = files;

/* Handle admin tasks via js */
jQuery(document).ready(function($) {
  /* Handle cdn search */
  $('[data-wpcdnkoloss-field="cdn-search-string"]').keyup(function(e) {
    /* Check for valid value */
    if ($(this).val().length > 0) {
      $('form[data-wpcdnkoloss-context="search-cdn"]').find('button').first().removeAttr('disabled', 'disabled');
    } else {
      $('form[data-wpcdnkoloss-context="search-cdn"]').find('button').first().attr('disabled', 'disabled');
    }
  });

  /* Handle cdn search */
  $('form[data-wpcdnkoloss-context="search-cdn"]').on('submit', function(e) {
    /* Stop default */
    e.preventDefault();

    /* Set form var */
    var activeForm = $(this);

    /* Disable field and button */
    $(activeForm).find('[data-wpcdnkoloss-field="cdn-search-string"]').first().attr('disabled', 'disabled');
    $(activeForm).find('button').first().attr('disabled', 'disabled');

    /* Enable spinner */
    $(activeForm).find('.spinner').first().addClass('is-active');

    /* Empty search detail */
    $('[data-wpcdnkoloss-context="search-result-detail"]').html('');

    /* Setup data */
    var data = {
      action: 'searchCdnAjax',
      security: $(this).find('[name="_wpnonce"]').first().attr('value'),
      search_string: $(activeForm).find('[data-wpcdnkoloss-field="cdn-search-string"]').first().attr('value')
    };

    /* Hide and clean search result */
    $('.wpcdnkoloss-search-result').removeClass('has-result');
    $('[data-wpcdnkoloss-context="search-result"]').addClass('hidden');
    $('.wpcdnkoloss-search-result ul li').remove();

    /* Make ajax call to find matching libraries */
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response) {
        /* Populate result list */
        if (response.total > 0) {
          /* Walk result */
          $.each(response.results, function(resultIndex, resultElem) {
            /* Create li */
            if ($('[data-wpcdnkoloss-template="search-cdn-result-li"]').length > 0) {
              /* Clone */
              newLi = $('[data-wpcdnkoloss-template="search-cdn-result-li"]').first().clone();
              $(newLi).removeAttr('data-wpcdnkoloss-template');
              $(newLi).find('[data-wpcdnkoloss-placeholder="name"]').html(resultElem.name);
              $(newLi).find('[data-wpcdnkoloss-context="show-details"]').attr('data-wpcdnkoloss-library', resultElem.name);
              $(newLi).find('[data-wpcdnkoloss-placeholder="description"]').html(resultElem.description);

              /* Add to result list */
              $('[data-wpcdnkoloss-context="search-result-list"] ul').append(newLi);
            }
          });

          /* Set search string */
          $('[data-wpcdnkoloss-context="search-result"]').find('[data-wpcdnkoloss-placeholder="search_string"]').html(data.search_string);
          $('[data-wpcdnkoloss-context="search-result"]').find('[data-wpcdnkoloss-placeholder="total_results"]').html(response.total);

          /* Show search result */
          $('.wpcdnkoloss-search-result').addClass('has-result');
        } else {
          /* Show search result */
          $('.wpcdnkoloss-search-result').addClass('no-result');
        }

        /* Show search result block */
        $('[data-wpcdnkoloss-context="search-result"]').removeClass('hidden');
      },
      error: function(response) {
        console.log(response);
      },
      complete: function(response) {
        /* Enable search field and button again */
        $(activeForm).find('[data-wpcdnkoloss-field="cdn-search-string"]').first().removeAttr('disabled');
        $(activeForm).find('button').first().removeAttr('disabled');
        $(activeForm).find('.spinner').first().removeClass('is-active');
      },
      dataType: 'json'
    });
  });

  /* Handle library show details */
  $('body').on('click touchend', 'a[data-wpcdnkoloss-context="show-details"]', function(e) {
    /* Stop default */
    e.preventDefault();

    /* Clicked element */
    var clickedResult = $(this);

    /* De-/-Activate */
    $('[data-wpcdnkoloss-context="search-result-list"] ul li').removeClass('active');
    $(clickedResult).closest('li').addClass('active');

    /* Empty search detail */
    $('[data-wpcdnkoloss-context="search-result-detail"]').html('');
    $('[data-wpcdnkoloss-context="search-result-list"]').addClass('is-loading');

    /* Add active spinner */
    $(clickedResult).find('.spinner').addClass('is-active');

    /* Get library name */
    var library = $(clickedResult).attr('data-wpcdnkoloss-library');

    /* Setup data */
    var data = {
      action: 'getLibraryDetailAjax',
      security: $('div[data-wpcdnkoloss-context="search-result-detail"]').attr('data-wpcdnkoloss-nonce'),
      search_string: library
    };

    /* Get detail information about library */
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response) {
        /* Empty and set active library */
        wpcdnkoloss.active_library = null;
        wpcdnkoloss.active_library = response;
        wpcdnkoloss.active_library.version_assets = new Object();

        /* Create detail view */
        if ($('[data-wpcdnkoloss-template="search-cdn-result-detail-item"]').length > 0) {
          /* Clone */
          newDetailView = $('[data-wpcdnkoloss-template="search-cdn-result-detail-item"]').first().clone();
          $(newDetailView).removeAttr('data-wpcdnkoloss-template');
          $(newDetailView).find('[data-wpcdnkoloss-placeholder="name"]').html(data.search_string);
          $(newDetailView).find('[data-wpcdnkoloss-placeholder="description"]').html(response.description);
          $(newDetailView).find('[data-wpcdnkoloss-placeholder="homepage"] a').attr('href', response.homepage);

          /* Walk assets */
          if (response.assets.length > 0) {
            /* Add each version to version selector */
            if ($(newDetailView).find('[data-wpcdnkoloss-context="choose-library-version"]').length > 0) {
              /* Walk assets and get versions */
              $.each(response.assets, function(assetIndex, assetElem) {
                /* Set option elements */
                $(newDetailView).find('[data-wpcdnkoloss-context="choose-library-version"] select').append('<option value="'+assetElem.version+'">'+assetElem.version+'</otpion>');

                /* Set version_assets element */
                wpcdnkoloss.active_library.version_assets[assetElem.version] = assetElem;
              });
            }

            /* Add asset list to asset list */
            if (response.assets['0'].files.length > 0) {
              populateAssetList(response.assets['0'].files, newDetailView);
            }
          }

          /* Show detail */
          $('[data-wpcdnkoloss-context="search-result-detail"]').append(newDetailView);
        }

      },
      error: function(response) {
        console.log('error',response);
      },
      complete: function(response) {
        /* Hide active spinner */
        $(clickedResult).find('.spinner').removeClass('is-active');
        $('[data-wpcdnkoloss-context="search-result-list"]').removeClass('is-loading');
      },
      dataType: 'json'
    });
  });

  /* Handle library version choose */
  $('body').on('change', '[data-wpcdnkoloss-context="choose-library-version"] select', function(e) {
    /* Stop default */
    e.preventDefault();

    /* Show loading state */
    $('[data-wpcdnkoloss-context="assets-list"]').addClass('is-loading');

    /* Check for existing version assets */
    if (wpcdnkoloss.active_library.version_assets[$(this).val()]) {
      /* Remove existing files list */
      $('[data-wpcdnkoloss-context="assets-list"] ul li').remove();

      /* Populate list */
      populateAssetList(wpcdnkoloss.active_library.version_assets[$(this).val()].files, $('.search-cdn-result-detail-item'));
    }
  });

  /* Handle library version choose */
  $('body').on('change', '[data-wpcdnkoloss-context="assets-list"] [data-wpcdnkoloss-context="include-in"]', function(e) {
    /* Stop default */
    e.preventDefault();

    /* Check for existing version assets */
    if ($(this).val() || $(this).closest('.item-wrapper').find('[data-wpcdnkoloss-context="include-file"]').attr('data-wpcdnkoloss-file-id')) {
      /* Activate button */
      $(this).closest('.item-wrapper').find('[data-wpcdnkoloss-context="include-file"]').removeAttr('disabled');
    } else {
      /* Deactivate button */
      if (!$(this).closest('.item-wrapper').find('[data-wpcdnkoloss-context="include-file"]').attr('data-wpcdnkoloss-file-id'))
        $(this).closest('.item-wrapper').find('[data-wpcdnkoloss-context="include-file"]').attr('disabled', 'disabled');
    }
  });

  /* Handle library version choose in list */
  $('body').on('change', '[data-wpcdnkoloss-context="files-list"] [data-wpcdnkoloss-context="include-in"]', function(e) {
    /* Stop default */
    e.preventDefault();

    /* Check for existing version assets */
    if ($(this).val() || $(this).closest('tr').find('[data-wpcdnkoloss-context="include-file"]').attr('data-wpcdnkoloss-file-id')) {
      /* Activate button */
      $(this).closest('tr').find('[data-wpcdnkoloss-context="include-file"]').removeAttr('disabled');
    } else {
      /* Deactivate button */
      if (!$(this).closest('tr').find('[data-wpcdnkoloss-context="include-file"]').attr('data-wpcdnkoloss-file-id'))
        $(this).closest('tr').find('[data-wpcdnkoloss-context="include-file"]').attr('disabled', 'disabled');
    }
  });

  /* Handle file inclusion */
  $('body').on('click touchend', '[data-wpcdnkoloss-context="include-file"]', function(e) {
    /* Stop default */
    e.preventDefault();

    /* Get clicked element */
    var clickedElement = $(this);

    /* Enable spinner */
    $(clickedElement).find('.spinner').first().addClass('is-active');

    /* Check for library */
    if ($(clickedElement).attr('data-wpcdnkoloss-file-library'))
      var library = $(clickedElement).attr('data-wpcdnkoloss-file-library');
    else
      var library = wpcdnkoloss.active_library.name;

    /* Check for version */
    if ($(clickedElement).attr('data-wpcdnkoloss-file-version'))
      var version = $(clickedElement).attr('data-wpcdnkoloss-file-version');
    else
      var version = $('[data-wpcdnkoloss-context="choose-library-version"] select').val();

    /* Check for position */
    if ($(clickedElement).closest('.item-wrapper').find('select[name="include-in"]').length > 0)
      var position = $(clickedElement).closest('.item-wrapper').find('select[name="include-in"]').val();
    else
      var position = $(this).closest('tr').find('select[name="include-in"]').val();

    /* Setup data */
    var data = {
      action: 'addFileAjax',
      security: $('div[data-wpcdnkoloss-context="assets-list"]').attr('data-wpcdnkoloss-nonce'),
      library: library,
      version: version,
      file: $(clickedElement).attr('data-wpcdnkoloss-file'),
      position: position
    };

    console.log(data);

    /* Check for id */
    if ($(clickedElement).attr('data-wpcdnkoloss-file-id')) {
      /* Set id in data */
      data.id = $(clickedElement).attr('data-wpcdnkoloss-file-id');
    }

    /* Add file into database */
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response) {
        console.log(response);
        /* Create detail view */
        if (response > 0 && response != null) {
          /* Add id to elements */
          $(clickedElement).find('span').html('Update');
          $(clickedElement).attr('data-wpcdnkoloss-file-id', response);
          $(clickedElement).next().removeClass('hidden');
          $(clickedElement).next().attr('data-wpcdnkoloss-file-id', response);

          /* Setup data */
          var data = {
            action: 'updateIncludedFilesAjax',
            security: $('div[data-wpcdnkoloss-context="files-list"]').attr('data-wpcdnkoloss-nonce')
          };

          console.log(data);

          /* Set loading */
          $('div[data-wpcdnkoloss-context="files-list"]').addClass('is-loading');

          /* Update included files table */
          $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            dataType: 'json',
            success: function(response) {
              console.log(response);
              /* Set included files */
              setIncludedFiles(response);

              /* Setup data */
              var data = {
                action: 'getIncludedFilesTableAjax',
                security: $('div[data-wpcdnkoloss-context="files-list"]').attr('data-wpcdnkoloss-nonce')
              };

              /* Set loading */
              $('div[data-wpcdnkoloss-context="files-list"]').addClass('is-loading');

              /* Update included files table */
              $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function(response) {
                  console.log(response);
                  /* Set file list */
                  $('[data-wpcdnkoloss-context="files-list"] table tbody').html(response);
                },
                error: function(response) {
                  console.log(response);
                },
                complete: function(response) {
                  console.log(response);
                  /* Remove loading */
                  $('div[data-wpcdnkoloss-context="files-list"]').removeClass('is-loading');
                }
              });
            },
            error: function(response) {
              console.log(response);
            },
            complete: function(response) {
              console.log(response);
            }
          });
        }
      },
      error: function(response) {
        console.log('error',response);
      },
      complete: function(response) {
        /* Hide active spinner */
        $(clickedElement).find('.spinner').removeClass('is-active');
      },
      dataType: 'json'
    });
  });

  /* Handle file remove */
  $('body').on('click touchend', '[data-wpcdnkoloss-context="remove-file"]', function(e) {
    /* Stop default */
    e.preventDefault();

    /* Get clicked element */
    var clickedElement = $(this);

    /* Enable spinner */
    $(clickedElement).find('.spinner').first().addClass('is-active');

    /* Setup data */
    var data = {
      action: 'removeFileAjax',
      security: $('div[data-wpcdnkoloss-context="assets-list"]').attr('data-wpcdnkoloss-nonce'),
      id: $(clickedElement).attr('data-wpcdnkoloss-file-id')
    };

    /* Remove file from database */
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response) {
        /* Create detail view */
        if (response > 0 && response != null) {
          /* Remove id from elements */
          fileId = $(clickedElement).attr('data-wpcdnkoloss-file-id');

          /* Remove id from elements */
          $(clickedElement).prev().find('span').html('Include');
          $(clickedElement).prev().attr('data-wpcdnkoloss-file-id', '');
          $(clickedElement).addClass('hidden');
          $(clickedElement).attr('data-wpcdnkoloss-file-id', '');

          /* Setup data */
          var data = {
            action: 'updateIncludedFilesAjax',
            security: $('div[data-wpcdnkoloss-context="files-list"]').attr('data-wpcdnkoloss-nonce')
          };

          /* Set loading */
          $('div[data-wpcdnkoloss-context="files-list"]').addClass('is-loading');

          /* Update included files table */
          $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            dataType: 'json',
            success: function(response) {
              console.log(response);
              /* Set included files */
              setIncludedFiles(response);

              /* Setup data */
              var data = {
                action: 'getIncludedFilesTableAjax',
                security: $('div[data-wpcdnkoloss-context="files-list"]').attr('data-wpcdnkoloss-nonce')
              };

              /* Set loading */
              $('div[data-wpcdnkoloss-context="files-list"]').addClass('is-loading');

              /* Update included files table */
              $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function(response) {
                  /* Set file list */
                  $('[data-wpcdnkoloss-context="files-list"] table tbody').html(response);
                },
                error: function(response) {},
                complete: function(response) {
                  /* Remove loading */
                  $('div[data-wpcdnkoloss-context="files-list"]').removeClass('is-loading');
                }
              });
            },
            error: function(response) {},
            complete: function(response) {}
          });
        }
      },
      error: function(response) {
        console.log('error',response);
      },
      complete: function(response) {
        /* Hide active spinner */
        $(clickedElement).find('.spinner').removeClass('is-active');
      },
      dataType: 'json'
    });
  });

  function populateAssetList(asset_list, detail_view) {
    /* Get files possible for adding */
    if ($('[data-wpcdnkoloss-template="asset-list-item-li"]').length > 0) {
      /* Setup some data */
      var library = wpcdnkoloss.active_library.name;
      var version = $(detail_view).find('[data-wpcdnkoloss-context="choose-library-version"] select').val();

      /* Walk files and check for appropriate file type */
      $.each(asset_list, function(fileIndex, fileElem) {
        /* Get file extension and check for allowed filetype */
        fileExt = fileElem.split('.');
        fileExt = fileExt[fileExt.length-1];

        /* Check for allowed filetype */
        if ($.inArray(fileExt, wpcdnkoloss.filetypes) >= 0) {
          /* Clone */
          assetLi = $('[data-wpcdnkoloss-template="asset-list-item-li"]').first().clone();
          $(assetLi).removeAttr('data-wpcdnkoloss-template');
          $(assetLi).find('[data-wpcdnkoloss-placeholder="name"]').html(fileElem);

          /* Check for css file extension to remove footer inclusion option */
          if (fileExt == 'css') {
            $(assetLi).find('select[name="include-in"] option[value="footer"]').remove();
          }

          /* Add data to include/remove */
          $(assetLi).find('[data-wpcdnkoloss-context="include-file"]').attr('data-wpcdnkoloss-file', fileElem);
          $(assetLi).find('[data-wpcdnkoloss-context="remove-file"]').attr('data-wpcdnkoloss-file', fileElem);

          /* Check for already included file */
          if (wpcdnkoloss.included_files[library]) {
            if (wpcdnkoloss.included_files[library][version]) {
              if (wpcdnkoloss.included_files[library][version][fileElem]) {
                /* Set file */
                incFile =  wpcdnkoloss.included_files[library][version][fileElem];

                /* Setup file ids etc. */
                $(assetLi).find('[data-wpcdnkoloss-context="include-file"]').attr('data-wpcdnkoloss-file-id', incFile.id);
                $(assetLi).find('[data-wpcdnkoloss-context="include-file"] span').html('Update');
                $(assetLi).find('[data-wpcdnkoloss-context="remove-file"]').attr('data-wpcdnkoloss-file-id', incFile.id);
                $(assetLi).find('[data-wpcdnkoloss-context="remove-file"]').removeClass('hidden');

                /* Check for position */
                if (incFile.position) {
                  $(assetLi).find('[data-wpcdnkoloss-context="include-in"]').val(incFile.position);
                }
              }
            }
          }

          /* Add to result list */
          $(detail_view).find('[data-wpcdnkoloss-context="assets-list"] ul').append(assetLi);
        }
      });

      /* Remove loading state */
      $('[data-wpcdnkoloss-context="assets-list"]').removeClass('is-loading');
    }
  }

  function setIncludedFiles(files) {
    /* Set files */
    wpcdnkoloss.included_files = files.ordered;
  }
});
