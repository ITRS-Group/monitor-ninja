(function () {

  var baseurl = _site_domain + _index_page + '/';

  var nl2br = function(text) {
    return text.replace("\n", "<br>", "g");
  };

  function restart () {
    $.get(baseurl + 'backup/restart')
      .done(function (message) {
        Notify.message(message.result);
      })
      .fail(function (data) {
        data = JSON.parse(data.responseText);
        var notification = Notify.message(data.result.message, {
          sticky: true,
          buttons: {
            "Show output": function () {
              notification.remove(1);
              Notify.message(nl2br(data.result.debug), {
                sticky: true
              });
            }
          }
        });
      })
  }

  function backup () {

    var notification = Notify.message('<img src="/ninja/application/media/images/loading.gif" /> Creating new backup..', {
      sticky: true,
      removable: false
    });

    $.get(baseurl + 'backup/backup')
      .done(function (response) {
        var file = response.result;

        var first = $('#backups tbody tr:first');

        notification.remove(1);
        Notify.message("Backup  '" + file + "' was created!");

        $('#backups tbody').prepend(
          $('<tr>').append(
            $('<td>').append(
              $('<a class="view_backup" style="border: 0px; margin-right: 4px">')
                .attr('href', baseurl + 'backup/view/' + file)
                .html('<span class="icon-16 x16-backup-view"></span>'),
              $('<a class="restore_backup" style="border: 0px; margin-right: 4px">')
                .attr('href', baseurl + 'backup/restore/' + file)
                .html('<span class="icon-16 x16-backup-restore"></span>'),
              $('<a class="delete_backup" style="border: 0px; margin-right: 4px">')
                .attr('href', baseurl + 'backup/delete/' + file)
                .html('<span class="icon-16 x16-backup-delete"></span>')
            ),
            $('<td>').append(
              $('<a class="download_backup">')
                .attr('href', baseurl + 'backup/download/' + file)
                .text(file)
            )
          ).addClass(first.length && first.hasClass('odd') ? 'even' : 'odd')
        );

      })
      .fail(function (data) {
        notification.remove(1);
        data = JSON.parse(data.responseText);
        notification = Notify.message(data.result.message, {
          type: "error",
          sticky: true,
          buttons: {
            "Show output": function () {
              notification.remove(1);
              Notify.message(nl2br(data.result.debug), {sticky: true});
            }
          }
        });
      });

  }

  $(document).on('click', '#verify_backup', function(ev){

    var link = $(this);

    $.get($(link).attr('href'))
      .done(function (data) {
        var notification = Notify.message(
        data.result + '. Do you really want to backup your current configuration?',
        {
          sticky: true,
          buttons: {
            "Yes": function () {
              notification.remove(1);
              backup();
            }
          }
        });
      })
      .fail(function (data) {
        data = JSON.parse(data.responseText);
        var notification = Notify.message(data.result.message, {
          type: "error",
          sticky: true,
          buttons: {
            "Show output": function () {
              notification.remove(1);
              Notify.message(nl2br(data.result.debug), {sticky: true});
            },
            "Backup anyway": backup
          }
        });
      });

    return false;

  });

  $(document).on('click', 'a.restore_backup', function(ev){

    var link = $(this);
    var notification = Notify.message('Do you really want to restore this backup?', {
      sticky: true,
      buttons: {
        "Yes": function () {
          notification.remove(1);
          $.get(link.attr('href'))
            .done(function (data) {
              notification = Notify.message(data.result + '. Your new configuration will not be used until the monitoring process is restarted', {
                sticky: true,
                buttons: {
                  "Restart now": function () {
                    notification.remove(1);
                    restart();
                  }
                }
              });
            })
            .fail(function (data) {
              data = JSON.parse(data.responseText);
              Notify.message(data.result.message, {
                type: "error",
                sticky: true,
                "Show output": function () {
                  notification.remove(1);
                  Notify.message(nl2br(data.result.debug), {sticky: true});
                }
              });
            });
        }
      }
    });

    return false;

  });

  $(document).on('click', 'a.delete_backup', function(){

    var link = $(this);
    var notification = Notify.message(
      'Do you really want to delete ' + link.closest('tr').find('.download_backup').text() + ' ?',
      {
        sticky: true,
        buttons: {
          "Yes": function () {
            $.get(link.attr('href'))
              .done(function (data) {
                notification.remove(1);
                Notify.message(data.result);
                link.parents('tr').remove();
              })
              .fail(function (data) {
                notification.remove(1);
                data = JSON.parse(data.responseText);
                Notify.message(data.result.message, {
                  type: "error"
                });
              })
          }
        }
      });

    return false;

  });

})();
